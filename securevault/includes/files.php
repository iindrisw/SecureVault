<?php
// includes/files.php - File Management Operations

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/crypto.php';
require_once __DIR__ . '/auth.php';

/**
 * Save uploaded encrypted file metadata to DB
 * The actual encryption is done client-side in JavaScript
 */
function saveFile(
    int    $ownerId,
    string $originalName,
    string $mimeType,
    int    $fileSize,
    string $encryptedContent, // Base64-encoded encrypted file bytes
    string $sessionKeyEnc,    // Session key wrapped with owner's RSA public key (base64)
    string $sessionKeyIv,     // IV for AES-GCM (hex)
    string $aesTag,           // AES-GCM auth tag (hex)
    string $originalHash,     // SHA-256 of original file
): array {
    $db = getDB();

    $uuid         = generateUUID();
    $storedName   = $uuid . '.enc';
    $storedPath   = UPLOAD_DIR . $storedName;

    // Decode and write encrypted file
    $encryptedBytes = base64_decode($encryptedContent);
    if ($encryptedBytes === false || strlen($encryptedBytes) === 0) {
        return ['success' => false, 'message' => 'Data terenkripsi tidak valid.'];
    }

    if (file_put_contents($storedPath, $encryptedBytes) === false) {
        return ['success' => false, 'message' => 'Gagal menyimpan file terenkripsi.'];
    }

    // Hash of the encrypted file for integrity verification
    $encryptedHash = hash_file('sha256', $storedPath);

    try {
        $stmt = $db->prepare('
            INSERT INTO files (owner_id, filename_original, filename_stored, file_size, mime_type,
                               file_hash, original_hash, session_key_enc, session_key_iv, aes_tag)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $ownerId, $originalName, $storedName, $fileSize, $mimeType,
            $encryptedHash, $originalHash, $sessionKeyEnc, $sessionKeyIv, $aesTag
        ]);
        $fileId = $db->lastInsertId();

        logActivity($ownerId, 'upload', $fileId, null, "Uploaded: $originalName ($fileSize bytes)");

        return ['success' => true, 'file_id' => $fileId, 'message' => 'File berhasil diupload dan dienkripsi.'];
    } catch (PDOException $e) {
        @unlink($storedPath);
        return ['success' => false, 'message' => 'Gagal menyimpan metadata: ' . $e->getMessage()];
    }
}

/**
 * Get file list for a user (owned + shared)
 */
