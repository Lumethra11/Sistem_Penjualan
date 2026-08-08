<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Penjualan - Kelola Stok & Transaksi Modern</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    @vite('resources/css/welcome.css')
</head>
<body>

{{-- NAVBAR UTAMA --}}
<nav class="navbar">
    <div class="logo">
        <i class="fa-solid fa-store"></i>
        <span>Sistem Penjualan</span>
    </div>

    {{-- Link Navigasi Desktop --}}
    <div class="nav-links">
        <a href="#tentang"><i class="fa-regular fa-circle-question"></i> Tentang</a>
        <a href="#fitur"><i class="fa-solid fa-bolt"></i> Fitur</a>
        <a href="#cara-kerja"><i class="fa-solid fa-route"></i> Cara Kerja</a>
        <a href="#keunggulan"><i class="fa-solid fa-award"></i> Keunggulan</a>
    </div>

    {{-- Tombol Login & Register Desktop --}}
    <div class="nav-action-desktop">
        <a href="{{ route('login') }}" class="btn-secondary">
            <i class="fa-solid fa-right-to-bracket"></i> Login
        </a>
        <a href="{{ route('register') }}" class="btn-primary">
            <i class="fa-solid fa-user-plus"></i> Register
        </a>
    </div>

    {{-- Burger Toggle Mobile --}}
    <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle Menu">
        <i class="fa-solid fa-bars-staggered"></i>
    </button>

    {{-- Pop-Up Card Mobile Menu (Elegan & Berisi) --}}
    <div class="nav-popup-mobile" id="navPopup">
        <div class="popup-header">
            <i class="fa-solid fa-shield-halved"></i>
            <div>
                <strong>Akses Akun</strong>
                <p>Masuk atau buat akun baru</p>
            </div>
        </div>
        <div class="popup-actions">
            <a href="{{ route('login') }}" class="btn-popup-secondary">
                <i class="fa-solid fa-right-to-bracket"></i> Login
            </a>
            <a href="{{ route('register') }}" class="btn-popup-primary">
                <i class="fa-solid fa-user-plus"></i> Register Gratis
            </a>
        </div>
    </div>
</nav>

{{-- SUB-NAVBAR MOBILE (Shortcut Scroll Horizontal) --}}
<div class="mobile-subnav">
    <div class="subnav-container">
        <a href="#tentang" class="subnav-item"><i class="fa-regular fa-circle-question"></i> Tentang</a>
        <a href="#fitur" class="subnav-item"><i class="fa-solid fa-bolt"></i> Fitur</a>
        <a href="#cara-kerja" class="subnav-item"><i class="fa-solid fa-route"></i> Cara Kerja</a>
        <a href="#keunggulan" class="subnav-item"><i class="fa-solid fa-award"></i> Keunggulan</a>
    </div>
</div>

{{-- HERO SECTION --}}
<section class="hero">
    <div class="hero-content">
        <span class="badge">
            <i class="fa-solid fa-sparkles"></i> Sistem Penjualan Berbasis Web
        </span>
        <h1>Kelola Penjualan & Stok Barang Dalam Satu Sistem Terintegrasi</h1>
        <p>Membantu pencatatan transaksi, pengelolaan stok, laporan penjualan, serta pemantauan aktivitas usaha secara lebih mudah, cepat, dan terstruktur.</p>
        <div class="hero-buttons">
            <a href="{{ route('register') }}" class="btn-primary btn-hero">
                Mulai Sekarang <i class="fa-solid fa-arrow-right"></i>
            </a>
            <a href="#fitur" class="btn-secondary btn-hero">
                Lihat Fitur <i class="fa-solid fa-eye"></i>
            </a>
        </div>
    </div>

    <div class="hero-illustration">
        <div class="hero-card">
            <div class="card-icon"><i class="fa-solid fa-box"></i></div>
            <span>Manajemen Barang</span>
        </div>
        <div class="hero-card">
            <div class="card-icon"><i class="fa-solid fa-cart-shopping"></i></div>
            <span>Transaksi Kasir</span>
        </div>
        <div class="hero-card">
            <div class="card-icon"><i class="fa-solid fa-chart-column"></i></div>
            <span>Laporan Penjualan</span>
        </div>
        <div class="hero-card">
            <div class="card-icon"><i class="fa-solid fa-bell"></i></div>
            <span>Notifikasi</span>
        </div>
    </div>
</section>

