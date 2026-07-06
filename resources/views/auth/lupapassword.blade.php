<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/auth/lupapassword.css'])
    <style>
        .toast-success-container {
            position: fixed; top: 24px; left: 50%; transform: translateX(-50%) translateY(-150%);
            background: #f0fdf4; border: 1.5px solid #bbf7d0; color: #15803d; padding: 14px 24px;
            border-radius: 12px; display: flex; align-items: center; gap: 10px; font-size: 14px;
            font-weight: 500; z-index: 99999; box-shadow: 0 10px 25px -5px rgba(34, 197, 94, 0.2);
            transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.15);
        }
        .toast-success-container.slide-down { transform: translateX(-50%) translateY(0); }
        .toast-success-container.slide-up { transform: translateX(-50%) translateY(-150%); }
        .toast-success-container svg { flex-shrink: 0; width: 20px; height: 20px; color: #22c55e; }
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
    <div class="left">
        <h2>Lupa Password?</h2>
        <p class="subtitle">Masukkan email terdaftar untuk menerima link reset password</p>

        <form id="forgotForm" method="POST" action="{{ route('password.email') }}">
            @csrf

            @if(session('error') || $errors->any())
                <div class="alert-bubble" id="errorBubble">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span>{{ session('error') ?? $errors->first() }}</span>
                </div>
            @endif

            <div class="form-group">
                <div class="input-wrapper" id="wrap-email">
                    <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                    <input type="email" id="email" name="email" class="validate-me" placeholder="Masukkan Email Anda" value="{{ old('email') }}" required>
                    <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </div>
            </div>

            <button type="submit" class="btn-primary">KIRIM LINK RESET</button>

            <div class="register-link">
                Kembali ke <a href="{{ route('login') }}">Login</a>
            </div>
        </form>
    </div>

    <div class="right">
        <h2>Keamanan Akun</h2>
        <p>Sistem mengamankan kredensial Anda. Cukup masukkan email, periksa kotak masuk, dan lakukan reset sandi secara aman.</p>
    </div>
</div>

<script>
    document.getElementById('forgotForm').addEventListener('submit', function(e) {
        let isValid = true;
        const input = document.getElementById('email');
        const wrapper = input.closest('.input-wrapper');
        if(input.value.trim() === '') {
            isValid = false;
            wrapper.classList.add('error');
        } else {
            wrapper.classList.remove('error');
        }
        if(!isValid) e.preventDefault();
    });

    document.getElementById('email').addEventListener('input', function() {
        this.closest('.input-wrapper').classList.remove('error');
        const errBubble = document.getElementById('errorBubble');
        if(errBubble) errBubble.style.display = 'none';
    });

    document.addEventListener("DOMContentLoaded", function() {
        const toast = document.getElementById('toastSuccess');
        if (toast) {
            setTimeout(() => { toast.classList.add('slide-down'); }, 150);
            setTimeout(() => {
                toast.classList.remove('slide-down');
                toast.classList.add('slide-up');
            }, 3500);
        }
    });

    (function() {
        const wrapEmail = document.getElementById('wrap-email');
        @if(session('error') || $errors->any())
            if(wrapEmail) wrapEmail.classList.add('error');
        @endif
    })();
</script>
</body>
</html>