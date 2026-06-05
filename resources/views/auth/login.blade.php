<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif}

body{
    min-height:100vh;
    background: linear-gradient(135deg, #dbeafe, #eff6ff);
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
    width:100%;max-width:880px;
    display:flex;border-radius:20px;overflow:hidden;
    background:#fff;
    /* Shadow ditambah efek glow biru agar menyatu dengan background */
    box-shadow: 0 30px 60px rgba(0,0,0,.4), 0 0 80px rgba(37,99,235,0.15);
    animation:fadeUp .6s ease both;
}

/* ── LEFT (FORM) ── */
.left{
    flex:1;padding:48px 44px;
    display:flex;flex-direction:column;justify-content:center;
}
.left h2{
    font-size:26px;font-weight:700;color:#0f172a;margin-bottom:6px;
}
.left .subtitle{
    font-size:14px;color:#94a3b8;margin-bottom:30px;
}

/* ── INPUT ── */
.input-group{position:relative;margin-bottom:16px}
.input-group input{
    width:100%;padding:13px 44px 13px 42px;
    border-radius:10px;border:1.5px solid #e2e8f0;
    background:#ffffff;font-size:14px;color:#0f172a;
    outline:none;transition:.25s;
}
.input-group input::placeholder{color:#94a3b8}
.input-group input:focus{
    box-shadow:0 0 0 3px rgba(34,197,94,.15);
    border-color:#22c55e;
}
.input-group input.input-error{
    border-color:#ef4444;
    box-shadow:0 0 0 3px rgba(239,68,68,.1);
}
.icon-left{
    position:absolute;left:14px;top:50%;transform:translateY(-50%);
    width:18px;height:18px;color:#94a3b8;pointer-events:none;
}
.input-group input:focus~.icon-left{color:#3b82f6}

.toggle{
    position:absolute;right:14px;top:50%;transform:translateY(-50%);
    cursor:pointer;color:#94a3b8;transition:.2s;
    display:flex;align-items:center;
}
.toggle:hover{color:#64748b}

/* ── REMEMBER / FORGOT ── */
.form-options{
    display:flex;align-items:center;justify-content:space-between;
    margin-bottom:24px;font-size:13px;
}
.form-options label{
    display:flex;align-items:center;gap:7px;color:#64748b;cursor:pointer;
}
.form-options input[type=checkbox]{
    width:16px;height:16px;accent-color:#3b82f6;cursor:pointer;
}
.form-options a{color:#3b82f6;text-decoration:none;font-weight:500;transition:.2s}
.form-options a:hover{color:#1d4ed8}

/* ── BUTTON ── */
.btn-primary{
    width:100%;padding:13px;border:none;border-radius:10px;
    background: linear-gradient(135deg, #3b82f6, #22c55e);
    color:#fff;font-size:15px;font-weight:600;
    cursor:pointer;transition:.3s;
}
.btn-primary:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 24px rgba(37,99,235,.35);
}
.btn-primary:active{transform:translateY(0)}

/* ── REGISTER LINK ── */
.register-link{
    text-align:center;margin-top:20px;
    font-size:14px;color:#94a3b8;
}
.register-link a{
    color:#2563eb;font-weight:600;text-decoration:none;transition:.2s;
}
.register-link a:hover{color:#1d4ed8;text-decoration:underline}

/* ── RIGHT (WELCOME) ── */
.right{
    flex:1;
    background: linear-gradient(160deg, #3b82f6, #22c55e);
    color:#fff;
    display:flex;flex-direction:column;
    justify-content:center;align-items:flex-start;
    padding:48px 44px;
    position:relative;overflow:hidden;
}
.right::before{
    content:'';position:absolute;
    width:320px;height:320px;border-radius:50%;
    background:rgba(255,255,255,.06);
    top:-80px;right:-80px;
}
.right::after{
    content:'';position:absolute;
    width:200px;height:200px;border-radius:50%;
    background:rgba(255,255,255,.04);
    bottom:-60px;left:-40px;
}
.right h2{font-size:28px;font-weight:700;margin-bottom:10px;position:relative;z-index:1}
.right p{font-size:14px;line-height:1.7;opacity:.85;margin-bottom:28px;position:relative;z-index:1}
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

/* ── RESPONSIVE ── */
@media(max-width:860px){
    .container{max-width:440px;flex-direction:column}

    /* Welcome di ATAS */
    .right{order:0;padding:28px 32px;align-items:center;text-align:center}
    .right::before{width:180px;height:180px;top:-50px;right:-50px}
    .right::after{width:120px;height:120px;bottom:-30px;left:-20px}
    .right h2{font-size:20px;margin-bottom:6px}
    .right p{font-size:13px;margin-bottom:16px;line-height:1.6}
    .right .btn-outline{padding:9px 22px;font-size:13px}

    /* Form di BAWAH */
    .left{order:1;padding:28px 32px}
    .left h2{font-size:22px}
}

@media(max-width:440px){
    body{padding:12px}
    .container{border-radius:16px}
    .left{padding:24px 20px}
    .right{padding:24px 20px}
    .left h2{font-size:20px}
    .right h2{font-size:18px}
    .right p{font-size:12px}
    .form-options{flex-direction:column;gap:8px;align-items:flex-start}
}
</style>
</head>
<body>

<!-- ALERT -->
<div class="alert alert-error" id="alertBox">
    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-.75-11.25a.75.75 0 011.5 0v4.5a.75.75 0 01-1.5 0v-4.5zM10 14a1 1 0 100-2 1 1 0 000 2z"/></svg>
    <span id="alertMsg"></span>
</div>

<div class="container">

    <!-- WELCOME (di atas saat mobile karena order:0) -->
    <div class="right">
        <h2>Welcome Back!</h2>
        <p>Masuk untuk mengakses sistem dan kelola bisnis Anda dengan lebih efisien.</p>
    </div>

    <!-- FORM -->
    <div class="left">
        <h2>Login</h2>
        <p class="subtitle">Masuk ke akun Anda untuk melanjutkan</p>

        <form id="loginForm" method="POST" action="{{ route('login') }}">
            @csrf

            <div class="input-group">
                <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                <input type="text" id="username" name="username" placeholder="Email / Username" required>
            </div>

            <div class="input-group">
                <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                <input type="password" id="password" name="password" placeholder="Password" required>
                <span class="toggle" onclick="togglePassword()" id="toggleBtn" title="Tampilkan password">
                    <svg id="eyeOpen" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg id="eyeClose" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/><path d="M14.12 14.12a3 3 0 01-4.24-4.24"/></svg>
                </span>
            </div>

            <div class="form-options">
                <label><input type="checkbox" name="remember"> Ingat saya</label>
                <a href="#">Lupa password?</a>
            </div>

            <button type="submit" class="btn-primary">LOG IN</button>

            <div class="register-link">
                Belum punya akun? <a href="{{ route('register') }}">Daftar</a>
            </div>
        </form>
    </div>

</div>

<script>
function togglePassword(){
    const input=document.getElementById('password');
    const open=document.getElementById('eyeOpen');
    const close=document.getElementById('eyeClose');
    const btn=document.getElementById('toggleBtn');
    if(input.type==='password'){
        input.type='text';
        open.style.display='none';
        close.style.display='block';
        btn.title='Sembunyikan password';
    }else{
        input.type='password';
        open.style.display='block';
        close.style.display='none';
        btn.title='Tampilkan password';
    }
}

function showAlert(msg,type='error'){
    const box=document.getElementById('alertBox');
    const msgEl=document.getElementById('alertMsg');
    msgEl.textContent=msg;
    box.className='alert alert-'+type+' show';
    clearTimeout(box._timer);
    box._timer=setTimeout(()=>{box.classList.remove('show')},3500);
}

document.getElementById('loginForm').addEventListener('submit',function(e){
    const u=document.getElementById('username');
    const p=document.getElementById('password');
    u.classList.remove('input-error');
    p.classList.remove('input-error');
    if(!u.value.trim()){
        e.preventDefault();u.classList.add('input-error');u.focus();
        showAlert('Username / Email wajib diisi','error');return;
    }
    if(!p.value.trim()){
        e.preventDefault();p.classList.add('input-error');p.focus();
        showAlert('Password wajib diisi','error');return;
    }
    if(p.value.trim().length<6){
        e.preventDefault();p.classList.add('input-error');p.focus();
        showAlert('Password minimal 6 karakter','error');return;
    }
});

document.querySelectorAll('.input-group input').forEach(input=>{
    input.addEventListener('input',function(){this.classList.remove('input-error')});
});
</script>

</body>
</html>