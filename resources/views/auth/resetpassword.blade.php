<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/auth/lupapassword.css'])
</head>
<body>

<div class="container">
    <div class="left">
        <h2>Sandi Baru</h2>
        <p class="subtitle">Silakan setel ulang password baru akun Anda</p>

        <form id="resetForm" method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            @if($errors->any())
                <div class="alert-bubble" id="errorBubble">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <div class="form-group">
                <div class="input-wrapper" id="wrap-password">
                    <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                    <input type="password" id="password" name="password" class="validate-me" placeholder="Password Baru" required>
                    <span class="toggle" onclick="togglePassword('password', 'eyeOpen1', 'eyeClose1', this)" title="Tampilkan password">
                        <svg id="eyeOpen1" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg id="eyeClose1" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/><path d="M14.12 14.12a3 3 0 01-4.24-4.24"/></svg>
                    </span>
                    <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </div>
            </div>

            <div class="form-group">
                <div class="input-wrapper" id="wrap-password-confirm">
                    <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="validate-me" placeholder="Ulangi Password" required>
                    <span class="toggle" onclick="togglePassword('password_confirmation', 'eyeOpen2', 'eyeClose2', this)" title="Tampilkan password">
                        <svg id="eyeOpen2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg id="eyeClose2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/><path d="M14.12 14.12a3 3 0 01-4.24-4.24"/></svg>
                    </span>
                    <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </div>
            </div>

            <button type="submit" class="btn-primary">PERBARUI PASSWORD</button>
        </form>
    </div>

    <div class="right">
        <h2>Langkah Terakhir</h2>
        <p>Gunakan kombinasi password yang kuat dan aman dengan minimal 5 karakter demi melindungi data penjualan ritel Anda.</p>
    </div>
</div>

<script>
    function togglePassword(inputId, openId, closeId, btn){
        const input = document.getElementById(inputId);
        const open = document.getElementById(openId);
        const close = document.getElementById(closeId);
        if(input.type === 'password'){
            input.type = 'text'; open.style.display = 'none'; close.style.display = 'block';
            btn.title = 'Sembunyikan password';
        } else {
            input.type = 'password'; open.style.display = 'block'; close.style.display = 'none';
            btn.title = 'Tampilkan password';
        }
    }

    document.getElementById('resetForm').addEventListener('submit', function(e) {
        let isValid = true;
        this.querySelectorAll('.validate-me').forEach(input => {
            const wrapper = input.closest('.input-wrapper');
            if(input.value.trim() === '') {
                isValid = false; wrapper.classList.add('error');
            } else { wrapper.classList.remove('error'); }
        });
        if(!isValid) e.preventDefault();
    });

    document.querySelectorAll('.validate-me').forEach(input => {
        input.addEventListener('input', function() {
            this.closest('.input-wrapper').classList.remove('error');
            const errBubble = document.getElementById('errorBubble');
            if(errBubble) errBubble.style.display = 'none';
        });
    });

    (function() {
        @if($errors->any())
            document.getElementById('wrap-password').classList.add('error');
            document.getElementById('wrap-password-confirm').classList.add('error');
        @endif
    })();
</script>
</body>
</html>