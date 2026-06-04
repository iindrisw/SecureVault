// assets/js/crypto.js - Client-Side Cryptography using Web Crypto API
// All file encryption/decryption happens here — server never sees plaintext

'use strict';

const SV = {

  // ── UTILITY ──────────────────────────────────────────

  buf2hex: (buffer) => Array.from(new Uint8Array(buffer))
    .map(b => b.toString(16).padStart(2, '0')).join(''),

  hex2buf: (hex) => new Uint8Array(hex.match(/.{2}/g).map(b => parseInt(b, 16))),

  // ── UTILITY (Ganti buf2b64 dan b64toBuf dengan ini di crypto.js) ──────────

  buf2b64: (buffer) => {
    let binary = '';
    const bytes = new Uint8Array(buffer);
    const len = bytes.byteLength;
    // Memproses per 64KB chunk agar aman dari stack overflow
    const chunkSize = 0x10000; 
    for (let i = 0; i < len; i += chunkSize) {
      binary += String.fromCharCode.apply(null, bytes.subarray(i, Math.min(i + chunkSize, len)));
    }
    return btoa(binary);
  },

  b64toBuf: (b64) => {
    const bin = atob(b64);
    const len = bin.length;
    const bytes = new Uint8Array(len);
    for (let i = 0; i < len; i++) {
      bytes[i] = bin.charCodeAt(i);
    }
    return bytes.buffer;
  },

  str2buf: (str) => new TextEncoder().encode(str).buffer,
  buf2str: (buf) => new TextDecoder().decode(buf),

  // ── RSA KEY PAIR GENERATION ───────────────────────────

  /**
   * Generate RSA-OAEP 2048-bit key pair in browser
   * Returns { publicKeyPem, privateKeyPem, publicKey, privateKey }
   */
  generateKeyPair: async () => {
    const keyPair = await crypto.subtle.generateKey(
      { name: 'RSA-OAEP', modulusLength: 2048, publicExponent: new Uint8Array([1, 0, 1]), hash: 'SHA-256' },
      true,
      ['encrypt', 'decrypt']
    );

    const pubExport  = await crypto.subtle.exportKey('spki', keyPair.publicKey);
    const privExport = await crypto.subtle.exportKey('pkcs8', keyPair.privateKey);

    const pubB64  = SV.buf2b64(pubExport);
    const privB64 = SV.buf2b64(privExport);

    const publicKeyPem  = `-----BEGIN PUBLIC KEY-----\n${pubB64.match(/.{1,64}/g).join('\n')}\n-----END PUBLIC KEY-----`;
    const privateKeyPem = `-----BEGIN PRIVATE KEY-----\n${privB64.match(/.{1,64}/g).join('\n')}\n-----END PRIVATE KEY-----`;

    return { publicKeyPem, privateKeyPem, publicKey: keyPair.publicKey, privateKey: keyPair.privateKey };
  },

  // ── RSA KEY IMPORT ────────────────────────────────────

  importPublicKey: async (pem) => {
    const b64 = pem.replace(/-----[^-]+-----/g, '').replace(/\s/g, '');
    const buf = SV.b64toBuf(b64);
    return await crypto.subtle.importKey('spki', buf, { name: 'RSA-OAEP', hash: 'SHA-256' }, false, ['encrypt']);
  },

  importPrivateKey: async (pem) => {
    const b64 = pem.replace(/-----[^-]+-----/g, '').replace(/\s/g, '');
    const buf = SV.b64toBuf(b64);
    return await crypto.subtle.importKey('pkcs8', buf, { name: 'RSA-OAEP', hash: 'SHA-256' }, false, ['decrypt']);
  },

  // ── PRIVATE KEY ENCRYPTION (PBKDF2 + AES-CBC) ────────

  /**
   * Encrypt PEM private key with user password for server storage
   */
  encryptPrivateKeyWithPassword: async (privateKeyPem, password) => {
    const salt = crypto.getRandomValues(new Uint8Array(16));
    const iv   = crypto.getRandomValues(new Uint8Array(16));

    const passKey = await crypto.subtle.importKey('raw', SV.str2buf(password), 'PBKDF2', false, ['deriveKey']);
    const aesKey  = await crypto.subtle.deriveKey(
      { name: 'PBKDF2', salt, iterations: 100000, hash: 'SHA-256' },
      passKey,
      { name: 'AES-CBC', length: 256 },
      false,
      ['encrypt']
    );

    const enc = await crypto.subtle.encrypt({ name: 'AES-CBC', iv }, aesKey, SV.str2buf(privateKeyPem));

    return {
      encrypted: SV.buf2b64(enc),
      iv:        SV.buf2hex(iv),
      salt:      SV.buf2hex(salt),
    };
  },

  /**
   * Decrypt PEM private key with user password
   */
  decryptPrivateKeyWithPassword: async (encryptedB64, ivHex, saltHex, password) => {
    const salt = SV.hex2buf(saltHex);
    const iv   = SV.hex2buf(ivHex);

    const passKey = await crypto.subtle.importKey('raw', SV.str2buf(password), 'PBKDF2', false, ['deriveKey']);
    const aesKey  = await crypto.subtle.deriveKey(
      { name: 'PBKDF2', salt, iterations: 100000, hash: 'SHA-256' },
      passKey,
      { name: 'AES-CBC', length: 256 },
      false,
      ['decrypt']
    );

    const dec = await crypto.subtle.decrypt({ name: 'AES-CBC', iv }, aesKey, SV.b64toBuf(encryptedB64));
    return SV.buf2str(dec);
  },

  // ── HYBRID FILE ENCRYPTION (AES-256-GCM + RSA OAEP) ──

  /**
   * Encrypt file data with AES-256-GCM
   * Returns { encryptedData, sessionKey (raw bytes), iv (hex), tag (hex) }
   */
  encryptFile: async (fileArrayBuffer, recipientPublicKey) => {
    // Generate random 256-bit session key
    const sessionKey = await crypto.subtle.generateKey({ name: 'AES-GCM', length: 256 }, true, ['encrypt', 'decrypt']);
    const iv         = crypto.getRandomValues(new Uint8Array(12)); // 96-bit IV for GCM

    // Encrypt file data
    const encryptedWithTag = await crypto.subtle.encrypt(
      { name: 'AES-GCM', iv, tagLength: 128 },
      sessionKey,
      fileArrayBuffer
    );

    // The last 16 bytes of AES-GCM output is the auth tag
    const encData  = encryptedWithTag.slice(0, encryptedWithTag.byteLength - 16);
    const tag      = encryptedWithTag.slice(encryptedWithTag.byteLength - 16);

    // Export session key and wrap with recipient's RSA public key
    const rawSessionKey  = await crypto.subtle.exportKey('raw', sessionKey);
    const wrappedKeyBuf  = await crypto.subtle.encrypt({ name: 'RSA-OAEP' }, recipientPublicKey, rawSessionKey);

    return {
      encryptedData:    encData,
      sessionKeyWrapped: SV.buf2b64(wrappedKeyBuf),
      iv:               SV.buf2hex(iv),
      tag:              SV.buf2hex(tag),
    };
  },

  /**
   * Decrypt file data
   */
  decryptFile: async (encryptedData, tagHex, sessionKeyWrappedB64, ivHex, userPrivateKey) => {
    // Unwrap session key with user's RSA private key
    const wrappedKey    = SV.b64toBuf(sessionKeyWrappedB64);
    const rawSessionKey = await crypto.subtle.decrypt({ name: 'RSA-OAEP' }, userPrivateKey, wrappedKey);

    const sessionKey = await crypto.subtle.importKey('raw', rawSessionKey, { name: 'AES-GCM', length: 256 }, false, ['decrypt']);

    const iv  = SV.hex2buf(ivHex);
    const tag = SV.hex2buf(tagHex);

    // Recombine ciphertext + tag for AES-GCM
    const combined = new Uint8Array(encryptedData.byteLength + tag.byteLength);
    combined.set(new Uint8Array(encryptedData), 0);
    combined.set(tag, encryptedData.byteLength);

    const decrypted = await crypto.subtle.decrypt(
      { name: 'AES-GCM', iv, tagLength: 128 },
      sessionKey,
      combined
    );

    return decrypted;
  },

  // ── KEY WRAPPING FOR SHARING ──────────────────────────

  /**
   * Re-wrap session key for a recipient (for file sharing)
   * Requires current user's private key and recipient's public key
   */
  rewrapSessionKey: async (wrappedKeyB64, userPrivateKey, recipientPublicKey) => {
    // Unwrap with owner's private key
    const wrappedKey    = SV.b64toBuf(wrappedKeyB64);
    const rawSessionKey = await crypto.subtle.decrypt({ name: 'RSA-OAEP' }, userPrivateKey, wrappedKey);

    // Re-wrap with recipient's public key
    const reWrapped = await crypto.subtle.encrypt({ name: 'RSA-OAEP' }, recipientPublicKey, rawSessionKey);
    return SV.buf2b64(reWrapped);
  },

  // ── INTEGRITY ─────────────────────────────────────────

  hashArrayBuffer: async (buffer) => {
    const hashBuf = await crypto.subtle.digest('SHA-256', buffer);
    return SV.buf2hex(hashBuf);
  },

  // ── SESSION KEY CACHE ─────────────────────────────────
  // Store decrypted private key in memory during session (cleared on logout)
  _privateKey: null,

  setPrivateKey: (key) => { SV._privateKey = key; },
  getPrivateKey: () => SV._privateKey,
  clearPrivateKey: () => { SV._privateKey = null; },
};

// ── UI HELPERS ───────────────────────────────────────────

function showAlert(msg, type = 'info', containerId = 'alert-container') {
  const container = document.getElementById(containerId);
  if (!container) return;
  const icons = { success: '✓', error: '✕', warning: '⚠', info: 'ℹ' };
  container.innerHTML = `
    <div class="sv-alert sv-alert-${type}">
      <span>${icons[type] || 'ℹ'}</span>
      <span>${msg}</span>
    </div>`;
  container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function showModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('active');
}

function hideModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('active');
}

function formatBytes(bytes) {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
}

function getFileIcon(mime) {
  if (!mime) return { cls: 'other', ico: '📄' };
  if (mime.startsWith('image/')) return { cls: 'img', ico: '🖼️' };
  if (mime === 'application/pdf') return { cls: 'pdf', ico: '📕' };
  if (mime.includes('word') || mime.includes('document')) return { cls: 'doc', ico: '📝' };
  if (mime.includes('text')) return { cls: 'doc', ico: '📄' };
  return { cls: 'other', ico: '📦' };
}

// Close modal when clicking backdrop
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('sv-modal-overlay')) {
    e.target.classList.remove('active');
  }
});