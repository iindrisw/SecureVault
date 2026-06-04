<?php
// includes/crypto.php - Cryptographic Operations Helper
// Server-side crypto for key storage; real file crypto done client-side in JS

/**
 * Generate RSA-2048 key pair for a new user
 * Returns ['public_key' => PEM, 'private_key' => PEM]
 */
function generateRSAKeyPair(): array {
    $config = [
        'digest_alg'       => 'sha256',
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ];
    $resource = openssl_pkey_new($config);
    if (!$resource) {
        throw new Exception('Failed to generate RSA key pair: ' . openssl_error_string());
    }

    openssl_pkey_export($resource, $privateKeyPem);
    $details = openssl_pkey_get_details($resource);
    $publicKeyPem = $details['key'];

    return [
        'public_key'  => $publicKeyPem,
        'private_key' => $privateKeyPem,
    ];
}

/**
 * Encrypt private key with user password using AES-256-CBC
 * Used so server stores encrypted private key (never plaintext)
 */
function encryptPrivateKey(string $privateKeyPem, string $password): array {
    $salt = random_bytes(16);
    // Derive 32-byte key from password using PBKDF2
    $key = hash_pbkdf2('sha256', $password, $salt, 100000, 32, true);
    $iv  = random_bytes(16);

    $encrypted = openssl_encrypt(
        $privateKeyPem,
        'AES-256-CBC',
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );

    if ($encrypted === false) {
        throw new Exception('Failed to encrypt private key');
    }

    return [
        'encrypted' => base64_encode($encrypted),
        'iv'        => bin2hex($iv),
        'salt'      => bin2hex($salt),
    ];
}

/**
 * Decrypt private key with user password
 */
function decryptPrivateKey(string $encryptedB64, string $ivHex, string $saltHex, string $password): string {
    $salt      = hex2bin($saltHex);
    $iv        = hex2bin($ivHex);
    $encrypted = base64_decode($encryptedB64);

    $key = hash_pbkdf2('sha256', $password, $salt, 100000, 32, true);

    $decrypted = openssl_decrypt(
        $encrypted,
        'AES-256-CBC',
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );

    if ($decrypted === false) {
        throw new Exception('Failed to decrypt private key — wrong password?');
    }

    return $decrypted;
}

/**
 * Wrap (encrypt) a session key with RSA public key (OAEP padding)
 * Used for key wrapping when sharing files
 */
function wrapSessionKey(string $sessionKeyB64, string $publicKeyPem): string {
    $sessionKey = base64_decode($sessionKeyB64);
    $publicKey  = openssl_pkey_get_public($publicKeyPem);

    if (!$publicKey) {
        throw new Exception('Invalid public key');
    }

    $wrapped = '';
    $result  = openssl_public_encrypt($sessionKey, $wrapped, $publicKey, OPENSSL_PKCS1_OAEP_PADDING);

    if (!$result) {
        throw new Exception('Failed to wrap session key: ' . openssl_error_string());
    }

    return base64_encode($wrapped);
}

/**
 * Unwrap (decrypt) a session key with RSA private key
 */
function unwrapSessionKey(string $wrappedB64, string $privateKeyPem): string {
    $wrapped    = base64_decode($wrappedB64);
    $privateKey = openssl_pkey_get_private($privateKeyPem);

    if (!$privateKey) {
        throw new Exception('Invalid private key');
    }

    $sessionKey = '';
    $result     = openssl_private_decrypt($wrapped, $sessionKey, $privateKey, OPENSSL_PKCS1_OAEP_PADDING);

    if (!$result) {
        throw new Exception('Failed to unwrap session key: ' . openssl_error_string());
    }

    return base64_encode($sessionKey);
}

/**
 * Generate SHA-256 hash of file content
 */
function hashFile(string $filePath): string {
    return hash_file('sha256', $filePath);
}

/**
 * Generate SHA-256 hash of data
 */
function hashData(string $data): string {
    return hash('sha256', $data);
}

/**
 * Generate secure random token
 */
function generateToken(int $bytes = 32): string {
    return bin2hex(random_bytes($bytes));
}

/**
 * Generate UUID v4
 */
function generateUUID(): string {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}