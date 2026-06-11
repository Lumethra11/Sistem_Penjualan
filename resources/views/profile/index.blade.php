@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="profile-page">

    {{-- TOAST ALERET --}}
    @if(session('success'))
        <div class="toast-alert">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- PROFILE CARD --}}
    <div class="profile-view-card">

        {{-- TOP PROFILE --}}
        <div class="profile-top">
            <div class="profile-avatar-big">
                @if(Auth::user()->foto_profil)
                    <img src="{{ asset('uploads/profile/' . Auth::user()->foto_profil) }}" alt="Profile">
                @else
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                @endif
            </div>

            <div class="profile-user-info">
                <h2>{{ Auth::user()->name }}</h2>
                <p>{{ Auth::user()->email }}</p>
                <span>
                    {{ Auth::user()->role === 'admin' ? 'Admin' : 'Kasir' }}
                </span>
            </div>
        </div>

        {{-- PROFILE INFO --}}
        <div class="profile-info-grid">

            <div class="info-box">
                <div class="info-label">
                    <i class="fa-regular fa-user"></i>
                    ID Username
                </div>
                <div class="info-value">
                    {{ Auth::user()->username ?? '-' }}
                </div>
            </div>

            <div class="info-box">
                <div class="info-label">
                    <i class="fa-solid fa-phone"></i>
                    No Telepon
                </div>
                <div class="info-value">
                    {{ Auth::user()->no_telp ?? '-' }}
                </div>
            </div>

            {{-- JIKA USER ADALAH ADMIN --}}
            @if(Auth::user()->role === 'admin')
                <div class="info-box">
                    <div class="info-label">
                        <i class="fa-solid fa-store"></i>
                        Nama Toko / Usaha
                    </div>
                    <div class="info-value">
                        {{ Auth::user()->nama_toko ?? '-' }}
                    </div>
                </div>

                {{-- FIXED: Alamat sekarang mutlak milik Admin/Toko --}}
                <div class="info-box">
                    <div class="info-label">
                        <i class="fa-solid fa-location-dot"></i>
                        Alamat Toko
                    </div>
                    <div class="info-value">
                        {{ Auth::user()->alamat ?? '-' }}
                    </div>
                </div>
            @endif

            {{-- JIKA USER ADALAH KASIR --}}
            @if(Auth::user()->role === 'kasir')
                <div class="info-box">
                    <div class="info-label">
                        <i class="fa-solid fa-store"></i>
                        Tempat Kerja
                    </div>
                    <div class="info-value">
                        {{ Auth::user()->admin->nama_toko ?? 'Belum ada toko' }}
                    </div>
                </div>

                <div class="info-box">
                    <div class="info-label">
                        <i class="fa-solid fa-user-tie"></i>
                        Nama Admin
                    </div>
                    <div class="info-value">
                        {{ Auth::user()->admin->name ?? '-' }}
                    </div>
                </div>

                {{-- FIXED: Data Alamat Toko Utama diambil secara dinamis lewat relasi Admin --}}
                <div class="info-box full-width">
                    <div class="info-label">
                        <i class="fa-solid fa-location-dot"></i>
                        Alamat Toko Utama
                    </div>
                    <div class="info-value">
                        {{ Auth::user()->admin->alamat ?? '-' }}
                    </div>
                </div>
            @endif

        </div>

        {{-- ACTION --}}
        <div class="profile-actions">
            <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                <i class="fa-solid fa-user-pen" style="margin-right: 8px;"></i>
                Edit Profile
            </a>
        </div>

    </div>

</div>
@endsection