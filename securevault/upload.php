<?php
// upload.php — Encrypted File Upload
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
  <title>Upload — SecureVault</title>
  <link rel="stylesheet" href="/securevault/assets/css/style.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<nav class="sv-navbar">
  <a href="/securevault/dashboard.php" class="sv-brand"><div class="lock-icon">🔐</div> SecureVault</a>
  <div class="sv-nav-links">
    <a href="/securevault/dashboard.php"><i class="bi bi-grid"></i> Dashboard</a>
    <a href="/securevault/files.php"><i class="bi bi-folder"></i> File Saya</a>
    <a href="/securevault/upload.php" class="active"><i class="bi bi-cloud-upload"></i> Upload</a>
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

<main class="sv-main" style="max-width:700px">
  <div class="page-header fade-in-up">
    <h1><i class="bi bi-cloud-upload"></i> Upload File Terenkripsi</h1>
    <p>File dienkripsi sepenuhnya di browser Anda sebelum dikirim ke server.</p>
  </div>

  <div id="alert-container"></div>

  <!-- ENCRYPTION FLOW INFO -->
  <div class="sv-card fade-in-up" style="margin-bottom:1.5rem;background:rgba(0,212,170,0.03)">
    <div style="display:flex;gap:1.5rem;justify-content:center;flex-wrap:wrap;font-size:0.8rem">
      <div style="text-align:center;color:var(--text-muted)">
        <div style="font-size:1.5rem;margin-bottom:0.3rem">📁</div>
        <div>1. Pilih File</div>
      </div>
      <div style="display:flex;align-items:center;color:var(--border)">→</div>
      <div style="text-align:center;color:var(--text-muted)">
        <div style="font-size:1.5rem;margin-bottom:0.3rem">🔑</div>
        <div>2. Generate AES Session Key</div>
      </div>
      <div style="display:flex;align-items:center;color:var(--border)">→</div>
      <div style="text-align:center;color:var(--text-muted)">
        <div style="font-size:1.5rem;margin-bottom:0.3rem">🔒</div>
        <div>3. Enkripsi AES-256-GCM</div>
      </div>
      <div style="display:flex;align-items:center;color:var(--border)">→</div>
      <div style="text-align:center;color:var(--text-muted)">
        <div style="font-size:1.5rem;margin-bottom:0.3rem">🔐</div>
        <div>4. Key Wrap RSA-OAEP</div>
      </div>
      <div style="display:flex;align-items:center;color:var(--border)">→</div>
      <div style="text-align:center;color:var(--accent)">
        <div style="font-size:1.5rem;margin-bottom:0.3rem">☁️</div>
        <div>5. Upload Ciphertext</div>
      </div>
    </div>
  </div>

  <!-- UPLOAD ZONE -->
 <div class="sv-card fade-in-up">
    <label class="upload-zone" id="upload-zone" for="file-input" style="display: block; cursor: pointer;">
      
      <input type="file" id="file-input" multiple accept="*/*" style="display:none;">
      
      <div class="upload-icon">☁️</div>
      <div class="upload-text">
        <strong>Klik untuk pilih file</strong> atau drag & drop di sini
      </div>
      <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.5rem">Semua tipe file · Maks. 50MB per file</div>
    </label>
  </div>

  <!-- FILE QUEUE -->
  <div id="file-queue" style="margin-top:1rem"></div>

  <!-- UPLOAD BUTTON -->
  <div id="upload-controls" style="display:none;margin-top:1rem">
    <button onclick="startUpload()" id="btn-upload" class="btn-sv btn-primary-sv" style="width:100%;justify-content:center;padding:0.8rem">
      <i class="bi bi-shield-lock"></i> Enkripsi & Upload Semua File
    </button>
  </div>

  <!-- PASSWORD INPUT (needed to unlock private key for key wrapping) -->
  <div id="password-section" style="margin-top:1rem;display:none">
    <div class="sv-card">
      <div style="font-size:0.85rem;color:var(--text-muted);margin-bottom:0.75rem">
        <i class="bi bi-key" style="color:var(--accent)"></i>
        Masukkan password untuk membuka kunci privat (diperlukan untuk key wrapping RSA-OAEP):
      </div>
      <div style="display:flex;gap:0.5rem">
        <input type="password" id="upload-password" class="sv-input" placeholder="Password Anda" style="flex:1">
        <button onclick="confirmPassword()" class="btn-sv btn-primary-sv">
          <i class="bi bi-unlock"></i> Buka Kunci
        </button>
      </div>
    </div>
  </div>
