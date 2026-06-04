<?php
// register.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

startSecureSession();
if (isLoggedIn()) { header('Location: /securevault/dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrasi — SecureVault</title>
  <link rel="stylesheet" href="/securevault/assets/css/style.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card fade-in-up" style="max-width:480px">
    <div class="auth-logo">
      <div class="logo-mark">🔐</div>
      <h2>Buat Akun</h2>
      <p>RSA key pair akan digenerate di browser Anda</p>
    </div>

    <div id="alert-container"></div>

    <div id="step-form">
      <div class="form-group">
        <label class="sv-label"><i class="bi bi-person"></i> Username</label>
        <input type="text" id="reg-username" class="sv-input" placeholder="username (3-50 karakter)" required>
      </div>
      <div class="form-group">
        <label class="sv-label"><i class="bi bi-envelope"></i> Email</label>
        <input type="email" id="reg-email" class="sv-input" placeholder="email@example.com" required>
      </div>
      <div class="form-group">
        <label class="sv-label"><i class="bi bi-key"></i> Password</label>
        <input type="password" id="reg-password" class="sv-input" placeholder="minimal 8 karakter" required>
        <div id="pass-strength" style="margin-top:0.4rem;height:4px;border-radius:2px;background:var(--border);overflow:hidden">
          <div id="pass-bar" style="height:100%;width:0;transition:width 0.3s;border-radius:2px"></div>
        </div>
      </div>
      <div class="form-group">
        <label class="sv-label"><i class="bi bi-key-fill"></i> Konfirmasi Password</label>
        <input type="password" id="reg-password2" class="sv-input" placeholder="ulangi password" required>
      </div>

      <div class="sv-card" style="margin-bottom:1.25rem;background:rgba(0,212,170,0.04)">
        <div style="font-size:0.8rem;color:var(--text-muted);display:flex;gap:0.5rem;align-items:flex-start">
          <i class="bi bi-info-circle" style="color:var(--accent);flex-shrink:0;margin-top:2px"></i>
          <span>Saat Anda klik <strong style="color:var(--accent)">Buat Akun</strong>, browser akan generate RSA-2048 key pair. Kunci privat dienkripsi dengan password Anda (PBKDF2 + AES-256-CBC) sebelum dikirim ke server. Server <strong style="color:var(--accent)">tidak pernah</strong> menerima kunci privat dalam bentuk plaintext.</span>
        </div>
      </div>

      <button onclick="doRegister()" id="btn-register" class="btn-sv btn-primary-sv" style="width:100%;justify-content:center;padding:0.7rem">
        <i class="bi bi-person-plus"></i> Buat Akun
      </button>
    </div>

    <div id="step-generating" style="display:none;text-align:center;padding:2rem 0">
      <div style="font-size:2.5rem;margin-bottom:1rem" id="gen-icon">⚙️</div>
      <div style="font-weight:700;margin-bottom:0.5rem" id="gen-status">Generating RSA-2048 Key Pair...</div>
      <div style="font-size:0.8rem;color:var(--text-muted)" id="gen-sub">Ini mungkin membutuhkan beberapa detik</div>
      <div style="margin-top:1rem">
        <div class="sv-progress">
          <div class="sv-progress-bar" id="gen-progress" style="width:0%"></div>
        </div>
      </div>
    </div>

    <div class="auth-divider">— atau —</div>
    <div style="text-align:center">
      <a href="/securevault/login.php" class="btn-sv btn-ghost" style="width:100%;justify-content:center">
        <i class="bi bi-arrow-left"></i> Sudah punya akun? Masuk
      </a>
    </div>
  </div>
</div>

<script src="/securevault/assets/js/crypto.js"></script>
<script>
  // Password strength indicator
  document.getElementById('reg-password').addEventListener('input', function() {
    const pw = this.value;
    let strength = 0;
    if (pw.length >= 8) strength++;
    if (pw.length >= 12) strength++;
    if (/[A-Z]/.test(pw)) strength++;
    if (/[0-9]/.test(pw)) strength++;
    if (/[^A-Za-z0-9]/.test(pw)) strength++;

    const bar = document.getElementById('pass-bar');
    const colors = ['', '#ff4d6d', '#ffd166', '#ffd166', '#00d4aa', '#00d4aa'];
    const widths = ['0%', '20%', '40%', '60%', '80%', '100%'];
    bar.style.width = widths[strength];
    bar.style.background = colors[strength];
  });

  async function doRegister() {
    const username  = document.getElementById('reg-username').value.trim();
    const email     = document.getElementById('reg-email').value.trim();
    const password  = document.getElementById('reg-password').value;
    const password2 = document.getElementById('reg-password2').value;

    if (!username || !email || !password) { showAlert('Semua field wajib diisi.', 'error'); return; }
    if (password !== password2) { showAlert('Password tidak sama.', 'error'); return; }
    if (password.length < 8)   { showAlert('Password minimal 8 karakter.', 'error'); return; }

    // Show generating UI
    document.getElementById('step-form').style.display = 'none';
    document.getElementById('step-generating').style.display = 'block';
    document.getElementById('alert-container').innerHTML = '';

    const progress = document.getElementById('gen-progress');
    const status   = document.getElementById('gen-status');
    const sub      = document.getElementById('gen-sub');

    try {
      // Step 1: Generate key pair
      progress.style.width = '20%';
      status.textContent = 'Generating RSA-2048 Key Pair...';
      sub.textContent    = 'Membuat pasangan kunci enkripsi...';
      await new Promise(r => setTimeout(r, 100));

      const keyPair = await SV.generateKeyPair();
      progress.style.width = '50%';

      // Step 2: Encrypt private key with password
      status.textContent = 'Mengenkripsi Kunci Privat...';
      sub.textContent    = 'PBKDF2 (100k iterasi) + AES-256-CBC';
      await new Promise(r => setTimeout(r, 100));

      const encPriv = await SV.encryptPrivateKeyWithPassword(keyPair.privateKeyPem, password);
      progress.style.width = '75%';

      // Step 3: Send to server
      status.textContent = 'Mendaftarkan Akun...';
      sub.textContent    = 'Mengirim data ke server';

      const fd = new FormData();
      fd.append('action', 'register');
      fd.append('username', username);
      fd.append('email', email);
      fd.append('password', password);
      fd.append('public_key', keyPair.publicKeyPem);
      fd.append('private_key_enc', encPriv.encrypted);
      fd.append('private_key_iv', encPriv.iv);
      fd.append('private_key_salt', encPriv.salt);

      const res  = await fetch('/securevault/api/auth.php', { method: 'POST', body: fd });
      const data = await res.json();

      progress.style.width = '100%';
      document.getElementById('gen-icon').textContent = data.success ? '✅' : '❌';

      if (data.success) {
        status.textContent = 'Akun Berhasil Dibuat!';
        sub.textContent    = 'Mengalihkan ke halaman login...';
        setTimeout(() => window.location.href = '/securevault/login.php?registered=1', 1500);
      } else {
        status.textContent = 'Registrasi Gagal';
        sub.textContent    = data.message;
        setTimeout(() => {
          document.getElementById('step-form').style.display = 'block';
          document.getElementById('step-generating').style.display = 'none';
          showAlert(data.message, 'error');
        }, 2000);
      }
    } catch (err) {
      document.getElementById('gen-icon').textContent = '❌';
      status.textContent = 'Terjadi Kesalahan';
      sub.textContent    = err.message;
      setTimeout(() => {
        document.getElementById('step-form').style.display = 'block';
        document.getElementById('step-generating').style.display = 'none';
        showAlert('Error: ' + err.message, 'error');
      }, 2000);
    }
  }
</script>
</body>
</html>