<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Sistem Penjualan</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    <div class="app">
        @include('layouts.partials.navbar')

        @include('layouts.partials.sidebar')

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <main class="main">
            <div class="content-wrapper">
                @yield('content')
            </div>

            @include('layouts.partials.footer')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // 1. PENGATURAN USER DROPDOWN
            const userBtn = document.getElementById('userDropdownBtn');
            const dropdownMenu = document.getElementById('dropdownMenu');
            
            if (userBtn && dropdownMenu) {
                userBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });

                // Tutup dropdown otomatis jika klik di luar area menu
                document.addEventListener('click', function() {
                    dropdownMenu.classList.remove('show');
                });
            }

            // 2. PENGATURAN TOGGLE SIDEBAR MOBILE
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (menuToggle && sidebar && overlay) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.add('show');
                    overlay.classList.add('show');
                });

                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });
            }

            // 3. LIVE IMAGE PREVIEW (EDIT PROFILE)
            const fotoInput = document.getElementById('fotoInput');
            const previewImage = document.getElementById('previewImage');
            const previewText = document.getElementById('previewText');
            
            if (fotoInput && previewImage) {
                fotoInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImage.src = e.target.result;
                            previewImage.style.display = 'block';
                            
                            // Sembunyikan text inisial huruf jika ada
                            if (previewText) {
                                previewText.style.display = 'none';
                            }
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }

        });
    </script>
</body>
</html>