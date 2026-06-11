<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/auth/register.css'])
</head>
<body>

<div class="alert alert-error" id="alertBox">
    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-.75-11.25a.75.75 0 011.5 0v4.5a.75.75 0 01-1.5 0v-4.5zM10 14a1 1 0 100-2 1 1 0 000 2z"/></svg>
    <span id="alertMsg"></span>
</div>

<div class="container" id="mainContainer">

    <div class="left">
        <h2>Buat Akun</h2>
        <p class="subtitle">Isi data di bawah untuk memulai</p>

        <form id="registerForm" method="POST" action="{{ route('register') }}" novalidate>
            @csrf

            <div class="form-group">
                <div class="input-wrapper @error('name') error @enderror">
                    <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
                    <input type="text" id="name" name="name" class="validate-me" placeholder="Nama Lengkap" value="{{ old('name') }}">
                    <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </div>
                <span class="error-text" style="{{ $errors->has('name') ? 'display:block;' : '' }}">{{ $errors->first('name') ?? 'Nama lengkap wajib diisi!' }}</span>
            </div>

            <div class="form-group">
                <div class="input-wrapper @error('username') error @enderror">
                    <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                    <input type="text" id="username" name="username" class="validate-me" placeholder="Username" value="{{ old('username') }}">
                    <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </div>
                <span class="error-text" style="{{ $errors->has('username') ? 'display:block;' : '' }}">{{ $errors->first('username') ?? 'Username wajib diisi!' }}</span>
            </div>

            <div class="form-group">
                <div class="input-wrapper @error('email') error @enderror">
                    <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                    <input type="email" id="email" name="email" class="validate-me" placeholder="Email" value="{{ old('email') }}">
                    <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </div>
                <span class="error-text" style="{{ $errors->has('email') ? 'display:block;' : '' }}">{{ $errors->first('email') ?? 'Email wajib diisi!' }}</span>
            </div>

            <div class="form-group">
                <div class="input-wrapper @error('nama_toko') error @enderror">
                    <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path d="M3 7l1-4h12l1 4M4 7h12v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7z"/></svg>
                    <input type="text" id="nama_toko" name="nama_toko" class="validate-me" placeholder="Nama Toko" value="{{ old('nama_toko') }}">
                    <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </div>
                <span class="error-text" style="{{ $errors->has('nama_toko') ? 'display:block;' : '' }}">{{ $errors->first('nama_toko') ?? 'Nama toko wajib diisi!' }}</span>
            </div>

            <div class="form-group">
                <div class="input-wrapper @error('no_telp') error @enderror">
                    <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                    <input type="text" id="no_telp" name="no_telp" class="validate-me" placeholder="No Telepon (Opsional)" value="{{ old('no_telp') }}">
                    <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </div>
                <span class="error-text" style="{{ $errors->has('no_telp') ? 'display:block;' : '' }}">{{ $errors->first('no_telp') ?? 'No telepon sudah terdaftar!' }}</span>
            </div>

            <div class="form-group">
                <div class="input-wrapper">
                    <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                    <input type="text" id="alamat" name="alamat" placeholder="Alamat (Opsional)" value="{{ old('alamat') }}">
                </div>
            </div>

            <div class="row">
                <div class="form-group">
                    <div class="input-wrapper @error('password') error @enderror">
                        <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                        <input type="password" id="password" name="password" class="validate-me" placeholder="Password">
                        <span class="toggle" onclick="togglePass('password','eye1','eye2')" title="Tampilkan">
                            <svg id="eye1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eye2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </span>
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <span class="error-text" style="{{ $errors->has('password') ? 'display:block;' : '' }}">{{ $errors->first('password') ?? 'Password wajib diisi!' }}</span>
                </div>

                <div class="form-group">
                    <div class="input-wrapper @error('password_confirmation') error @enderror">
                        <svg class="icon-left" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="validate-me" placeholder="Konfirmasi Password">
                        <span class="toggle" onclick="togglePass('password_confirmation','eye3','eye4')" title="Tampilkan">
                            <svg id="eye3" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eye4" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </span>
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <span class="error-text" style="{{ $errors->has('password_confirmation') ? 'display:block;' : '' }}">{{ $errors->first('password_confirmation') ?? 'Konfirmasi password wajib diisi!' }}</span>
                </div>
            </div>

            <button type="submit" class="btn-primary">DAFTAR</button>
        </form>

        <div class="login-link">
            Sudah punya akun? <a href="{{ route('login') }}" onclick="goToPage(event, '{{ route('login') }}')">Login di sini</a>
        </div>
    </div>

    <div class="right">
        <div class="icon-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
        </div>
        <h2>Bergabunglah!</h2>
        <p>Buat akun Anda dan mulai kelola bengkel dengan lebih efisien dan terorganisir.</p>
    </div>
