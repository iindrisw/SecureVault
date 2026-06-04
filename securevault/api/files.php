<?php
// api/files.php — File Management API
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/files.php';
require_once __DIR__ . '/../includes/crypto.php';

startSecureSession();

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Tidak terautentikasi.']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {

        case 'upload':
            // Receive encrypted file data from client
            $originalName    = trim($_POST['filename'] ?? '');
            $mimeType        = trim($_POST['mime_type'] ?? 'application/octet-stream');
            $fileSize        = (int)($_POST['file_size'] ?? 0);
            $encryptedData   = $_POST['encrypted_data'] ?? '';   // base64
            $sessionKeyEnc   = $_POST['session_key_enc'] ?? '';  // RSA-wrapped session key
            $sessionKeyIv    = $_POST['session_key_iv'] ?? '';   // hex
            $aesTag          = $_POST['aes_tag'] ?? '';          // hex
            $originalHash    = $_POST['original_hash'] ?? '';    // SHA-256 of original

            if (empty($originalName) || empty($encryptedData) || empty($sessionKeyEnc)) {
                echo json_encode(['success' => false, 'message' => 'Data upload tidak lengkap.']);
                exit;
            }

            // Validate file size
            $maxSize = MAX_FILE_SIZE;
            $estimatedSize = strlen($encryptedData) * 0.75; // rough base64 decode estimate
            if ($fileSize > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'File terlalu besar (max 50MB).']);
                exit;
            }

            $result = saveFile($userId, $originalName, $mimeType, $fileSize,
                               $encryptedData, $sessionKeyEnc, $sessionKeyIv, $aesTag, $originalHash);
            echo json_encode($result);
            break;

        case 'list':
            $files = getUserFiles($userId);
            echo json_encode(['success' => true, 'data' => $files]);
            break;

        case 'download':
            $fileId = (int)($_GET['file_id'] ?? $_POST['file_id'] ?? 0);
            if (!$fileId) { echo json_encode(['success' => false, 'message' => 'File ID diperlukan.']); exit; }

            $file = getFileWithAccess($fileId, $userId);
            if (!$file) { echo json_encode(['success' => false, 'message' => 'Akses ditolak atau file tidak ditemukan.']); exit; }

            // Verify integrity
            $filePath    = UPLOAD_DIR . $file['filename_stored'];
            $currentHash = hash_file('sha256', $filePath);
            if ($currentHash !== $file['file_hash']) {
                echo json_encode(['success' => false, 'message' => 'PERINGATAN: Integritas file gagal! File mungkin dimodifikasi.']);
                exit;
            }

            $encContent = file_get_contents($filePath);
            if ($encContent === false) {
                echo json_encode(['success' => false, 'message' => 'Gagal membaca file.']);
                exit;
            }

            // Determine which session key to use
            $sessionKeyEnc = ($file['access_type'] === 'shared' && $file['shared_session_key_enc'])
                           ? $file['shared_session_key_enc']
                           : $file['session_key_enc'];

            logActivity($userId, 'download', $fileId, null, "Downloaded: {$file['filename_original']}");

            echo json_encode([
                'success'         => true,
                'encrypted_data'  => base64_encode($encContent),
                'session_key_enc' => $sessionKeyEnc,
                'session_key_iv'  => $file['session_key_iv'],
                'aes_tag'         => $file['aes_tag'],
                'original_hash'   => $file['original_hash'],
                'filename'        => $file['filename_original'],
                'mime_type'       => $file['mime_type'],
            ]);
            break;

        case 'get_file_info':
            $fileId = (int)($_GET['file_id'] ?? 0);
            $file   = getFileWithAccess($fileId, $userId);
            if (!$file) { echo json_encode(['success' => false, 'message' => 'File tidak ditemukan.']); exit; }

            // Get shares list (only for owner)
            $shares = [];
            if ($file['owner_id'] == $userId) {
                $shares = getFileShares($fileId, $userId);
            }

            echo json_encode([
                'success' => true,
                'file'    => [
                    'id'               => $file['id'],
                    'filename_original'=> $file['filename_original'],
                    'file_size'        => $file['file_size'],
                    'mime_type'        => $file['mime_type'],
                    'created_at'       => $file['created_at'],
                    'owner_name'       => $file['owner_name'],
                    'access_type'      => $file['access_type'],
                    'original_hash'    => $file['original_hash'],
                    'file_hash'        => $file['file_hash'],
                ],
                'shares' => $shares,
            ]);
            break;

        case 'share':
            $fileId      = (int)($_POST['file_id'] ?? 0);
            $recipientId = (int)($_POST['recipient_id'] ?? 0);
            $wrappedKey  = $_POST['session_key_enc'] ?? '';

            if (!$fileId || !$recipientId || empty($wrappedKey)) {
                echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap.']);
                exit;
            }

            $result = shareFile($fileId, $userId, $recipientId, $wrappedKey);
            echo json_encode($result);
            break;

        case 'revoke_share':
            $fileId      = (int)($_POST['file_id'] ?? 0);
            $recipientId = (int)($_POST['recipient_id'] ?? 0);
            $result      = revokeShare($fileId, $userId, $recipientId);
            echo json_encode($result);
            break;

        case 'delete':
            $fileId = (int)($_POST['file_id'] ?? 0);
            $result = deleteFile($fileId, $userId);
            echo json_encode($result);
            break;

        case 'verify_integrity':
            $fileId = (int)($_GET['file_id'] ?? 0);
            $result = verifyFileIntegrity($fileId, $userId);
            echo json_encode($result);
            break;

        case 'get_users':
            $users = getUsers($userId);
            echo json_encode(['success' => true, 'users' => $users]);
            break;

        case 'get_recipient_pubkey':
            $recipientId = (int)($_GET['recipient_id'] ?? 0);
            if (!$recipientId) { echo json_encode(['success' => false, 'message' => 'ID pengguna diperlukan.']); exit; }

            $db   = getDB();
            $stmt = $db->prepare('SELECT id, username, public_key FROM users WHERE id = ? AND is_active = 1');
            $stmt->execute([$recipientId]);
            $user = $stmt->fetch();

            if (!$user) { echo json_encode(['success' => false, 'message' => 'Pengguna tidak ditemukan.']); exit; }

            echo json_encode(['success' => true, 'public_key' => $user['public_key'], 'username' => $user['username']]);
            break;

        case 'get_activity':
            $limit = min((int)($_GET['limit'] ?? 50), 200);
            $logs  = getUserActivityLog($userId, $limit);
            echo json_encode(['success' => true, 'logs' => $logs]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action tidak dikenali: ' . $action]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}