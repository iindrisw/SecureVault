<?php
// activity.php — Activity Log
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
startSecureSession();
requireLogin();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Riwayat Aktivitas — SecureVault</title>
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
    <a href="/securevault/activity.php" class="active"><i class="bi bi-clock-history"></i> Riwayat</a>
  
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

<main class="sv-main">
  <div class="page-header fade-in-up">
    <h1><i class="bi bi-clock-history"></i> Riwayat Aktivitas</h1>
    <p>Audit trail lengkap — siapa mengakses file apa dan kapan.</p>
  </div>

  <div class="sv-card fade-in-up">
    <div class="sv-card-header">
      <h5>Log Aktivitas Terbaru</h5>
      <select id="filter-action" class="sv-input" style="width:auto;font-size:0.8rem;padding:0.3rem 0.6rem" onchange="filterLogs()">
        <option value="">Semua Aktivitas</option>
        <option value="upload">Upload</option>
        <option value="download">Download</option>
        <option value="share">Berbagi</option>
        <option value="delete">Hapus</option>
        <option value="login">Login</option>
        <option value="preview">Preview</option>
      </select>
    </div>
    <div id="log-container">
      <div style="text-align:center;padding:2rem;color:var(--text-muted)">
        <div class="spinning" style="font-size:1.5rem">⚙️</div>
        <div>Memuat log...</div>
      </div>
    </div>
  </div>
</main>

<script src="/securevault/assets/js/crypto.js"></script>
<script>
let allLogs = [];

const actionConfig = {
  upload:       { icon: 'bi-cloud-upload', color: 'var(--accent)',   label: 'Upload' },
  download:     { icon: 'bi-download',     color: 'var(--info)',     label: 'Download' },
  share:        { icon: 'bi-share',        color: 'var(--warning)',  label: 'Berbagi' },
  revoke_share: { icon: 'bi-x-circle',     color: 'var(--danger)',   label: 'Cabut Akses' },
  delete:       { icon: 'bi-trash',        color: 'var(--danger)',   label: 'Hapus' },
  login:        { icon: 'bi-box-arrow-in-right', color: '#a78bfa',  label: 'Login' },
  logout:       { icon: 'bi-box-arrow-right', color: 'var(--text-muted)', label: 'Logout' },
  register:     { icon: 'bi-person-plus',  color: 'var(--accent)',   label: 'Registrasi' },
  preview:      { icon: 'bi-eye',          color: 'var(--info)',     label: 'Preview' },
  view:         { icon: 'bi-eye',          color: 'var(--info)',     label: 'Lihat' },
};

async function loadLogs() {
  const res  = await fetch('/securevault/api/files.php?action=get_activity&limit=100');
  const data = await res.json();
  if (!data.success) return;
  allLogs = data.logs;
  renderLogs(allLogs);
}

function renderLogs(logs) {
  const container = document.getElementById('log-container');
  if (logs.length === 0) {
    container.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted)">Belum ada aktivitas.</div>';
    return;
  }

  container.innerHTML = logs.map(log => {
    const cfg = actionConfig[log.action] || { icon: 'bi-activity', color: 'var(--text-muted)', label: log.action };
    let desc = '';

    if (log.filename_original) desc += `File: <strong>${escHtml(log.filename_original)}</strong>`;
    if (log.target_username)   desc += ` → ${escHtml(log.target_username)}`;
    if (log.details && !log.filename_original) desc = log.details;

    return `<div class="log-item">
      <div style="width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:${cfg.color}22;color:${cfg.color};font-size:0.9rem">
        <i class="bi ${cfg.icon}"></i>
      </div>
      <div class="log-text">
        <div class="log-action">${cfg.label}</div>
        <div style="font-size:0.8rem;color:var(--text-muted);margin-top:0.1rem">${desc || '—'}</div>
      </div>
      <div style="text-align:right;flex-shrink:0">
        <div class="log-time">${formatDate(log.created_at)}</div>
        <div style="font-size:0.7rem;color:var(--text-muted);font-family:var(--mono)">${log.ip_address || ''}</div>
      </div>
    </div>`;
  }).join('');
}

function filterLogs() {
  const filter = document.getElementById('filter-action').value;
  const filtered = filter ? allLogs.filter(l => l.action === filter) : allLogs;
  renderLogs(filtered);
}

function escHtml(s) {
  if (!s) return '';
  const d = document.createElement('div'); d.textContent = s; return d.innerHTML;
}

loadLogs();
</script>
</body>
</html>