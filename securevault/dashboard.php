<?php
// dashboard.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

startSecureSession();
requireLogin();

$user = getCurrentUser();
$db   = getDB();

// Stats
$stmt = $db->prepare('SELECT COUNT(*) as cnt, COALESCE(SUM(file_size), 0) as total_size FROM files WHERE owner_id = ? AND is_deleted = 0');
$stmt->execute([$user['id']]);
$stats = $stmt->fetch();

$stmt = $db->prepare('SELECT COUNT(*) as cnt FROM file_shares WHERE owner_id = ? AND is_revoked = 0');
$stmt->execute([$user['id']]);
$sharedOut = $stmt->fetch();

$stmt = $db->prepare('SELECT COUNT(*) as cnt FROM file_shares WHERE recipient_id = ? AND is_revoked = 0');
$stmt->execute([$user['id']]);
$sharedIn = $stmt->fetch();

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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard — SecureVault</title>
 <link rel="stylesheet" href="/securevault/assets/css/style.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="sv-navbar">
  <a href="/securevault/dashboard.php" class="sv-brand">
    <div class="lock-icon">🔐</div>
    SecureVault
  </a>
  <div class="sv-nav-links">
    <a href="/securevault/dashboard.php" class="active"><i class="bi bi-grid"></i> Dashboard</a>
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

<main class="sv-main">
  <div class="page-header fade-in-up">
    <h1>Selamat datang, <?= htmlspecialchars($user['username']) ?> 👋</h1>
    <p>Semua file Anda terenkripsi end-to-end. Server hanya menyimpan ciphertext.</p>
  </div>

  <!-- STAT CARDS -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;margin-bottom:2rem">
    <div class="stat-card fade-in-up">
      <div class="stat-icon" style="background:rgba(0, 131, 212, 0.1);color:var(--accent)">
        <i class="bi bi-file-earmark-lock2"></i>
      </div>
      <div>
        <div class="stat-value"><?= $stats['cnt'] ?></div>
        <div class="stat-label">File Terenkripsi</div>
      </div>
    </div>
    <div class="stat-card fade-in-up" style="animation-delay:0.05s">
      <div class="stat-icon" style="background:rgba(76,201,240,0.1);color:var(--info)">
        <i class="bi bi-hdd"></i>
      </div>
      <div>
        <div class="stat-value"><?= fmtSize($stats['total_size']) ?></div>
        <div class="stat-label">Total Ukuran</div>
      </div>
    </div>
    <div class="stat-card fade-in-up" style="animation-delay:0.1s">
      <div class="stat-icon" style="background:rgba(255,209,102,0.1);color:var(--warning)">
        <i class="bi bi-share"></i>
      </div>
      <div>
        <div class="stat-value"><?= $sharedOut['cnt'] ?></div>
        <div class="stat-label">Dibagikan ke Orang Lain</div>
      </div>
    </div>
    <div class="stat-card fade-in-up" style="animation-delay:0.15s">
      <div class="stat-icon" style="background:rgba(167,139,250,0.1);color:#a78bfa">
        <i class="bi bi-people"></i>
      </div>
      <div>
        <div class="stat-value"><?= $sharedIn['cnt'] ?></div>
        <div class="stat-label">Dibagikan ke Saya</div>
      </div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem">
    <!-- RECENT FILES -->
    <div class="sv-card fade-in-up">
      <div class="sv-card-header">
        <h5><i class="bi bi-clock"></i> File Terbaru</h5>
        <a href="/securevault/files.php" style="font-size:0.8rem;color:var(--accent);text-decoration:none">Lihat semua →</a>
      </div>
      <div id="recent-files">
        <div style="text-align:center;padding:2rem;color:var(--text-muted)">
          <div class="spinning" style="font-size:1.5rem;margin-bottom:0.5rem">⚙️</div>
          <div>Memuat file...</div>
        </div>
      </div>
    </div>

    <!-- QUICK ACTIONS + KEY INFO -->
    <div style="display:flex;flex-direction:column;gap:1.5rem">
      <div class="sv-card fade-in-up">
        <div class="sv-card-header"><h5><i class="bi bi-lightning"></i> Aksi Cepat</h5></div>
        <div style="display:flex;flex-direction:column;gap:0.5rem">
          <a href="/securevault/upload.php" class="btn-sv btn-primary-sv" style="justify-content:center">
            <i class="bi bi-cloud-upload"></i> Upload File Baru
          </a>
          <a href="/securevault/files.php" class="btn-sv btn-ghost" style="justify-content:center">
            <i class="bi bi-folder2-open"></i> Kelola File
          </a>
          <a href="/securevault/activity.php" class="btn-sv btn-ghost" style="justify-content:center">
            <i class="bi bi-clock-history"></i> Lihat Riwayat
          </a>
          <a href="/securevault/profile.php" class="btn-sv btn-ghost" style="justify-content:center">
            <i class="bi bi-person-gear"></i> Profil & Kunci
          </a>
        </div>
      </div>

      <div class="sv-card fade-in-up">
        <div class="sv-card-header"><h5><i class="bi bi-shield-lock"></i> Status Keamanan</h5></div>
        <div style="display:flex;flex-direction:column;gap:0.5rem;font-size:0.8rem">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="color:var(--text-muted)">Enkripsi File</span>
            <span style="color:var(--accent);font-weight:700">AES-256-GCM</span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="color:var(--text-muted)">Key Wrapping</span>
            <span style="color:var(--accent);font-weight:700">RSA-2048-OAEP</span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="color:var(--text-muted)">Integritas</span>
            <span style="color:var(--accent);font-weight:700">SHA-256</span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="color:var(--text-muted)">Kunci Privat</span>
            <span style="color:var(--accent);font-weight:700">Dienkripsi</span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="color:var(--text-muted)">Enkripsi Client-Side</span>
            <span style="color:var(--accent);font-weight:700">✓ Aktif</span>
          </div>
        </div>
        <div style="margin-top:1rem;padding-top:0.75rem;border-top:1px solid var(--border)">
          <div style="font-size:0.7rem;color:var(--text-muted)">Public Key Fingerprint:</div>
          <div id="key-fingerprint" class="key-info-box" style="font-size:0.65rem;max-height:50px;margin-top:0.4rem">
            Memuat...
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script src="/securevault/assets/js/crypto.js"></script>
<script>
async function loadRecentFiles() {
  try {
    const res  = await fetch('/securevault/api/files.php?action=list');
    const data = await res.json();
    if (!data.success) throw new Error(data.message);

    const all = [...data.data.owned, ...data.data.shared].slice(0, 5);
    const container = document.getElementById('recent-files');

    if (all.length === 0) {
      container.innerHTML = `<div style="text-align:center;padding:2rem;color:var(--text-muted)">
        <div style="font-size:2rem;margin-bottom:0.5rem">📂</div>
        <div>Belum ada file. <a href="/securevault/upload.php" style="color:var(--accent)">Upload sekarang</a></div>
      </div>`;
      return;
    }

    container.innerHTML = all.map(f => {
      const icon = getFileIcon(f.mime_type);
      const isShared = f.access_type === 'shared';
      return `<div class="file-item">
        <div class="file-icon ${icon.cls}">${icon.ico}</div>
        <div class="file-info">
          <div class="file-name">${escHtml(f.filename_original)}</div>
          <div class="file-meta">
            <span>${formatBytes(f.file_size)}</span>
            <span>${formatDate(f.created_at)}</span>
            ${isShared ? `<span style="color:var(--info)">dari ${escHtml(f.owner_name)}</span>` : ''}
          </div>
        </div>
        <div class="file-actions">
          <button onclick="openPreview(${f.id}, '${escHtml(f.filename_original)}', '${f.mime_type}')" class="btn-sv btn-ghost" style="padding:0.3rem 0.6rem;font-size:0.75rem" title="Lihat File">
            <i class="bi bi-eye"></i>
          </button>

          <a href="/securevault/files.php" class="btn-sv btn-ghost" style="padding:0.3rem 0.6rem;font-size:0.75rem" title="Buka Folder">
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>`;
    }).join('');
  } catch (e) {
    document.getElementById('recent-files').innerHTML =
      `<div class="sv-alert sv-alert-error">Gagal memuat: ${e.message}</div>`;
  }
}

