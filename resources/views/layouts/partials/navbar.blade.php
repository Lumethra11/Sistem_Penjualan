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

        {{-- NOTIF DROPDOWN POP-UP KHUSUS ADMIN --}}
        @if(Auth::user()->role === 'admin')
            <div class="nav-notif-wrapper">
                <button class="nav-icon" id="notifBellBtn" title="Notifikasi Ringkas">
                    <i class="fa-regular fa-bell"></i>
                    <span class="notification-red-dot" id="notifRedDot" style="display: none;"></span>
                </button>

                {{-- DROPDOWN POP-UP KECIL MELAYANG KE BAWAH --}}
                <div class="navbar-notif-dropdown" id="notifDropdownMenu">
                    <div class="notif-dropdown-header">
                        <h3>Notifikasi</h3>
                        <span class="notif-header-count" id="notifCountText">0 Baru</span>
                    </div>
                    
                    <div class="notif-dropdown-body" id="notifContainerList">
                        <div class="notif-loading-text">Memuat data...</div>
                    </div>

                    <div class="notif-dropdown-footer">
                        <a href="{{ route('notifikasi.index') }}" class="btn-view-all-notif">Lihat Semua Notifikasi</a>
                    </div>
                </div>
            </div>
        @endif

        {{-- USER DROPDOWN --}}
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
                        <div class="dropdown-name">{{ Auth::user()->name }}</div>
                        <div class="dropdown-role">{{ Auth::user()->role === 'admin' ? 'Admin' : 'Kasir' }}</div>
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

@if(Auth::user()->role === 'admin')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const bellBtn = document.getElementById('notifBellBtn');
        const dropdown = document.getElementById('notifDropdownMenu');
        const redDot = document.getElementById('notifRedDot');
        const countText = document.getElementById('notifCountText');
        const containerList = document.getElementById('notifContainerList');

        let liveNotifs = [];

        function loadNavbarNotifs() {
            let readItems = JSON.parse(localStorage.getItem('read_notif_ids') || "[]");

            fetch("{{ route('api.notifikasi') }}")
                .then(response => response.json())
                .then(res => {
                    if(res.status === 'success') {
                        liveNotifs = res.data.filter(item => !readItems.includes(item.id_item));
                        countText.innerText = liveNotifs.length + " Baru";
                        
                        if(liveNotifs.length > 0) {
                            redDot.style.display = 'block';
                            containerList.innerHTML = "";
                            
                            liveNotifs.slice(0, 4).forEach(item => {
                                let itemHtml = `
                                    <div class="notif-dropdown-item type-${item.tipe}" id="pop-${item.id_item}">
                                        <div class="notif-item-icon">
                                            <i class="fa-solid ${item.icon}"></i>
                                        </div>
                                        <div class="notif-item-text">
                                            <h4>${item.judul}</h4>
                                            <p>${item.pesan}</p>
                                        </div>
                                        <button class="btn-pop-dismiss" data-id="${item.id_item}">&times;</button>
                                    </div>
                                `;
                                containerList.insertAdjacentHTML('beforeend', itemHtml);
                            });

                            document.querySelectorAll('.btn-pop-dismiss').forEach(btn => {
                                btn.addEventListener('click', function(e) {
                                    e.stopPropagation();
                                    let targetId = this.getAttribute('data-id');
                                    dismissNavbarNotif(targetId);
                                });
                            });
                        } else {
                            redDot.style.display = 'none';
                            containerList.innerHTML = '<div class="notif-empty-state">Tidak ada notifikasi baru.</div>';
                        }
                    }
                })
                .catch(() => {
                    containerList.innerHTML = '<div class="notif-empty-state">Gagal memuat data.</div>';
                });
        }

        function dismissNavbarNotif(id) {
            let readItems = JSON.parse(localStorage.getItem('read_notif_ids') || "[]");
            if(!readItems.includes(id)) {
                readItems.push(id);
                localStorage.setItem('read_notif_ids', JSON.stringify(readItems));
            }
            document.getElementById('pop-' + id)?.remove();
            liveNotifs = liveNotifs.filter(item => item.id_item !== id);
            countText.innerText = liveNotifs.length + " Baru";
            if(liveNotifs.length === 0) { redDot.style.display = 'none'; containerList.innerHTML = '<div class="notif-empty-state">Tidak ada notifikasi baru.</div>'; }
        }

        bellBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('show');
            document.getElementById('dropdownMenu').classList.remove('show');
        });

        document.addEventListener('click', function() { dropdown.classList.remove('show'); });
        loadNavbarNotifs();
    });
</script>
@endif