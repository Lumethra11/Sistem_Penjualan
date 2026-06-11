<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/auth/login.css'])

    <style>
        .toast-success-container {
            position: fixed;
            top: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(-150%);
            background: #f0fdf4;
            border: 1.5px solid #bbf7d0;
            color: #15803d;
            padding: 14px 24px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
            z-index: 99999;
            box-shadow: 0 10px 25px -5px rgba(34, 197, 94, 0.2);
            transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.15);
        }
        
        /* Class saat toast muncul meluncur ke bawah */
        .toast-success-container.slide-down {
            transform: translateX(-50%) translateY(0);
        }

        /* Class saat toast kembali ditarik ke atas luar layar */
        .toast-success-container.slide-up {
            transform: translateX(-50%) translateY(-150%);
        }

        .toast-success-container svg {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
            color: #22c55e;
        }
    </style>
</head>
<body>

@if(session('success'))
    <div class="toast-success-container" id="toastSuccess">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

<div class="container">

    <div class="right">
        <h2>Welcome Back!</h2>
        <p>Masuk untuk mengakses sistem dan kelola bisnis Anda dengan lebih efisien.</p>
    </div>

    <div class="left">
        <h2>Login</h2>
        <p class="subtitle">Masuk ke akun Anda untuk melanjutkan</p>

        <form id="loginForm" method="POST" action="{{ route('login') }}">
            @csrf

            @if(session('error'))
                <div class="alert-bubble" id="errorBubble">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div class="form-group">
                <div class="input-wrapper" id="wrap-username">
                    <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                    <input type="text" id="username" name="username" class="validate-me" placeholder="Email / Username" value="{{ old('username') }}" required>
                    
                    <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </div>
            </div>

            <div class="form-group">
                <div class="input-wrapper" id="wrap-password">
                    <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                    <input type="password" id="password" name="password" class="validate-me" placeholder="Password" required>
                    
                    <span class="toggle" onclick="togglePassword()" id="toggleBtn" title="Tampilkan password">
                        <svg id="eyeOpen" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg id="eyeClose" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/><path d="M14.12 14.12a3 3 0 01-4.24-4.24"/></svg>
                    </span>

                    <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </div>
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
// Tampilkan/Sembunyikan Sandi
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

// Validasi Kosong Sisi Klien saat Submit Form
document.getElementById('loginForm').addEventListener('submit', function(e) {
    let isValid = true;
    const inputs = this.querySelectorAll('.validate-me');
    
    inputs.forEach(input => {
        const wrapper = input.closest('.input-wrapper');
        if(input.value.trim() === '') {
            isValid = false;
            wrapper.classList.add('error');
        } else {
            wrapper.classList.remove('error');
        }
    });

    if(!isValid) { 
        e.preventDefault(); 
    }
});

// Bersihkan warna merah pinggiran kolom dan bubble eror saat user mulai mengetik kembali
document.querySelectorAll('.validate-me').forEach(input => {
    input.addEventListener('input', function() {
        this.closest('.input-wrapper').classList.remove('error');
        
        const errBubble = document.getElementById('errorBubble');
        if(errBubble) errBubble.style.display = 'none';
    });
});

// ── LOGIKA JAVASCRIPT ANIMASI TOAST MELUNCUR (ATAS - BAWAH - ATAS) ──
document.addEventListener("DOMContentLoaded", function() {
    const toast = document.getElementById('toastSuccess');
    if (toast) {
        // 1. Munculkan: Beri jeda sedikit lalu luncurkan ke bawah layar
        setTimeout(() => {
            toast.classList.add('slide-down');
        }, 150);

        // 2. Sembunyikan otomatis: Setelah 3.5 detik, tarik kembali ke atas layar
        setTimeout(() => {
            toast.classList.remove('slide-down');
            toast.classList.add('slide-up');
        }, 3500);
    }
});

// ── TRIGGER PINGGIRAN FIELD MEMERAH JIKA SERVER MENYATAKAN EROR ──
(function() {
    const wrapUser = document.getElementById('wrap-username');
    const wrapPass = document.getElementById('wrap-password');

    @if(session('error') || $errors->any())
        if(wrapUser) wrapUser.classList.add('error');
        if(wrapPass) wrapPass.classList.add('error');
    @endif
})();
</script>

</body>
</html>