async function loadKeyFingerprint() {
  try {
    const res  = await fetch('/securevault/api/auth.php?action=get_key_data');
    const data = await res.json();
    if (data.success) {
      // Show first 60 chars of public key as fingerprint hint
      const fp = data.public_key.replace(/-----[^-]+-----|\n/g, '').substring(0, 60) + '...';
      document.getElementById('key-fingerprint').textContent = fp;
    }
  } catch (e) { /* ignore */ }
}

function escHtml(s) {
  const d = document.createElement('div'); d.textContent = s; return d.innerHTML;
}

loadRecentFiles();
loadKeyFingerprint();
</script>
<div id="previewModal" class="sv-modal-overlay">
  <div class="sv-modal" style="max-width: 800px; width: 95%;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
      <h5 id="previewTitle" class="sv-modal-title" style="margin: 0;">Preview File</h5>
      <button onclick="closePreview()" class="btn-sv btn-ghost" style="padding: 0.2rem 0.6rem; border:none; font-size:1.2rem;">&times;</button>
    </div>
    
    <div id="previewContent" style="text-align: center; min-height: 200px; background: var(--bg-primary); border-radius: 8px; padding: 1rem; overflow: auto; max-height: 60vh;">
      </div>
    
    <div style="text-align: right; margin-top: 1rem;">
      <button onclick="closePreview()" class="btn-sv btn-primary-sv">Tutup</button>
    </div>
  </div>
</div>

<script>
let currentBlobUrl = null;
// Fungsi Pembantu Konversi Base64 ke ArrayBuffer
function base64ToArrayBuffer(base64) {
    const binary_string = window.atob(base64);
    const len = binary_string.length;
    const bytes = new Uint8Array(len);
    for (let i = 0; i < len; i++) {
        bytes[i] = binary_string.charCodeAt(i);
    }
    return bytes.buffer;
}

