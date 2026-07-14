@extends('layouts.app')

@section('title', 'Tambah Kasir')

@vite('resources/css/pages/kelolauser.css')

@section('content')

<div class="kelola-user-container">

    <h1>Kelola Kasir</h1>

    <div class="tab-container">
        <a href="{{ route('kelolauser.tambah') }}" class="tab-button active">Tambah Kasir</a>
        <a href="{{ route('kelolauser.daftar') }}" class="tab-button">Daftar Kasir</a>
    </div>

    <div class="form-container">
        <form action="{{ route('kelolauser.store') }}" method="POST">
            @csrf

            <div class="form-row">
                <!-- Nama Lengkap -->
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <div class="input-wrapper @error('name') input-error @enderror">
                        <input type="text" name="name" value="{{ old('name') }}" required>
                    </div>
                    @error('name')
                        <span class="error-message" style="color: #dc3545; font-size: 0.85rem; margin-top: 5px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Username -->
                <div class="form-group">
                    <label>Username</label>
                    <div class="input-wrapper @error('username') input-error @enderror">
                        <input type="text" name="username" value="{{ old('username') }}" required>
                    </div>
                    @error('username')
                        <span class="error-message" style="color: #dc3545; font-size: 0.85rem; margin-top: 5px; display: block;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <!-- Email -->
                <div class="form-group">
                    <label>Email</label>
                    <div class="input-wrapper @error('email') input-error @enderror">
                        <input type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    @error('email')
                        <span class="error-message" style="color: #dc3545; font-size: 0.85rem; margin-top: 5px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- No Telepon -->
                <div class="form-group">
                    <label>No Telepon</label>
                    <div class="input-wrapper @error('no_telp') input-error @enderror">
                        <input type="text" name="no_telp" value="{{ old('no_telp') }}">
                    </div>
                    @error('no_telp')
                        <span class="error-message" style="color: #dc3545; font-size: 0.85rem; margin-top: 5px; display: block;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <!-- Password -->
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrapper @error('password') input-error @enderror">
                        <input type="password" name="password" required>
                    </div>
                    @error('password')
                        <span class="error-message" style="color: #dc3545; font-size: 0.85rem; margin-top: 5px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Konfirmasi Password -->
                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="password_confirmation" required>
                    </div>
                </div>
            </div>

            {{-- Hak Akses Box --}}
            <div class="permission-box" style="margin-top: 20px;">
                <label class="checkbox-wrapper">
                    <input type="checkbox" name="can_input_manual_barang" value="1" {{ old('can_input_manual_barang') ? 'checked' : '' }}>
                    <div class="custom-checkbox"></div>
                    <div class="checkbox-text">
                        <span>Izinkan Kasir Menginput Barang Manual</span>
                        <small>Kasir dapat menambahkan barang manual saat transaksi apabila barang belum tersedia pada data master.</small>
                    </div>
                </label>
            </div>

            <button type="submit" class="submit-btn" style="margin-top: 20px;">
                Tambah Kasir
            </button>
        </form>
    </div>
</div>

@endsection