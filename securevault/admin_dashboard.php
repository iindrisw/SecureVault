<?php
// admin_dashboard.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

startSecureSession();
requireLogin();

// BENTENG KEAMANAN: Tolak jika bukan admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('<h2 style="color:red; text-align:center; margin-top:50px;">Akses Ditolak! Anda bukan Administrator.</h2>');
}

$db = getDB();

// --- LOGIKA EKSEKUSI ADMIN (BLOKIR USER & HAPUS FILE) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $target_id = (int)$_GET['id'];
    
    if ($action === 'suspend' || $action === 'activate') {
        // Cegah Admin memblokir dirinya sendiri
        if ($target_id === $_SESSION['user_id']) {
            $msg = "Error: Anda tidak bisa memblokir diri sendiri!";
            $msgType = "error";
        } else {
            if ($action === 'suspend') {
                $db->prepare("UPDATE users SET is_active = 0 WHERE id = ?")->execute([$target_id]);
                $msg = "Akun berhasil diblokir. User tidak akan bisa login.";
                $msgType = "success";
            } elseif ($action === 'activate') {
                $db->prepare("UPDATE users SET is_active = 1 WHERE id = ?")->execute([$target_id]);
                $msg = "Akun berhasil diaktifkan kembali.";
                $msgType = "success";
            }
        }
    } elseif ($action === 'delete_file') {
        // Hapus paksa file (soft delete)
        $db->prepare("UPDATE files SET is_deleted = 1 WHERE id = ?")->execute([$target_id]);
        $msg = "File berhasil dihapus paksa dari server.";
        $msgType = "success";
    }
    
    // Refresh halaman agar bersih dari parameter URL
    header("Location: /securevault/admin_dashboard.php?msg=" . urlencode($msg) . "&type=" . ($msgType ?? 'success'));
    exit;
}

// 1. Ambil Data Manajemen Pengguna
$usersCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers = $db->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();

// 2. Ambil Data Kapasitas Server
$totalStorage = $db->query("SELECT SUM(file_size) FROM files WHERE is_deleted = 0")->fetchColumn();

// 3. Ambil Log Audit Keamanan (5 aktivitas terbaru)
$recentLogs = $db->query("
    SELECT a.*, u.username 
    FROM activity_logs a 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC LIMIT 5
")->fetchAll();

// 4. Ambil Daftar Semua User untuk Dikelola
$allUsers = $db->query("SELECT id, username, email, role, is_active FROM users ORDER BY id DESC")->fetchAll();

// 5. Ambil Daftar Semua File di Server
$allFiles = $db->query("
    SELECT f.id, f.filename_original, f.file_size, f.created_at, u.username as owner_name 
    FROM files f 
    JOIN users u ON f.owner_id = u.id 
    WHERE f.is_deleted = 0 
    ORDER BY f.created_at DESC
")->fetchAll();

function fmtSize($b) {
    if ($b < 1024) return $b . ' B';
    if ($b < 1048576) return round($b/1024,1) . ' KB';
    return round($b/1048576,2) . ' MB';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard — SecureVault</title>
  <link rel="stylesheet" href="/securevault/assets/css/style.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<nav class="sv-navbar" style="border-bottom: 2px solid var(--danger);">
  <a href="#" class="sv-brand">
    <div class="lock-icon" style="background: var(--danger);">🛡️</div> 
    SecureVault <span style="color: var(--danger); font-size: 0.8rem; margin-left:10px;">[ADMIN PANEL]</span>
  </a>
  <div class="sv-nav-right">
    <div class="user-badge">
      <div class="avatar" style="background: var(--danger);"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
      <span>Admin: <?= htmlspecialchars($_SESSION['username']) ?></span>
    </div>
    <a href="/securevault/logout.php" class="btn-sv btn-ghost" style="padding:0.35rem 0.75rem;font-size:0.8rem">
      <i class="bi bi-box-arrow-right"></i> Keluar
    </a>
  </div>
</nav>

<main class="sv-main">
  <div class="page-header fade-in-up">
    <h1>Pusat Kendali Sistem</h1>
    <p>Kelola pengguna, pantau lalu lintas data, dan amankan server dari file mencurigakan.</p>
  </div>

  <?php if (isset($_GET['msg'])): ?>
    <div class="sv-alert sv-alert-<?= htmlspecialchars($_GET['type'] ?? 'success') ?>">
      <i class="bi bi-info-circle-fill"></i> <?= htmlspecialchars($_GET['msg']) ?>
    </div>
  <?php endif; ?>

  <div class="stats-grid" style="margin-bottom:2rem;">
    <div class="stat-card fade-in-up">
      <div class="stat-icon" style="background:rgba(56, 189, 248, 0.1);color:var(--accent)">
        <i class="bi bi-people"></i>
      </div>
      <div>
        <div class="stat-value"><?= $usersCount ?></div>
        <div class="stat-label">Total Pengguna (<?= $activeUsers ?> Aktif)</div>
      </div>
    </div>

    <div class="stat-card fade-in-up" style="animation-delay:0.1s">
      <div class="stat-icon" style="background:rgba(244, 63, 94, 0.1);color:var(--danger)">
        <i class="bi bi-server"></i>
      </div>
      <div>
        <div class="stat-value"><?= fmtSize($totalStorage) ?></div>
        <div class="stat-label">Beban Server (Storage)</div>
      </div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1.5fr 1fr;gap:1.5rem; margin-bottom:1.5rem;">
    
    <div class="sv-card fade-in-up">
      <div class="sv-card-header">
        <h5><i class="bi bi-person-gear"></i> Kelola Pengguna</h5>
      </div>
      <div style="overflow-x: auto;">
        <table class="sv-table">
          <thead>
            <tr>
              <th>Username</th>
              <th>Status</th>
              <th>Tindakan (Aksi)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allUsers as $u): ?>
            <tr>
              <td style="font-weight: 600; color: var(--text-primary);">
                <?= htmlspecialchars($u['username']) ?>
                <?= $u['role'] === 'admin' ? '<span class="sv-badge badge-shared" style="margin-left:5px;">Admin</span>' : '' ?>
              </td>
              
              <td>
                <?php if ($u['is_active'] == 1): ?>
                    <span class="sv-badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">Aktif</span>
                <?php else: ?>
                    <span class="sv-badge" style="background: rgba(244, 63, 94, 0.1); color: var(--danger);">Diblokir</span>
                <?php endif; ?>
              </td>
              
              <td>
                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                    <?php if ($u['is_active'] == 1): ?>
                        <a href="?action=suspend&id=<?= $u['id'] ?>" class="btn-sv btn-danger-sv" style="padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="return confirm('Yakin ingin memblokir user ini?');">
                            <i class="bi bi-slash-circle"></i> Blokir
                        </a>
                    <?php else: ?>
                        <a href="?action=activate&id=<?= $u['id'] ?>" class="btn-sv btn-primary-sv" style="padding:0.2rem 0.5rem; font-size:0.75rem;">
                            <i class="bi bi-check-circle"></i> Aktifkan
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="font-size:0.75rem; color:var(--text-muted);">(Anda Sendiri)</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="sv-card fade-in-up" style="animation-delay:0.2s">
      <div class="sv-card-header">
        <h5><i class="bi bi-shield-check"></i> Radar Lalu Lintas Data</h5>
      </div>
      <table class="sv-table">
        <thead>
          <tr>
            <th>User</th>
            <th>Aktivitas</th>
            <th>IP Address</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentLogs as $log): ?>
          <tr>
            <td style="font-weight: 600; color: var(--accent);"><?= htmlspecialchars($log['username']) ?></td>
            <td>
               <span class="sv-badge badge-private"><?= htmlspecialchars($log['action']) ?></span>
            </td>
            <td style="font-family: var(--mono); font-size:0.75rem;"><?= htmlspecialchars($log['ip_address']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($recentLogs)): ?>
          <tr><td colspan="3" style="text-align:center;">Belum ada log aktivitas.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="sv-card fade-in-up" style="animation-delay:0.3s">
    <div class="sv-card-header">
      <h5><i class="bi bi-hdd-network"></i> Manajemen Berkas Server</h5>
      <span style="font-size: 0.75rem; color: var(--text-muted);"><i class="bi bi-info-circle"></i> Admin tidak dapat membuka isi file.</span>
    </div>
    <div style="overflow-x: auto;">
      <table class="sv-table">
        <thead>
          <tr>
            <th>Nama File Asli</th>
            <th>Pemilik (Uploader)</th>
            <th>Ukuran</th>
            <th>Tanggal Upload</th>
            <th>Tindakan Server</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allFiles as $f): ?>
          <tr>
            <td style="font-weight: 600; color: var(--text-primary); max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
              <i class="bi bi-file-earmark-lock2" style="color:var(--text-muted); margin-right:5px;"></i> <?= htmlspecialchars($f['filename_original']) ?>
            </td>
            <td style="color: var(--accent); font-weight: 600;">
              @<?= htmlspecialchars($f['owner_name']) ?>
            </td>
            <td style="font-family: var(--mono); font-size: 0.8rem;">
              <?= fmtSize($f['file_size']) ?>
            </td>
            <td style="font-family: var(--mono); font-size: 0.8rem; color: var(--text-muted);">
              <?= date('d M Y H:i', strtotime($f['created_at'])) ?>
            </td>
            <td>
              <a href="?action=delete_file&id=<?= $f['id'] ?>" class="btn-sv btn-danger-sv" style="padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="return confirm('Tindakan ini akan menghapus file dari server. Lanjutkan?');">
                <i class="bi bi-trash3"></i> Hapus Paksa
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($allFiles)): ?>
          <tr><td colspan="5" style="text-align:center; padding: 2rem;">Server kosong. Belum ada file yang diunggah.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>

</body>
</html>