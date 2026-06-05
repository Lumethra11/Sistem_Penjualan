<nav class="navbar">

    {{-- LEFT --}}
    <div class="navbar-left">

        <button class="menu-toggle" id="menuToggle">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="logo">
            <div class="logo-box">SP</div>
            <div class="logo-text">Sistem Penjualan</div>
        </div>

    </div>


    {{-- RIGHT --}}
    <div class="navbar-right">

        {{-- NOTIF HANYA ADMIN --}}
        @if(Auth::user()->role === 'admin')
            <button class="nav-icon">
                <i class="fa-regular fa-bell"></i>
            </button>
        @endif


        <div class="user-dropdown">

            <button class="user-trigger" id="userDropdownBtn">

                <div class="user-avatar">
                    @if(Auth::user()->foto_profil)
                        <img src="{{ asset('uploads/profile/' . Auth::user()->foto_profil) }}" alt="Profile">
                    @else
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    @endif
                </div>

            </button>


            {{-- DROPDOWN --}}
            <div class="dropdown-menu" id="dropdownMenu">

                <div class="dropdown-header">

                    <div class="dropdown-avatar">
                        @if(Auth::user()->foto_profil)
                            <img src="{{ asset('uploads/profile/' . Auth::user()->foto_profil) }}" alt="Profile">
                        @else
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        @endif
                    </div>

                    <div class="dropdown-user">

                        <div class="dropdown-name">
                            {{ Auth::user()->name }}
                        </div>

                        <div class="dropdown-role">

                            @if(Auth::user()->role === 'admin')
                                Admin
                            @elseif(Auth::user()->role === 'kasir')
                                Kasir
                            @endif

                        </div>

                    </div>

                </div>


                <div class="dropdown-links">

                    <a href="{{ route('profile') }}" class="dropdown-item">
                        <i class="fa-regular fa-user"></i>
                        <span>Profile</span>
                    </a>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf

                        <button class="dropdown-item logout-item" type="submit">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Logout</span>
                        </button>
                    </form>

                </div>

            </div>

        </div>

    </div>

</nav>