</div>

<script>
// Toggle Password View
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

// Alert System Atas Halaman
function showAlert(msg, type = 'error') {
    const box = document.getElementById('alertBox');
    const msgEl = document.getElementById('alertMsg');
    if(msgEl && box) {
        msgEl.textContent = msg;
        box.className = 'alert alert-' + type + ' show';
        clearTimeout(box._timer);
        box._timer = setTimeout(() => { box.classList.remove('show'); }, 3500);
    }
}

// Page Transition Animation
function goToPage(e, url) {
    e.preventDefault();
    const container = document.getElementById('mainContainer');
    container.classList.add('exit');
    setTimeout(() => { window.location.href = url; }, 500);
}

// Validasi Interaktif Sisi Klien saat Submit Form
document.getElementById('registerForm').addEventListener('submit', function(e) {
    let isValid = true;
    const inputs = this.querySelectorAll('.validate-me');
    
    inputs.forEach(input => {
        const formGroup = input.closest('.form-group');
        const wrapper = input.closest('.input-wrapper');
        const textMsg = formGroup.querySelector('.error-text');
        const val = input.value.trim();

        if(input.id === 'no_telp' && val === '') {
            if(wrapper) wrapper.classList.remove('error');
            if(textMsg) textMsg.style.display = 'none';
            return;
        }

        if(val === '') {
            isValid = false;
            if(wrapper) wrapper.classList.add('error');
            if(textMsg) {
                textMsg.textContent = input.placeholder.replace(' (Opsional)', '') + ' wajib diisi!';
                textMsg.style.display = 'block';
            }
        } 
        else if(input.id === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            isValid = false;
            if(wrapper) wrapper.classList.add('error');
            if(textMsg) {
                textMsg.textContent = 'Format email tidak valid!';
                textMsg.style.display = 'block';
            }
        } 
        else if(input.id === 'password' && val.length < 5) {
            isValid = false;
            if(wrapper) wrapper.classList.add('error');
            if(textMsg) {
                textMsg.textContent = 'Password minimal harus 5 karakter!';
                textMsg.style.display = 'block';
            }
        } 
        else if(input.id === 'password_confirmation') {
            const passVal = document.getElementById('password').value.trim();
            if(val !== passVal) {
                isValid = false;
                if(wrapper) wrapper.classList.add('error');
                if(textMsg) {
                    textMsg.textContent = 'Konfirmasi password tidak cocok!';
                    textMsg.style.display = 'block';
                }
            } else {
                if(wrapper) wrapper.classList.remove('error');
                if(textMsg) textMsg.style.display = 'none';
            }
        } 
        else {
            if(wrapper) wrapper.classList.remove('error');
            if(textMsg) textMsg.style.display = 'none';
        }
    });

    if(!isValid) { 
        e.preventDefault(); 
        showAlert('Mohon periksa kembali data pendaftaran Anda', 'error');
    }
});

// Bersihkan warna merah eror saat pengguna mulai mengetik kembali
document.querySelectorAll('.validate-me').forEach(input => {
    input.addEventListener('input', function() {
        const wrapper = this.closest('.input-wrapper');
        const formGroup = this.closest('.form-group');
        const textMsg = formGroup ? formGroup.querySelector('.error-text') : null;
        
        if(wrapper) wrapper.classList.remove('error');
        if(textMsg) textMsg.style.display = 'none';
    });
});

// Munculkan pesan peringatan di atas lewat Flash Session jika terdeteksi gagal duplikasi data
@if($errors->any())
    showAlert('Pendaftaran gagal. Beberapa data sudah terdaftar.', 'error');
@endif

// Laravel Flash Sessions
@if(session('success')) showAlert('{{ session("success") }}', 'success'); @endif
@if(session('error')) showAlert('{{ session("error") }}', 'error'); @endif
</script>
</body>
</html>