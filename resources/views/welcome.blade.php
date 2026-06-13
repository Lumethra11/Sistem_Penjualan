<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Penjualan</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    @vite('resources/css/welcome.css')
</head>
<body>

{{-- =========================
    NAVBAR
========================= --}}
<nav class="navbar">

    <div class="logo">
        <i class="fa-solid fa-store"></i>
        <span>Sistem Penjualan</span>
    </div>

    <div class="nav-links">
        <a href="#tentang">Tentang</a>
        <a href="#fitur">Fitur</a>
        <a href="#cara-kerja">Cara Kerja</a>
        <a href="#keunggulan">Keunggulan</a>
    </div>

    <div class="nav-action">
        <a href="{{ route('login') }}" class="btn-secondary">
            Login
        </a>

        <a href="{{ route('register') }}" class="btn-primary">
            Register
        </a>
    </div>

</nav>

{{-- =========================
    HERO
========================= --}}
<section class="hero">

    <div class="hero-content">

        <span class="badge">
            Sistem Penjualan Berbasis Web
        </span>

        <h1>
            Kelola Penjualan dan Stok Barang
            Dalam Satu Sistem Terintegrasi
        </h1>

        <p>
            Membantu pencatatan transaksi, pengelolaan stok,
            laporan penjualan, serta pemantauan aktivitas usaha
            secara lebih mudah, cepat, dan terstruktur.
        </p>

        <div class="hero-buttons">

            <a href="{{ route('register') }}"
               class="btn-primary">
                Mulai Sekarang
            </a>

            <a href="#fitur"
               class="btn-secondary">
                Lihat Fitur
            </a>

        </div>

    </div>

    <div class="hero-illustration">

        <div class="hero-card">
            <i class="fa-solid fa-box"></i>
            <span>Manajemen Barang</span>
        </div>

        <div class="hero-card">
            <i class="fa-solid fa-cart-shopping"></i>
            <span>Transaksi Kasir</span>
        </div>

        <div class="hero-card">
            <i class="fa-solid fa-chart-column"></i>
            <span>Laporan Penjualan</span>
        </div>

        <div class="hero-card">
            <i class="fa-solid fa-bell"></i>
            <span>Notifikasi</span>
        </div>

    </div>

</section>

{{-- =========================
    TENTANG
========================= --}}
<section class="about" id="tentang">

    <h2>Mengapa Menggunakan Sistem Ini?</h2>

    <div class="about-grid">

        <div class="about-card">
            <i class="fa-solid fa-file-lines"></i>
            <h3>Pencatatan Digital</h3>
            <p>
                Mengurangi risiko kehilangan data
                dan kesalahan pencatatan manual.
            </p>
        </div>

        <div class="about-card">
            <i class="fa-solid fa-boxes-stacked"></i>
            <h3>Stok Lebih Terkontrol</h3>
            <p>
                Memudahkan pemantauan ketersediaan barang
                secara lebih terstruktur.
            </p>
        </div>

        <div class="about-card">
            <i class="fa-solid fa-clock"></i>
            <h3>Lebih Efisien</h3>
            <p>
                Mempercepat proses transaksi
                dan pengelolaan data usaha.
            </p>
        </div>

    </div>

</section>

{{-- =========================
    FITUR
========================= --}}
<section class="features" id="fitur">

    <h2>Fitur Utama</h2>

    <div class="feature-grid">

        <div class="feature-card">
            <i class="fa-solid fa-chart-pie"></i>
            <h3>Dashboard</h3>
            <p>
                Menampilkan ringkasan aktivitas sistem
                secara cepat dan informatif.
            </p>
        </div>

        <div class="feature-card">
            <i class="fa-solid fa-box"></i>
            <h3>Manajemen Barang</h3>
            <p>
                Mengelola data barang dan stok barang
                secara terorganisir.
            </p>
        </div>

        <div class="feature-card">
            <i class="fa-solid fa-cart-shopping"></i>
            <h3>Kasir</h3>
            <p>
                Mendukung proses transaksi
                yang cepat dan mudah digunakan.
            </p>
        </div>

        <div class="feature-card">
            <i class="fa-solid fa-users"></i>
            <h3>Kelola User</h3>
            <p>
                Mengatur pengguna yang dapat
                mengakses sistem.
            </p>
        </div>

        <div class="feature-card">
            <i class="fa-solid fa-file-invoice"></i>
            <h3>Laporan</h3>
            <p>
                Menyediakan rekap data yang
                mudah dipantau dan dievaluasi.
            </p>
        </div>

        <div class="feature-card">
            <i class="fa-solid fa-bell"></i>
            <h3>Notifikasi</h3>
            <p>
                Memberikan informasi penting
                secara langsung kepada pengguna.
            </p>
        </div>

    </div>

</section>

{{-- =========================
    CARA KERJA
========================= --}}
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

{{-- =========================
    KEUNGGULAN
========================= --}}
<section class="advantages" id="keunggulan">

    <h2>Keunggulan Sistem</h2>

    <div class="advantage-grid">

        <div class="advantage-card">
            <h3>Mudah Digunakan</h3>
            <p>
                Antarmuka sederhana dan mudah dipahami.
            </p>
        </div>

        <div class="advantage-card">
            <h3>Berbasis Web</h3>
            <p>
                Dapat diakses dari berbagai perangkat.
            </p>
        </div>

        <div class="advantage-card">
            <h3>Terintegrasi</h3>
            <p>
                Seluruh data tersimpan dalam satu sistem.
            </p>
        </div>

        <div class="advantage-card">
            <h3>Responsif</h3>
            <p>
                Nyaman digunakan pada desktop maupun mobile.
            </p>
        </div>

    </div>

</section>

{{-- =========================
    CTA
========================= --}}
<section class="cta">

    <h2>
        Mulai Kelola Penjualan dan Stok Barang
        Dengan Lebih Mudah
    </h2>

    <a href="{{ route('register') }}"
       class="btn-primary">
        Daftar Sekarang
    </a>

</section>

{{-- =========================
    FOOTER
========================= --}}
<footer>

    <h3>Sistem Penjualan</h3>

    <p>
        Solusi digital untuk pengelolaan transaksi
        dan stok barang yang lebih efektif.
    </p>

    <span>
        © {{ date('Y') }} Sistem Penjualan
    </span>

</footer>

</body>
</html>