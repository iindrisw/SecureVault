<?php
// logout.php — Secure Logout
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Jalankan fungsi logout bawaan sistem untuk mencatat log aktivitas dan menghapus session PHP
logoutUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Keluar — SecureVault</title>
 <link rel="stylesheet" href="/securevault/assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card" style="text-align: center; max-width: 400px;">
    <div style="font-size: 2.5rem; margin-bottom: 1rem;">🔒</div>
    <h3 style="margin-bottom: 0.5rem;">Membersihkan Sesi Enkripsi...</h3>
    <p style="color: var(--text-muted); font-size: 0.85rem;">Menghapus kunci kriptografi dari memori browser Anda.</p>
  </div>
</div>

<script src="/securevault/assets/js/crypto.js"></script>
<script>
  // Pastikan skrip berjalan setelah halaman termuat
  window.addEventListener('DOMContentLoaded', () => {
    // 1. Hapus Kunci Privat RSA yang menggantung di memori ram JavaScript (Zero-Knowledge Clean)
    if (typeof SV !== 'undefined' && typeof SV.clearPrivateKey === 'function') {
      SV.clearPrivateKey();
    }

    // 2. Alihkan pengguna kembali ke halaman login setelah 1 detik
    setTimeout(() => {
      window.location.href = '/securevault/login.php';
    }, 1000);
  });
</script>
</body>
</html>