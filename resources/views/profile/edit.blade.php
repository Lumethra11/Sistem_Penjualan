@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="profile-page">

    {{-- TOAST --}}
    @if(session('success'))
        <div class="toast-alert">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- EDIT PROFILE CARD --}}
    <div class="profile-edit-card">

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- FOTO PROFILE --}}
            <div class="avatar-upload-wrapper">
                <label class="avatar-upload" for="fotoInput">
                    @if(Auth::user()->foto_profil)
                        <img id="previewImage" src="{{ asset('uploads/profile/' . Auth::user()->foto_profil) }}" alt="Preview">
                        <div id="previewText" style="display:none;"></div>
                    @else
                        <div id="previewText">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <img id="previewImage" style="display:none;" alt="Preview">
                    @endif

                    <div class="avatar-edit-overlay">
                        <i class="fa-solid fa-camera"></i>
                        <span>Ubah Foto</span>
                    </div>
                </label>

                <input type="file" id="fotoInput" name="foto_profil" hidden accept="image/*">
            </div>

            {{-- FORM GRID --}}
            <div class="profile-grid">

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" required>
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="{{ old('username', Auth::user()->username) }}" required>
                </div>

                <div class="form-group">
                    <label>Email Sistem</label>
                    <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" required>
                </div>

                <div class="form-group">
                    <label>No Telepon</label>
                    <input type="text" name="no_telp" value="{{ old('no_telp', Auth::user()->no_telp) }}">
                </div>

                {{-- KONDISI ADMIN --}}
                @if(Auth::user()->role === 'admin')
                    <div class="form-group full-width">
                        <label>Nama Toko</label>
                        <input type="text" name="nama_toko" value="{{ old('nama_toko', Auth::user()->nama_toko) }}">
                    </div>
                @endif

                {{-- KONDISI KASIR --}}
                @if(Auth::user()->role === 'kasir')
                    <div class="form-group full-width">
                        <label>Alamat Lengkap</label>
                        <textarea name="alamat">{{ old('alamat', Auth::user()->alamat) }}</textarea>
                    </div>
                @endif

                {{-- PEMBATAS: KEAMANAN AKUN (GANTI PASSWORD) --}}
                <div class="section-divider full-width">
                    <h3>Keamanan Akun</h3>
                    <p>Kosongkan kolom di bawah ini jika Anda tidak ingin mengubah password.</p>
                </div>

                <div class="form-group">
                    <label>Password Baru</label>
                    <input type="password" name="password" placeholder="Minimal 8 karakter">
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" placeholder="Ulangi password baru">
                </div>

            </div>

            {{-- ACTION --}}
            <div class="profile-actions">
                <a href="{{ route('profile') }}" class="btn btn-light">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
                </button>
            </div>

        </form>

    </div>

</div>

<script>
    // Preview gambar sebelum diupload
    document.getElementById('fotoInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImage').src = e.target.result;
                document.getElementById('previewImage').style.display = 'block';
                const textPreview = document.getElementById('previewText');
                if(textPreview) textPreview.style.display = 'none';
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection