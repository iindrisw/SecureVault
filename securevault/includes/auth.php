<?php
// includes/auth.php - Authentication & Session Management

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/crypto.php';

function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => false, // Set true in production with HTTPS
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

function isLoggedIn(): bool {
    startSecureSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /securevault/login.php');
        exit;
    }
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    $db   = getDB();
    $stmt = $db->prepare('SELECT id, username, role, email, public_key, created_at, last_login FROM users WHERE id = ? AND is_active = 1');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function loginUser(string $username, string $password): array {
    $db   = getDB();
    
    // Perbaikan: Hapus "AND is_active = 1" di sini agar kita bisa mengecek akun yang diblokir
    $stmt = $db->prepare('SELECT * FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        logActivity(null, 'login', null, null, 'Failed login attempt for: ' . $username);
        return ['success' => false, 'message' => 'Username atau password salah.'];
    }

    // 👇 PENCEGAT AKUN DIBLOKIR 👇
    if ($user['is_active'] == 0) {
        logActivity($user['id'], 'login_blocked', null, null, 'Blocked user attempted to login.');
        return ['success' => false, 'message' => 'Akun Anda telah ditangguhkan/diblokir oleh Administrator.'];
    }

    // Verify we can decrypt private key (password check)
    try {
        decryptPrivateKey($user['private_key_enc'], $user['private_key_iv'], $user['private_key_salt'], $password);
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Gagal memverifikasi kunci kriptografi.'];
    }

    startSecureSession();
    session_regenerate_id(true);

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['role']      = $user['role']; // Jabatan User / Admin
    $_SESSION['email']     = $user['email'];
    // Store encrypted private key data so client can decrypt with password
    $_SESSION['priv_enc']  = $user['private_key_enc'];
    $_SESSION['priv_iv']   = $user['private_key_iv'];
    $_SESSION['priv_salt'] = $user['private_key_salt'];
    $_SESSION['pub_key']   = $user['public_key'];

    // Update last login
    $db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);
    logActivity($user['id'], 'login');

    return ['success' => true, 'user' => $user];
}

function registerUser(string $username, string $email, string $password): array {
    // Validate input
    if (strlen($username) < 3 || strlen($username) > 50) {
        return ['success' => false, 'message' => 'Username harus 3-50 karakter.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Email tidak valid.'];
    }
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password minimal 8 karakter.'];
    }

    $db = getDB();

    // Check uniqueness
    $stmt = $db->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Username atau email sudah terdaftar.'];
    }

    // Generate RSA key pair
    try {
        $keyPair = generateRSAKeyPair();
        $encPriv = encryptPrivateKey($keyPair['private_key'], $password);
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Gagal membuat kunci kriptografi: ' . $e->getMessage()];
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    try {
        $stmt = $db->prepare('
            INSERT INTO users (username, email, password_hash, public_key, private_key_enc, private_key_iv, private_key_salt)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $username,
            $email,
            $passwordHash,
            $keyPair['public_key'],
            $encPriv['encrypted'],
            $encPriv['iv'],
            $encPriv['salt'],
        ]);
        $userId = $db->lastInsertId();
        logActivity($userId, 'register');
        return ['success' => true, 'message' => 'Registrasi berhasil! Silakan login.'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Gagal mendaftar: ' . $e->getMessage()];
    }
}

function logoutUser(): void {
    startSecureSession();
    if (isset($_SESSION['user_id'])) {
        logActivity($_SESSION['user_id'], 'logout');
    }
    session_destroy();
}

function logActivity(?int $userId, string $action, ?int $fileId = null, ?int $targetUserId = null, ?string $details = null): void {
    if ($userId === null) return;
    try {
        $db   = getDB();
        $stmt = $db->prepare('
            INSERT INTO activity_logs (user_id, action, file_id, target_user_id, ip_address, user_agent, details)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $userId,
            $action,
            $fileId,
            $targetUserId,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            $details,
        ]);
    } catch (Exception $e) {
        // Logging failure should not break the app
    }
}