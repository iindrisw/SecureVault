<?php
// api/auth.php — Authentication API Endpoint
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/crypto.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'register':
            $username      = trim($_POST['username'] ?? '');
            $email         = trim($_POST['email'] ?? '');
            $password      = $_POST['password'] ?? '';
            $publicKey     = $_POST['public_key'] ?? '';
            $privKeyEnc    = $_POST['private_key_enc'] ?? '';
            $privKeyIv     = $_POST['private_key_iv'] ?? '';
            $privKeySalt   = $_POST['private_key_salt'] ?? '';

            if (empty($username) || empty($email) || empty($password) || empty($publicKey)) {
                echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
                exit;
            }

            // Validate inputs
            if (strlen($username) < 3 || strlen($username) > 50) {
                echo json_encode(['success' => false, 'message' => 'Username harus 3-50 karakter.']);
                exit;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Email tidak valid.']);
                exit;
            }
            if (strlen($password) < 8) {
                echo json_encode(['success' => false, 'message' => 'Password minimal 8 karakter.']);
                exit;
            }

            $db = getDB();
            $stmt = $db->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Username atau email sudah terdaftar.']);
                exit;
            }

            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            $stmt = $db->prepare('
                INSERT INTO users (username, email, password_hash, public_key, private_key_enc, private_key_iv, private_key_salt)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([$username, $email, $passwordHash, $publicKey, $privKeyEnc, $privKeyIv, $privKeySalt]);
            $userId = $db->lastInsertId();

            logActivity($userId, 'register', null, null, "New user registered: $username");
            echo json_encode(['success' => true, 'message' => 'Registrasi berhasil!']);
            break;

        case 'get_key_data':
            // Return encrypted private key data so client can decrypt locally
            startSecureSession();
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Tidak terautentikasi.']);
                exit;
            }
            echo json_encode([
                'success'          => true,
                'private_key_enc'  => $_SESSION['priv_enc'],
                'private_key_iv'   => $_SESSION['priv_iv'],
                'private_key_salt' => $_SESSION['priv_salt'],
                'public_key'       => $_SESSION['pub_key'],
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action tidak dikenali.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}