{{-- TENTANG SECTION --}}
<section class="about" id="tentang">
    <h2>Mengapa Menggunakan Sistem Ini?</h2>
    <div class="about-grid">
        <div class="about-card">
            <i class="fa-solid fa-file-lines"></i>
            <h3>Pencatatan Digital</h3>
            <p>Mengurangi risiko kehilangan data dan kesalahan pencatatan manual.</p>
        </div>
        <div class="about-card">
            <i class="fa-solid fa-boxes-stacked"></i>
            <h3>Stok Lebih Terkontrol</h3>
            <p>Memudahkan pemantauan ketersediaan barang secara lebih terstruktur.</p>
        </div>
        <div class="about-card">
            <i class="fa-solid fa-clock"></i>
            <h3>Lebih Efisien</h3>
            <p>Mempercepat proses transaksi dan pengelolaan data usaha.</p>
        </div>
    </div>
</section>

{{-- FITUR SECTION --}}
<section class="features" id="fitur">
    <h2>Fitur Utama</h2>
    <div class="feature-grid">
        <div class="feature-card">
            <i class="fa-solid fa-chart-pie"></i>
            <h3>Dashboard</h3>
            <p>Menampilkan ringkasan aktivitas sistem secara cepat dan informatif.</p>
        </div>
        <div class="feature-card">
            <i class="fa-solid fa-box"></i>
            <h3>Manajemen Barang</h3>
            <p>Mengelola data barang dan stok barang secara terorganisir.</p>
        </div>
        <div class="feature-card">
            <i class="fa-solid fa-cart-shopping"></i>
            <h3>Kasir</h3>
            <p>Mendukung proses transaksi yang cepat dan mudah digunakan.</p>
        </div>
        <div class="feature-card">
            <i class="fa-solid fa-users"></i>
            <h3>Kelola User</h3>
            <p>Mengatur pengguna yang dapat mengakses sistem.</p>
        </div>
        <div class="feature-card">
            <i class="fa-solid fa-file-invoice"></i>
            <h3>Laporan</h3>
            <p>Menyediakan rekap data yang mudah dipantau dan dievaluasi.</p>
        </div>
        <div class="feature-card">
            <i class="fa-solid fa-bell"></i>
            <h3>Notifikasi</h3>
            <p>Memberikan informasi penting secara langsung kepada pengguna.</p>
        </div>
    </div>
</section>

{{-- CARA KERJA SECTION --}}
<section class="workflow" id="cara-kerja">
    <h2>Cara Kerja Sistem</h2>
    <div class="steps">
        <div class="step">
            <span>1</span>
            <h3>Input Data Barang</h3>
        </div>
        <div class="step">
            <span>2</span>
            <h3>Lakukan Transaksi</h3>
        </div>
        <div class="step">
            <span>3</span>
            <h3>Data Tersimpan Otomatis</h3>
        </div>
        <div class="step">
            <span>4</span>
            <h3>Pantau Laporan</h3>
        </div>
    </div>
</section>

{{-- KEUNGGULAN SECTION --}}
<section class="advantages" id="keunggulan">
    <h2>Keunggulan Sistem</h2>
    <div class="advantage-grid">
        <div class="advantage-card">
            <h3>Mudah Digunakan</h3>
            <p>Antarmuka sederhana dan mudah dipahami.</p>
        </div>
        <div class="advantage-card">
            <h3>Berbasis Web</h3>
            <p>Dapat diakses dari berbagai perangkat.</p>
        </div>
        <div class="advantage-card">
            <h3>Terintegrasi</h3>
            <p>Seluruh data tersimpan dalam satu sistem.</p>
        </div>
        <div class="advantage-card">
            <h3>Responsif</h3>
            <p>Nyaman digunakan pada desktop maupun mobile.</p>
        </div>
    </div>
</section>

{{-- CALL TO ACTION --}}
<section class="cta">
    <h2>Mulai Kelola Penjualan dan Stok Barang Dengan Lebih Mudah</h2>
    <a href="{{ route('register') }}" class="btn-primary btn-hero">Daftar Sekarang <i class="fa-solid fa-arrow-right"></i></a>
</section>

{{-- FOOTER --}}
<footer>
    <h3>Sistem Penjualan</h3>
    <p>Solusi digital untuk pengelolaan transaksi dan stok barang yang lebih efektif.</p>
    <span>© {{ date('Y') }} Sistem Penjualan. All Rights Reserved.</span>
</footer>

<script>
    const toggle = document.getElementById('mobileToggle');
    const navPopup = document.getElementById('navPopup');

    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        navPopup.classList.toggle('active');
        const icon = toggle.querySelector('i');
        if(navPopup.classList.contains('active')) {
            icon.classList.remove('fa-bars-staggered');
            icon.classList.add('fa-xmark');
        } else {
            icon.classList.remove('fa-xmark');
            icon.classList.add('fa-bars-staggered');
        }
    });

    document.addEventListener('click', (e) => {
        if (!navPopup.contains(e.target) && !toggle.contains(e.target)) {
            navPopup.classList.remove('active');
            const icon = toggle.querySelector('i');
            icon.classList.remove('fa-xmark');
            icon.classList.add('fa-bars-staggered');
        }
    });
</script>

</body>
</html>