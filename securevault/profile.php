<?php
// profile.php — User Profile & Cryptographic Keys
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

startSecureSession();
requireLogin();

$user = getCurrentUser();
$db   = getDB();

// Ambil data user lengkap dari database
$stmt = $db->prepare('SELECT username, email, public_key, created_at FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$userData = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profil & Kunci — SecureVault</title>
  <link rel="stylesheet" href="/securevault/assets/css/style.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<nav class="sv-navbar">
  <a href="/securevault/dashboard.php" class="sv-brand"><div class="lock-icon">🔐</div> SecureVault</a>
  <div class="sv-nav-links">
    <a href="/securevault/dashboard.php"><i class="bi bi-grid"></i> Dashboard</a>
    <a href="/securevault/files.php"><i class="bi bi-folder"></i> File Saya</a>
    <a href="/securevault/upload.php"><i class="bi bi-cloud-upload"></i> Upload</a>
    <a href="/securevault/activity.php"><i class="bi bi-clock-history"></i> Riwayat</a>
  </div>
  <div class="sv-nav-right">
    <div class="user-badge">
      <div class="avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
      <span><?= htmlspecialchars($user['username']) ?></span>
    </div>
    <a href="/securevault/logout.php" class="btn-sv btn-ghost" style="padding:0.35rem 0.75rem;font-size:0.8rem">
      <i class="bi bi-box-arrow-right"></i> Keluar
    </a>
  </div>
</nav>

<main class="sv-main" style="max-width: 800px;">
  <div class="page-header fade-in-up">
    <h1><i class="bi bi-person-gear"></i> Profil & Kunci Kriptografi</h1>
    <p>Kelola identitas akun dan lihat Kunci Publik E2EE Anda.</p>
  </div>

  <div class="sv-card fade-in-up" style="margin-bottom: 1.5rem;">
    <div class="sv-card-header">
      <h5><i class="bi bi-person-badge"></i> Informasi Akun</h5>
    </div>
    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem; font-size: 0.95rem;">
      <div style="color: var(--text-muted); font-weight: 600;">Username</div>
      <div style="font-weight: bold; color: var(--text-primary);"><?= htmlspecialchars($userData['username']) ?></div>
      
      <div style="color: var(--text-muted); font-weight: 600;">Email</div>
      <div><?= htmlspecialchars($userData['email']) ?></div>
      
      <div style="color: var(--text-muted); font-weight: 600;">Bergabung Sejak</div>
      <div><?= date('d F Y', strtotime($userData['created_at'])) ?></div>
    </div>
  </div>

  <div class="sv-card fade-in-up" style="animation-delay: 0.1s;">
    <div class="sv-card-header">
      <h5><i class="bi bi-key"></i> Kunci Publik (RSA-2048)</h5>
    </div>
    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
      Kunci di bawah ini digunakan oleh sistem atau pengguna lain untuk membungkus (*Key Wrapping*) Session Key AES sebelum mengirim file kepada Anda. Kunci ini aman untuk dilihat publik.
    </p>
    
    <textarea class="sv-input" style="width: 100%; height: 220px; font-family: var(--mono); font-size: 0.75rem; resize: none; background: var(--bg-body); color: var(--accent);" readonly><?= htmlspecialchars($userData['public_key']) ?></textarea>
    
    <div style="margin-top: 1rem; border-top: 1px solid var(--border); padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
      <div>
        <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Status Kunci Privat:</div>
        <div id="priv-key-status" style="font-size: 0.85rem; color: var(--warning); margin-top: 5px;">
          <i class="bi bi-shield-exclamation"></i> Belum dimuat di memori
        </div>
      </div>
      <button onclick="checkPrivateKey()" class="btn-sv btn-ghost" style="font-size: 0.8rem;">
        <i class="bi bi-arrow-clockwise"></i> Cek Status RAM
      </button>
    </div>
  </div>

</main>

<script src="/securevault/assets/js/crypto.js"></script>
<script>
// Fungsi untuk mengecek apakah Private Key pengguna sedang aktif di RAM browser
function checkPrivateKey() {
    const statusEl = document.getElementById('priv-key-status');
    
    // Cek apakah object SV ada dan memiliki Kunci Privat
    if (typeof SV !== 'undefined' && SV.getPrivateKey && SV.getPrivateKey()) {
        statusEl.innerHTML = '<i class="bi bi-shield-check"></i> Aktif dan tersimpan aman di RAM Browser';
        statusEl.style.color = 'var(--accent)'; // Warna hijau/biru sukses
    } else {
        statusEl.innerHTML = '<i class="bi bi-shield-exclamation"></i> Tidak ada di memori (Anda harus memasukkan password saat buka file)';
        statusEl.style.color = 'var(--warning)'; // Warna kuning/orange peringatan
    }
}

// Otomatis mengecek saat halaman dimuat
window.addEventListener('DOMContentLoaded', checkPrivateKey);
</script>
</body>
</html>