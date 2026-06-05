<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif}

body{
    min-height:100vh;
    background:
        radial-gradient(circle at 20% 30%, rgba(59,130,246,0.25), transparent),
        radial-gradient(circle at 80% 70%, rgba(16,185,129,0.2), transparent),
        linear-gradient(135deg, #dbeafe, #f0fdf4);
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
}

/* ── ALERT ── */
.alert{
    position:fixed;top:24px;left:50%;transform:translateX(-50%) translateY(-120%);
    padding:14px 24px 14px 20px;border-radius:12px;
    display:flex;align-items:center;gap:10px;
    font-size:14px;font-weight:500;
    z-index:9999;
    transition:transform .4s cubic-bezier(.4,0,.2,1),opacity .4s;
    opacity:0;
    box-shadow:0 12px 40px rgba(0,0,0,.25);
    max-width:90vw;
}
.alert.show{transform:translateX(-50%) translateY(0);opacity:1}
.alert svg{flex-shrink:0;width:20px;height:20px}
.alert-error{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca}
.alert-success{background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0}

/* ── CONTAINER ── */
.container{
    width:100%;max-width:960px;
    display:flex;border-radius:20px;overflow:hidden;
    background:#fff;
    box-shadow:0 30px 60px rgba(0,0,0,.4), 0 0 80px rgba(37,99,235,0.15);
    animation:fadeUp .6s ease both;
    transition:transform .5s cubic-bezier(.4,0,.2,1), opacity .4s;
}
.container.exit{transform:translateX(-100%);opacity:0}

/* ── LEFT (FORM) ── */
.left{
    flex:3;padding:44px 40px;
    display:flex;flex-direction:column;justify-content:center;
}
.left h2{
    font-size:26px;font-weight:700;color:#0f172a;margin-bottom:4px;
}
.left .subtitle{
    font-size:13px;color:#94a3b8;margin-bottom:24px;
}

/* ── INPUT GROUP ── */
.input-group{
    position:relative;
    margin-bottom:6px;
}
.input-group input,
.input-group textarea{
    width:100%;padding:12px 14px 12px 40px;
    border-radius:10px;border:1.5px solid #e2e8f0;
    background:#ffffff;font-size:13.5px;color:#0f172a;
    outline:none;transition:.25s;resize:none;
}
.input-group input::placeholder,
.input-group textarea::placeholder{color:#94a3b8}
.input-group input:focus,
.input-group textarea:focus{
    border-color:#22c55e;
    box-shadow:0 0 0 3px rgba(34,197,94,.15);
}

/* ── FIELD ERROR STATE ── */
.input-group.has-error input,
.input-group.has-error textarea{
    border-color:#ef4444;
    background:#fef2f2;
    box-shadow:0 0 0 3px rgba(239,68,68,.1);
}
.input-group.has-error input:focus,
.input-group.has-error textarea:focus{
    border-color:#ef4444;
    box-shadow:0 0 0 3px rgba(239,68,68,.15);
}
.input-group.has-error .icon-left{color:#ef4444}

/* ── ERROR MESSAGE ── */
.field-error{
    display:none;
    align-items:flex-start;
    gap:5px;
    margin-top:5px;
    margin-bottom:10px;
    padding:0 2px;
}
.field-error.visible{
    display:flex;
}
.field-error svg{
    flex-shrink:0;
    width:15px;height:15px;
    color:#ef4444;
    margin-top:1px;
}
.field-error span{
    font-size:12px;
    font-weight:500;
    color:#dc2626;
    line-height:1.4;
}

/* ── ICONS ── */
.icon-left{
    position:absolute;left:13px;top:15px;
    width:17px;height:17px;color:#94a3b8;pointer-events:none;
    transition:.2s;
}
.input-group input:focus~.icon-left,
.input-group textarea:focus~.icon-left{color:#3b82f6}

.toggle{
    position:absolute;right:12px;top:13px;
    cursor:pointer;color:#94a3b8;transition:.2s;
    display:flex;align-items:center;
}
.toggle:hover{color:#64748b}

/* ── SPACER ── */
.field-spacer{
    height:16px;
}

/* ── ROW 2 KOLOM ── */
.row{display:flex;gap:12px}
.row .input-group{flex:1}

/* ── BUTTON ── */
.btn-primary{
    width:100%;padding:13px;border:none;border-radius:10px;
    background:linear-gradient(135deg, #3b82f6, #22c55e);
    color:#fff;font-size:14.5px;font-weight:600;
    cursor:pointer;transition:.3s;margin-top:10px;
}
.btn-primary:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 24px rgba(37,99,235,.35);
}
.btn-primary:active{transform:translateY(0)}

/* ── LOGIN LINK ── */
.login-link{
    text-align:center;margin-top:16px;
    font-size:13.5px;color:#94a3b8;
}
.login-link a{
    color:#2563eb;font-weight:600;text-decoration:none;transition:.2s;
}
.login-link a:hover{color:#1d4ed8;text-decoration:underline}

/* ── RIGHT (WELCOME) ── */
.right{
    flex:2;
    background:linear-gradient(160deg, #22c55e, #3b82f6);
    color:#fff;
    display:flex;flex-direction:column;
    justify-content:center;align-items:center;
    padding:48px 36px;
    position:relative;overflow:hidden;
    text-align:center;
}
.right::before{
    content:'';position:absolute;
    width:350px;height:350px;border-radius:50%;
    background:rgba(255,255,255,.06);
    top:-100px;right:-100px;
}
.right::after{
    content:'';position:absolute;
    width:250px;height:250px;border-radius:50%;
    background:rgba(255,255,255,.04);
    bottom:-80px;left:-60px;
}
.right .icon-circle{
    width:80px;height:80px;border-radius:50%;
    background:rgba(255,255,255,.15);
    display:flex;align-items:center;justify-content:center;
    margin-bottom:24px;position:relative;z-index:1;
    animation:pulse 2s infinite;
}
.right .icon-circle svg{width:36px;height:36px;color:#fff}
.right h2{font-size:26px;font-weight:700;margin-bottom:10px;position:relative;z-index:1}
.right p{font-size:14px;line-height:1.7;opacity:.85;margin-bottom:28px;position:relative;z-index:1;max-width:280px}
.right .btn-outline{
    padding:11px 28px;border-radius:10px;
    border:2px solid rgba(255,255,255,.5);
    background:transparent;color:#fff;
    font-size:14px;font-weight:600;
    text-decoration:none;transition:.3s;
    display:inline-block;position:relative;z-index:1;
}
.right .btn-outline:hover{
    background:#fff;color:#1e40af;border-color:#fff;
    transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.2);
}

/* ── ANIMATION ── */
@keyframes fadeUp{
    from{opacity:0;transform:translateY(20px) scale(.98)}
    to{opacity:1;transform:translateY(0) scale(1)}
}
@keyframes pulse{
    0%,100%{transform:scale(1);box-shadow:0 0 0 0 rgba(255,255,255,.2)}
    50%{transform:scale(1.05);box-shadow:0 0 0 15px rgba(255,255,255,0)}
}

/* ── RESPONSIVE ── */
@media(max-width:860px){
    .container{max-width:480px;flex-direction:column}
    .right{order:0;padding:28px 32px}
    .right .icon-circle{width:60px;height:60px;margin-bottom:16px}
    .right .icon-circle svg{width:28px;height:28px}
    .right h2{font-size:20px}
    .right p{font-size:13px;margin-bottom:18px}
    .right .btn-outline{padding:9px 22px;font-size:13px}
    .left{order:1;padding:28px 28px}
    .left h2{font-size:22px}
    .row{flex-direction:column;gap:0}
}
@media(max-width:440px){
    body{padding:12px}
    .container{border-radius:16px}
    .left{padding:24px 20px}
    .right{padding:24px 20px}
    .left h2{font-size:20px}
    .right h2{font-size:18px}
    .right p{font-size:12px}
}
</style>
</head>
<body>

<!-- ALERT -->
<div class="alert alert-error" id="alertBox">
    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-.75-11.25a.75.75 0 011.5 0v4.5a.75.75 0 01-1.5 0v-4.5zM10 14a1 1 0 100-2 1 1 0 000 2z"/></svg>
    <span id="alertMsg"></span>
</div>

<div class="container" id="mainContainer">

    <!-- FORM -->
    <div class="left">
        <h2>Buat Akun</h2>
        <p class="subtitle">Isi data di bawah untuk memulai</p>

        <form id="registerForm" method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Nama Lengkap -->
            <div class="input-group" id="grp-name">
                <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
                <input type="text" id="name" name="name" placeholder="Nama Lengkap" required>
                <div class="field-error" id="err-name">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 10-2 0v4a1 1 0 102 0V6zm-1 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                    <span></span>
                </div>
            </div>
            <div class="field-spacer"></div>

            <!-- Username -->
            <div class="input-group" id="grp-username">
                <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                <input type="text" id="username" name="username" placeholder="Username" required>
                <div class="field-error" id="err-username">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 10-2 0v4a1 1 0 102 0V6zm-1 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                    <span></span>
                </div>
            </div>
            <div class="field-spacer"></div>

            <!-- Email -->
            <div class="input-group" id="grp-email">
                <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                <input type="email" id="email" name="email" placeholder="Email" required>
                <div class="field-error" id="err-email">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 10-2 0v4a1 1 0 102 0V6zm-1 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                    <span></span>
                </div>
            </div>
            <div class="field-spacer"></div>

            <!-- Nama Toko -->
            <div class="input-group" id="grp-nama_toko">
                <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M3 7l1-4h12l1 4M4 7h12v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7z"/>
                </svg>
                <input type="text" id="nama_toko" name="nama_toko" placeholder="Nama Toko" required>
                <div class="field-error" id="err-nama_toko">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 10-2 0v4a1 1 0 102 0V6zm-1 8a1 1 0 100-2 1 1 0 000 2z"/>
                    </svg>
                    <span></span>
                </div>
            </div>
            <div class="field-spacer"></div>

            <!-- No Telepon -->
            <div class="input-group" id="grp-no_telp">
                <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                <input type="text" id="no_telp" name="no_telp" placeholder="No Telepon (Opsional)">
                <div class="field-error" id="err-no_telp">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 10-2 0v4a1 1 0 102 0V6zm-1 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                    <span></span>
                </div>
            </div>
            <div class="field-spacer"></div>

            <!-- Alamat -->
            <div class="input-group" id="grp-alamat">
                <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                <input type="text" id="alamat" name="alamat" placeholder="Alamat (Opsional)">
                <div class="field-error" id="err-alamat">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 10-2 0v4a1 1 0 102 0V6zm-1 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                    <span></span>
                </div>
            </div>
            <div class="field-spacer"></div>

            <!-- Password + Konfirmasi (sejajar) -->
            <div class="row">
                <div class="input-group" id="grp-password">
                    <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <span class="toggle" onclick="togglePass('password','eye1','eye2')" title="Tampilkan">
                        <svg id="eye1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg id="eye2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </span>
                    <div class="field-error" id="err-password">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 10-2 0v4a1 1 0 102 0V6zm-1 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                        <span></span>
                    </div>
                </div>
                <div class="input-group" id="grp-password_confirmation">
                    <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Konfirmasi Password" required>
                    <span class="toggle" onclick="togglePass('password_confirmation','eye3','eye4')" title="Tampilkan">
                        <svg id="eye3" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg id="eye4" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </span>
                    <div class="field-error" id="err-password_confirmation">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 10-2 0v4a1 1 0 102 0V6zm-1 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                        <span></span>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary">DAFTAR</button>
        </form>

        <div class="login-link">
            Sudah punya akun? <a href="{{ route('login') }}" onclick="goToPage(event, '{{ route('login') }}')">Login di sini</a>
        </div>
    </div>

    <!-- WELCOME -->
    <div class="right">
        <div class="icon-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
        </div>
        <h2>Bergabunglah!</h2>
        <p>Buat akun Anda dan mulai kelola bengkel dengan lebih efisien dan terorganisir.</p>
    </div>
</div>

<script>
// ── Field Error Helpers ──
function setFieldError(fieldId, message) {
    const grp = document.getElementById('grp-' + fieldId);
    const err = document.getElementById('err-' + fieldId);
    if (!grp || !err) return;
    grp.classList.add('has-error');
    err.classList.add('visible');
    err.querySelector('span').textContent = message;
}

function clearFieldError(fieldId) {
    const grp = document.getElementById('grp-' + fieldId);
    const err = document.getElementById('err-' + fieldId);
    if (!grp || !err) return;
    grp.classList.remove('has-error');
    err.classList.remove('visible');
    err.querySelector('span').textContent = '';
}

function clearAllErrors() {
    document.querySelectorAll('.input-group').forEach(grp => {
        grp.classList.remove('has-error');
    });
    document.querySelectorAll('.field-error').forEach(err => {
        err.classList.remove('visible');
        err.querySelector('span').textContent = '';
    });
}

// ── Toggle Password ──
function togglePass(inputId, openId, closeId) {
    const input = document.getElementById(inputId);
    const open = document.getElementById(openId);
    const close = document.getElementById(closeId);
    if (input.type === 'password') {
        input.type = 'text';
        open.style.display = 'none';
        close.style.display = 'block';
    } else {
        input.type = 'password';
        open.style.display = 'block';
        close.style.display = 'none';
    }
}

// ── Alert System ──
function showAlert(msg, type = 'error') {
    const box = document.getElementById('alertBox');
    const msgEl = document.getElementById('alertMsg');
    msgEl.textContent = msg;
    box.className = 'alert alert-' + type + ' show';
    clearTimeout(box._timer);
    box._timer = setTimeout(() => { box.classList.remove('show'); }, 3500);
}

// ── Page Transition ──
function goToPage(e, url) {
    e.preventDefault();
    const container = document.getElementById('mainContainer');
    container.classList.add('exit');
    setTimeout(() => { window.location.href = url; }, 500);
}

// ── Form Validation ──
document.getElementById('registerForm').addEventListener('submit', function(e) {
    clearAllErrors();

    const name = document.getElementById('name');
    const username = document.getElementById('username');
    const email = document.getElementById('email');
    const namaToko = document.getElementById('nama_toko');
    const pass = document.getElementById('password');
    const confirm = document.getElementById('password_confirmation');

    let firstError = null;

    if (!name.value.trim()) {
        setFieldError('name', 'Nama lengkap wajib diisi');
        if (!firstError) firstError = name;
    }

    if (!username.value.trim()) {
        setFieldError('username', 'Username wajib diisi');
        if (!firstError) firstError = username;
    }

    if (!email.value.trim()) {
        setFieldError('email', 'Email wajib diisi');
        if (!firstError) firstError = email;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
        setFieldError('email', 'Format email tidak valid');
        if (!firstError) firstError = email;
    }

    if (!namaToko.value.trim()) {
        setFieldError('nama_toko', 'Nama toko wajib diisi');
        if (!firstError) firstError = namaToko;
    }

    if (!pass.value.trim()) {
        setFieldError('password', 'Password wajib diisi');
        if (!firstError) firstError = pass;
    } else if (pass.value.trim().length < 5) {
        setFieldError('password', 'Password minimal 5 karakter');
        if (!firstError) firstError = pass;
    }

    if (!confirm.value.trim()) {
        setFieldError('password_confirmation', 'Konfirmasi password wajib diisi');
        if (!firstError) firstError = confirm;
    } else if (pass.value !== confirm.value) {
        setFieldError('password_confirmation', 'Konfirmasi password tidak cocok');
        if (!firstError) firstError = confirm;
    }

    if (firstError) {
        e.preventDefault();
        firstError.focus();
        showAlert('Mohon periksa kembali data yang Anda masukkan', 'error');
    }
});

// ── Remove error on input ──
const fieldMap = {
    'name': 'name',
    'username': 'username',
    'email': 'email',
    'nama_toko': 'nama_toko',
    'no_telp': 'no_telp',
    'alamat': 'alamat',
    'password': 'password',
    'password_confirmation': 'password_confirmation'
};

document.querySelectorAll('.input-group input, .input-group textarea').forEach(el => {
    el.addEventListener('input', function() {
        const key = fieldMap[this.id];
        if (key) clearFieldError(key);
    });
});

// ── Laravel Server-Side Errors ──
(function() {
    const serverErrors = {};
    @if($errors->any())
        @foreach($errors->getMessages() as $field => $messages)
            serverErrors['{{ $field }}'] = '{{ $messages[0] }}';
        @endforeach
    @endif

    Object.keys(serverErrors).forEach(field => {
        setFieldError(field, serverErrors[field]);
    });

    if (Object.keys(serverErrors).length > 0) {
        showAlert('Mohon periksa kembali data yang Anda masukkan', 'error');
    }
})();

// ── Laravel Flash Messages ──
@if(session('success'))
    showAlert('{{ session("success") }}', 'success');
@endif
@if(session('error'))
    showAlert('{{ session("error") }}', 'error');
@endif
</script>
</body>
</html>