function getUserFiles(int $userId): array {
    $db = getDB();

    // Files owned by user
    $stmt = $db->prepare('
        SELECT f.*, u.username as owner_name, "owned" as access_type, NULL as shared_by,
               GROUP_CONCAT(DISTINCT su.username ORDER BY su.username SEPARATOR ", ") as shared_with
        FROM files f
        JOIN users u ON f.owner_id = u.id
        LEFT JOIN file_shares fs2 ON f.id = fs2.file_id AND fs2.is_revoked = 0
        LEFT JOIN users su ON fs2.recipient_id = su.id
        WHERE f.owner_id = ? AND f.is_deleted = 0
        GROUP BY f.id
        ORDER BY f.created_at DESC
    ');
    $stmt->execute([$userId]);
    $owned = $stmt->fetchAll();

    // Files shared with user
    $stmt = $db->prepare('
        SELECT f.*, u.username as owner_name, "shared" as access_type,
               u.username as shared_by, fs.session_key_enc as shared_session_key_enc,
               fs.permission, NULL as shared_with
        FROM files f
        JOIN users u ON f.owner_id = u.id
        JOIN file_shares fs ON f.id = fs.file_id AND fs.recipient_id = ? AND fs.is_revoked = 0
        WHERE f.is_deleted = 0
        ORDER BY fs.shared_at DESC
    ');
    $stmt->execute([$userId]);
    $shared = $stmt->fetchAll();

    return ['owned' => $owned, 'shared' => $shared];
}

/**
 * Get single file with access check
 */
function getFileWithAccess(int $fileId, int $userId): ?array {
    $db = getDB();

    // Check owned
    $stmt = $db->prepare('SELECT f.*, u.username as owner_name, "owned" as access_type, NULL as shared_session_key_enc
                          FROM files f JOIN users u ON f.owner_id = u.id
                          WHERE f.id = ? AND f.owner_id = ? AND f.is_deleted = 0');
    $stmt->execute([$fileId, $userId]);
    $file = $stmt->fetch();

    if (!$file) {
        // Check shared
        $stmt = $db->prepare('
            SELECT f.*, u.username as owner_name, "shared" as access_type, fs.session_key_enc as shared_session_key_enc
            FROM files f
            JOIN users u ON f.owner_id = u.id
            JOIN file_shares fs ON f.id = fs.file_id AND fs.recipient_id = ? AND fs.is_revoked = 0
            WHERE f.id = ? AND f.is_deleted = 0
        ');
        $stmt->execute([$userId, $fileId]);
        $file = $stmt->fetch();
    }

    return $file ?: null;
}

/**
 * Share file with another user (key wrapping)
 */
function shareFile(int $fileId, int $ownerId, int $recipientId, string $wrappedSessionKey): array {
    $db = getDB();

    // Verify ownership
    $stmt = $db->prepare('SELECT id FROM files WHERE id = ? AND owner_id = ? AND is_deleted = 0');
    $stmt->execute([$fileId, $ownerId]);
    if (!$stmt->fetch()) {
        return ['success' => false, 'message' => 'File tidak ditemukan atau bukan milik Anda.'];
    }

    // Can't share with yourself
    if ($ownerId === $recipientId) {
        return ['success' => false, 'message' => 'Tidak bisa berbagi dengan diri sendiri.'];
    }

    try {
        $stmt = $db->prepare('
            INSERT INTO file_shares (file_id, owner_id, recipient_id, session_key_enc, permission)
            VALUES (?, ?, ?, ?, "read")
            ON DUPLICATE KEY UPDATE session_key_enc = VALUES(session_key_enc), is_revoked = 0
        ');
        $stmt->execute([$fileId, $ownerId, $recipientId, $wrappedSessionKey]);

        logActivity($ownerId, 'share', $fileId, $recipientId);

        return ['success' => true, 'message' => 'File berhasil dibagikan.'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Gagal membagikan file: ' . $e->getMessage()];
    }
}

/**
 * Revoke file share
 */
function revokeShare(int $fileId, int $ownerId, int $recipientId): array {
    $db = getDB();

    $stmt = $db->prepare('
        UPDATE file_shares SET is_revoked = 1
        WHERE file_id = ? AND owner_id = ? AND recipient_id = ?
    ');
    $stmt->execute([$fileId, $ownerId, $recipientId]);

    if ($stmt->rowCount() === 0) {
        return ['success' => false, 'message' => 'Share tidak ditemukan.'];
    }

    logActivity($ownerId, 'revoke_share', $fileId, $recipientId);
    return ['success' => true, 'message' => 'Akses file berhasil dicabut.'];
}

/**
 * Delete file (soft delete + remove key material)
 */
function deleteFile(int $fileId, int $ownerId): array {
    $db = getDB();

    $stmt = $db->prepare('SELECT * FROM files WHERE id = ? AND owner_id = ? AND is_deleted = 0');
    $stmt->execute([$fileId, $ownerId]);
    $file = $stmt->fetch();

    if (!$file) {
        return ['success' => false, 'message' => 'File tidak ditemukan.'];
    }

    // Delete encrypted file from disk
    $filePath = UPLOAD_DIR . $file['filename_stored'];
    if (file_exists($filePath)) {
        // Overwrite with random data before deletion (secure delete)
        $size = filesize($filePath);
        file_put_contents($filePath, random_bytes(max($size, 1)));
        unlink($filePath);
    }

    // Nullify key material in DB, mark deleted
    $stmt = $db->prepare('
        UPDATE files SET is_deleted = 1, session_key_enc = "", aes_tag = "", session_key_iv = ""
        WHERE id = ?
    ');
    $stmt->execute([$fileId]);

    // Remove all shares (key material gone anyway)
    $db->prepare('UPDATE file_shares SET is_revoked = 1 WHERE file_id = ?')->execute([$fileId]);

    logActivity($ownerId, 'delete', $fileId, null, "Deleted: {$file['filename_original']}");

    return ['success' => true, 'message' => 'File berhasil dihapus secara aman.'];
}

/**
 * Get activity log for user
 */
function getUserActivityLog(int $userId, int $limit = 50): array {
    $db   = getDB();
    $stmt = $db->prepare('
        SELECT al.*, f.filename_original, u2.username as target_username
        FROM activity_logs al
        LEFT JOIN files f ON al.file_id = f.id
        LEFT JOIN users u2 ON al.target_user_id = u2.id
        WHERE al.user_id = ?
        ORDER BY al.created_at DESC
        LIMIT ?
    ');
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get list of users (for sharing)
 */
function getUsers(int $excludeUserId): array {
    $db   = getDB();
    $stmt = $db->prepare('SELECT id, username, email FROM users WHERE id != ? AND is_active = 1 ORDER BY username');
    $stmt->execute([$excludeUserId]);
    return $stmt->fetchAll();
}

/**
 * Verify file integrity
 */
function verifyFileIntegrity(int $fileId, int $userId): array {
    $file = getFileWithAccess($fileId, $userId);
    if (!$file) {
        return ['success' => false, 'message' => 'File tidak ditemukan.'];
    }

    $filePath    = UPLOAD_DIR . $file['filename_stored'];
    $currentHash = hash_file('sha256', $filePath);

    if ($currentHash === $file['file_hash']) {
        return ['success' => true, 'valid' => true, 'message' => 'Integritas file terverifikasi.'];
    } else {
        return ['success' => true, 'valid' => false, 'message' => 'PERINGATAN: File mungkin telah dimodifikasi!'];
    }
}

/**
 * Get file shares list for a file
 */
function getFileShares(int $fileId, int $ownerId): array {
    $db   = getDB();
    $stmt = $db->prepare('
        SELECT fs.*, u.username, u.email
        FROM file_shares fs
        JOIN users u ON fs.recipient_id = u.id
        WHERE fs.file_id = ? AND fs.owner_id = ? AND fs.is_revoked = 0
        ORDER BY fs.shared_at DESC
    ');
    $stmt->execute([$fileId, $ownerId]);
    return $stmt->fetchAll();
}