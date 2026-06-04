<?php
// login.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/crypto.php';

startSecureSession();

if (isLoggedIn()) {
    header('Location: /securevault/dashboard.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Masukkan username/email dan password.';
    } else {
        $result = loginUser($username, $password);
       if ($result['success']) {
            // Cek apakah dia admin atau user biasa
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                header('Location: /securevault/admin_dashboard.php');
            } else {
                header('Location: /securevault/dashboard.php');
            }
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — SecureVault</title>
<link rel="stylesheet" href="/securevault/assets/css/style.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card fade-in-up">
    <div class="auth-logo">
      <div class="logo-mark">🔐</div>
      <h2>SecureVault</h2>
      <p>Penyimpanan Dokumen Terenkripsi End-to-End</p>
    </div>

    <?php if ($error): ?>
    <div class="sv-alert sv-alert-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="sv-alert sv-alert-success"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label class="sv-label" for="username"><i class="bi bi-person"></i> Username / Email</label>
        <input type="text" id="username" name="username" class="sv-input"
               placeholder="username atau email" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label class="sv-label" for="password"><i class="bi bi-key"></i> Password</label>
        <div style="position:relative">
          <input type="password" id="password" name="password" class="sv-input" placeholder="password" required>
          <button type="button" onclick="togglePass()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1rem">
            <i class="bi bi-eye" id="eye-icon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-sv btn-primary-sv" style="width:100%;justify-content:center;padding:0.7rem">
        <i class="bi bi-shield-lock"></i> Masuk Dengan Aman
      </button>
    </form>

    <div class="auth-divider">— atau —</div>

    <div style="text-align:center">
      <p style="font-size:0.85rem;color:var(--text-muted)">Belum punya akun?</p>
      <a href="/securevault/register.php" class="btn-sv btn-ghost" style="width:100%;justify-content:center;margin-top:0.5rem">
        <i class="bi bi-person-plus"></i> Buat Akun Baru
      </a>
    </div>

    <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border)">
      <div style="display:flex;gap:0.4rem;flex-wrap:wrap;justify-content:center">
        <span class="crypto-tag"><i class="bi bi-shield"></i> RSA-2048-OAEP</span>
        <span class="crypto-tag"><i class="bi bi-lock"></i> AES-256-GCM</span>
        <span class="crypto-tag"><i class="bi bi-check-square"></i> SHA-256</span>
      </div>
      <p style="text-align:center;font-size:0.7rem;color:var(--text-muted);margin-top:0.5rem">
        Kunci privat Anda dienkripsi dengan password. Server tidak pernah melihat data asli.
      </p>
    </div>
  </div>
</div>

<script>
function togglePass() {
  const inp = document.getElementById('password');
  const ico = document.getElementById('eye-icon');
  if (inp.type === 'password') {
    inp.type = 'text';
    ico.className = 'bi bi-eye-slash';
  } else {
    inp.type = 'password';
    ico.className = 'bi bi-eye';
  }
}
</script>
</body>
</html>