</main>

<script src="/securevault/assets/js/crypto.js"></script>
<script>
let pendingFiles  = [];
let userPublicKey = null;
let passwordResolve = null;

// ── DRAG & DROP ───────────────────────────────────
const zone = document.getElementById('upload-zone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
zone.addEventListener('drop', e => {
  e.preventDefault(); zone.classList.remove('dragover');
  addFiles([...e.dataTransfer.files]);
});

document.getElementById('file-input').addEventListener('change', e => {
  addFiles([...e.target.files]);
  e.target.value = '';
});

function addFiles(files) {
  files.forEach(f => {
    if (f.size > 50 * 1024 * 1024) { showAlert(`File "${f.name}" melebihi 50MB.`, 'warning'); return; }
    pendingFiles.push({ file: f, id: Date.now() + Math.random() });
  });
  renderQueue();
}

function renderQueue() {
  const container = document.getElementById('file-queue');
  const controls  = document.getElementById('upload-controls');

  if (pendingFiles.length === 0) {
    container.innerHTML = '';
    controls.style.display = 'none';
    return;
  }

  controls.style.display = 'block';
  container.innerHTML = pendingFiles.map((pf, i) => {
    const icon = getFileIcon(pf.file.type);
    return `<div class="file-item" id="pf-${pf.id}">
      <div class="file-icon ${icon.cls}">${icon.ico}</div>
      <div class="file-info">
        <div class="file-name">${escHtml(pf.file.name)}</div>
        <div class="file-meta">
          <span>${formatBytes(pf.file.size)}</span>
          <span>${pf.file.type || 'unknown'}</span>
        </div>
        <div class="sv-progress" id="prog-${pf.id}" style="margin-top:0.4rem;display:none">
          <div class="sv-progress-bar" id="prog-bar-${pf.id}" style="width:0%"></div>
        </div>
        <div id="status-${pf.id}" style="font-size:0.75rem;color:var(--text-muted);margin-top:0.2rem"></div>
      </div>
      <button onclick="removeFile('${pf.id}')" class="btn-sv btn-ghost" style="padding:0.3rem 0.5rem">
        <i class="bi bi-x"></i>
      </button>
    </div>`;
  }).join('');
}

function removeFile(id) {
  pendingFiles = pendingFiles.filter(pf => pf.id.toString() !== id.toString());
  renderQueue();
}

// ── UNLOCK PRIVATE KEY (Ganti fungsi yang lama dengan ini) ────────────────
async function getPrivateKey() {
  if (SV.getPrivateKey()) return SV.getPrivateKey();

  // Tampilkan section password jika belum terbuka
  const passwordSection = document.getElementById('password-section');
  passwordSection.style.display = 'block'; 
  passwordSection.scrollIntoView({ behavior: 'smooth' });

  return new Promise((resolve, reject) => {
    passwordResolve = { resolve, reject };
  });
}

async function confirmPassword() {
  const password = document.getElementById('upload-password').value;
  if (!password) return;

  try {
    const res  = await fetch('/securevault/api/auth.php?action=get_key_data');
    const data = await res.json();

    const privKeyPem = await SV.decryptPrivateKeyWithPassword(
      data.private_key_enc, data.private_key_iv, data.private_key_salt, password
    );
    const privKey = await SV.importPrivateKey(privKeyPem);
    userPublicKey = await SV.importPublicKey(data.public_key);
    SV.setPrivateKey(privKey);

    document.getElementById('password-section').style.display = 'none';
    if (passwordResolve) { passwordResolve.resolve(privKey); passwordResolve = null; }
  } catch (e) {
    showAlert('Password salah atau gagal membuka kunci: ' + e.message, 'error');
    if (passwordResolve) { passwordResolve.reject(e); passwordResolve = null; }
  }
}

// ── LOAD PUBLIC KEY ───────────────────────────────
async function loadPublicKey() {
  if (userPublicKey) return userPublicKey;
  const res  = await fetch('/securevault/api/auth.php?action=get_key_data');
  const data = await res.json();
  userPublicKey = await SV.importPublicKey(data.public_key);
  return userPublicKey;
}

// ── START UPLOAD ──────────────────────────────────
async function startUpload() {
  console.log("=== MEMULAI PROSES UPLOAD ===");
  if (pendingFiles.length === 0) {
    console.log("Gagal: Tidak ada file di dalam antrean (pendingFiles kosong).");
    return;
  }

  const btn = document.getElementById('btn-upload');
  btn.disabled = true;

  try {
    console.log("Mencoba mengambil Kunci Privat via getPrivateKey()...");
    await getPrivateKey();
    console.log("Kunci Privat berhasil didapatkan!");
  } catch (e) {
    console.log("Error saat mengambil kunci privat:", e);
    btn.disabled = false;
    showAlert('Kunci privat diperlukan untuk enkripsi.', 'error');
    return;
  }

  console.log("Mencoba memuat Kunci Publik via loadPublicKey()...");
  const pubKey = await loadPublicKey();
  console.log("Kunci Publik berhasil dimuat:", pubKey);
  
  let successCount = 0;
  console.log(`Menjalankan loop untuk mengupload ${pendingFiles.length} file...`);

  for (const pf of pendingFiles) {
    try {
      console.log(`Memulai upload untuk file ID: ${pf.id}, Nama: ${pf.file.name}`);
      await uploadSingleFile(pf, pubKey);
      successCount++;
      console.log(`File ${pf.file.name} sukses diupload.`);
    } catch (e) {
      console.log(`Gagal mengupload file ${pf.file.name}. Pesan error:`, e);
      document.getElementById('status-' + pf.id).textContent = '✕ Gagal: ' + e.message;
      document.getElementById('status-' + pf.id).style.color = 'var(--danger)';
    }
  }

  console.log(`Selesai memproses semua file. Sukses: ${successCount}/${pendingFiles.length}`);
  showAlert(`✓ ${successCount}/${pendingFiles.length} file berhasil diupload dan dienkripsi!`, 'success');
  
  pendingFiles = pendingFiles.filter(pf => {
    const s = document.getElementById('status-' + pf.id);
    return s && s.style.color === 'var(--danger)';
  });
  renderQueue();
  btn.disabled = false;
}

async function uploadSingleFile(pf, pubKey) {
  const setStatus  = (t, c) => { const el = document.getElementById('status-' + pf.id); el.textContent = t; el.style.color = c || 'var(--text-muted)'; };
  const setProgress = (p)   => {
    document.getElementById('prog-' + pf.id).style.display = '';
    document.getElementById('prog-bar-' + pf.id).style.width = p + '%';
  };

  // Read file
  setStatus('Membaca file...'); setProgress(10);
  const buf = await pf.file.arrayBuffer();

  // Hash original
  setStatus('Menghitung hash SHA-256...'); setProgress(20);
  const originalHash = await SV.hashArrayBuffer(buf);

  // Encrypt
  setStatus('Mengenkripsi AES-256-GCM...'); setProgress(35);
  const enc = await SV.encryptFile(buf, pubKey);
  setProgress(65);

  // Prepare upload
  setStatus('Mengupload ciphertext...'); setProgress(75);
  const encB64 = SV.buf2b64(enc.encryptedData);

  const fd = new FormData();
  fd.append('action', 'upload');
  fd.append('filename', pf.file.name);
  fd.append('mime_type', pf.file.type || 'application/octet-stream');
  fd.append('file_size', pf.file.size);
  fd.append('encrypted_data', encB64);
  fd.append('session_key_enc', enc.sessionKeyWrapped);
  fd.append('session_key_iv', enc.iv);
  fd.append('aes_tag', enc.tag);
  fd.append('original_hash', originalHash);

  setProgress(85);
  const res  = await fetch('/securevault/api/files.php', { method: 'POST', body: fd });
  const data = await res.json();

  if (!data.success) throw new Error(data.message);

  setProgress(100);
  setStatus('✓ Upload berhasil!', 'var(--accent)');

  // Visual: green border
  const row = document.getElementById('pf-' + pf.id);
  if (row) { row.style.borderColor = 'var(--accent)'; }
}

function escHtml(s) {
  const d = document.createElement('div'); d.textContent = s; return d.innerHTML;
}
</script>
</body>
</html>