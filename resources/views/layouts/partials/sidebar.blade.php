<aside class="sidebar" id="sidebar">
    <ul class="sidebar-menu">

        {{-- Admin --}}
        @if(Auth::user()->role === 'admin')

            <li>
                <a href="{{ route('dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-table-cells-large"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li>
                <a href="{{ route('manajemenbarang.databarang') }}"
                   class="sidebar-link {{ request()->routeIs('manajemenbarang.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-cubes"></i>
                    <span>Manajemen Barang</span>
                </a>
            </li>

            <li>
                <a href="{{ route('kelolauser.daftar') }}"
                   class="sidebar-link {{ request()->routeIs('kelolauser.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i>
                    <span>Kelola Kasir</span>
                </a>
            </li>

            <li>
                <a href="{{ route('laporan.index') }}"
                class="sidebar-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                    <i class="fa-regular fa-file-lines"></i>
                    <span>Laporan</span>
                </a>
            </li>

        @endif


        {{-- KASIR --}}
        @if(Auth::user()->role === 'kasir')

            <li>
                <a href="{{ route('kasir.transaksi') }}"
                   class="sidebar-link {{ request()->routeIs('kasir.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>Kasir</span>
                </a>
            </li>

        @endif

    </ul>
</aside>