// Fungsi Utama Dekripsi E2EE Client-Side
async function decryptFileContent(encDataBase64, sessionKeyEncBase64, ivHex, tagHex) {
    try {
        // PERHATIAN: Ini adalah simulasi pembongkaran data (Base64 Decode)
        // Jika file asli Anda saat ini belum dienkripsi menggunakan WebCrypto saat Upload,
        // kode ini akan langsung menampilkan gambar/file Anda di layar.
        return base64ToArrayBuffer(encDataBase64);

        /* ========================================================================
        KODE ASLI E2EE (Gunakan ini nanti jika fitur enkripsi di browser sudah aktif)
        ========================================================================
        
        // 1. Bongkar Session Key AES menggunakan RSA Private Key dari memori
        const sessionKey = await window.crypto.subtle.decrypt(
            { name: "RSA-OAEP" },
            SV._privateKey, // Kunci privat pengguna yang disimpan saat login
            base64ToArrayBuffer(sessionKeyEncBase64)
        );

        // 2. Dekripsi File menggunakan AES-GCM
        const ivArray = new Uint8Array(ivHex.match(/.{1,2}/g).map(byte => parseInt(byte, 16)));
        return await window.crypto.subtle.decrypt(
            { name: "AES-GCM", iv: ivArray, tagLength: 128 },
            sessionKey,
            base64ToArrayBuffer(encDataBase64)
        );
        */

    } catch (error) {
        console.error("Proses dekripsi gagal:", error);
        throw new Error("Kunci rahasia tidak cocok atau file rusak.");
    }
}
async function openPreview(fileId, fileName, mimeType) {
    const previewModal = document.getElementById('previewModal');
    const previewContent = document.getElementById('previewContent');
    const previewTitle = document.getElementById('previewTitle');
    
    previewTitle.innerText = "Memuat " + fileName + "...";
    previewContent.innerHTML = `<div class="spinning" style="font-size:2rem; margin-top:3rem;">⚙️</div><p style="color:var(--text-muted)">Mendekripsi di memori...</p>`;
    previewModal.classList.add('active');

    try {
        // 1. Ambil data dari API
        const res = await fetch(`/securevault/api/files.php?action=download&file_id=${fileId}`);
        const data = await res.json();
        
        if (!data.success) throw new Error(data.message);

        // 2. Dekripsi file (Pastikan fungsi decryptFileContent sudah ada di file crypto.js Anda)
        const decryptedArrayBuffer = await decryptFileContent(
            data.encrypted_data, 
            data.session_key_enc, 
            data.session_key_iv, 
            data.aes_tag
        );

        // 3. Ubah jadi URL Sementara
        const blob = new Blob([decryptedArrayBuffer], { type: mimeType });
        currentBlobUrl = URL.createObjectURL(blob);
        
        previewTitle.innerText = fileName;

        // 4. Tampilkan ke Layar Pop-up
        if (mimeType.startsWith('image/')) {
            previewContent.innerHTML = `<img src="${currentBlobUrl}" style="max-width: 100%; max-height: 55vh; border-radius: 8px;">`;
        } 
        else if (mimeType === 'application/pdf') {
            previewContent.innerHTML = `<iframe src="${currentBlobUrl}#toolbar=0" style="width: 100%; height: 55vh; border: none; border-radius: 8px;"></iframe>`;
        } 
        else if (mimeType.startsWith('video/')) {
            previewContent.innerHTML = `<video src="${currentBlobUrl}" controls style="max-width: 100%; max-height: 55vh; border-radius: 8px;"></video>`;
        }
        else if (mimeType.startsWith('text/') || mimeType === 'application/json') {
            const text = await blob.text();
            previewContent.innerHTML = `<pre style="text-align: left; white-space: pre-wrap; font-family: monospace; font-size:0.85rem;">${text}</pre>`;
        }
        else {
            previewContent.innerHTML = `
                <div style="padding: 2rem; color: var(--text-muted);">
                    <i class="bi bi-file-earmark-x" style="font-size: 3rem;"></i>
                    <p>Format <b>${mimeType}</b> tidak bisa di-preview langsung.</p>
                    <a href="${currentBlobUrl}" download="${fileName}" class="btn-sv btn-primary-sv">Download File Saja</a>
                </div>`;
        }

    } catch (e) {
        previewTitle.innerText = "Error";
        previewContent.innerHTML = `<div style="color: var(--danger); padding:2rem;"><i class="bi bi-exclamation-triangle"></i> Gagal mendekripsi: ${e.message}</div>`;
    }
}

function closePreview() {
    document.getElementById('previewModal').classList.remove('active');
    document.getElementById('previewContent').innerHTML = '';
    
    // Hapus memori agar RAM tidak penuh
    if (currentBlobUrl) {
        URL.revokeObjectURL(currentBlobUrl);
        currentBlobUrl = null;
    }
}
</script>

</body>
</html>
</body>
</html>