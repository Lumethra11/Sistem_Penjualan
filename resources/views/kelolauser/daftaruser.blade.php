@extends('layouts.app')

@section('title', 'Daftar User')

@vite('resources/css/pages/kelolauser.css')

@section('content')

<div class="kelola-user-container">

    <h1>Kelola User</h1>

    {{-- TAB UTAMA (MAIN NAVIGATION) --}}
    <div class="tab-container">
        <a href="{{ route('kelolauser.tambah') }}" class="tab-button">
            Tambah User
        </a>
        <a href="{{ route('kelolauser.daftar') }}" class="tab-button active">
            Daftar User
        </a>
    </div>

    {{-- Ambil parameter status dari URL, default ke 'aktif' --}}
    @php
        $statusFilter = request('status', 'aktif');
    @endphp

    {{-- FILTER TABS (UNDERLINE DESIGN) --}}
    <div class="filter-tabs">
        <a href="{{ route('kelolauser.daftar', ['status' => 'aktif']) }}" 
           class="filter-btn {{ $statusFilter == 'aktif' ? 'active' : '' }}">
            User Aktif
        </a>
        <a href="{{ route('kelolauser.daftar', ['status' => 'nonaktif']) }}" 
           class="filter-btn {{ $statusFilter == 'nonaktif' ? 'active' : '' }}">
            User Tidak Aktif
        </a>
    </div>

    {{-- =========================================
         KONDISI JIKA FILTER "AKTIF" DIPILIH
         ========================================= --}}
    @if($statusFilter == 'aktif')
        <div class="user-grid">
            @forelse($users->where('is_active', true) as $user)
                <div class="user-card">
                    <div class="user-top">
                        <div class="user-avatar">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3>{{ $user->name }}</h3>
                            <p>{{ '@' . $user->username }}</p>
                        </div>
                    </div>

                    <div class="user-info">
                        <div class="info-item">
                            <span>Email</span>
                            <strong>{{ $user->email }}</strong>
                        </div>
                        <div class="info-item">
                            <span>No Telepon</span>
                            <strong>{{ $user->no_telp ?? '-' }}</strong>
                        </div>
                        <div class="info-item">
                            <span>Alamat</span>
                            <strong>{{ $user->alamat ?? '-' }}</strong>
                        </div>
                    </div>

                    <div class="user-footer">
                        <span class="status aktif">Aktif</span>
                        <button type="button" class="status-btn danger-btn"
                                onclick="openModal({{ $user->id }}, 'nonaktifkan')">
                            Nonaktifkan
                        </button>
                    </div>
                </div>
            @empty
                <div class="empty-user">
                    Tidak ada user aktif saat ini.
                </div>
            @endforelse
        </div>

    {{-- =========================================
         KONDISI JIKA FILTER "NONAKTIF" DIPILIH
         ========================================= --}}
    @else
        <div class="user-grid">
            @forelse($users->where('is_active', false) as $user)
                <div class="user-card inactive-card">
                    <div class="user-top">
                        <div class="user-avatar">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3>{{ $user->name }}</h3>
                            <p>{{ '@' . $user->username }}</p>
                        </div>
                    </div>

                    <div class="user-info">
                        <div class="info-item">
                            <span>Email</span>
                            <strong>{{ $user->email }}</strong>
                        </div>
                        <div class="info-item">
                            <span>No Telepon</span>
                            <strong>{{ $user->no_telp ?? '-' }}</strong>
                        </div>
                        <div class="info-item">
                            <span>Alamat</span>
                            <strong>{{ $user->alamat ?? '-' }}</strong>
                        </div>
                    </div>

                    <div class="user-footer">
                        <span class="status nonaktif">Tidak Aktif</span>
                        <button type="button" class="status-btn"
                                onclick="openModal({{ $user->id }}, 'aktifkan')">
                            Aktifkan
                        </button>
                    </div>
                </div>
            @empty
                <div class="empty-user">
                    Tidak ada user nonaktif saat ini.
                </div>
            @endforelse
        </div>
    @endif

    {{-- PAGINATION --}}
    <div class="pagination-wrapper">
        {{-- Appends 'status' agar saat pindah page, parameter filter tidak hilang --}}
        {{ $users->appends(['status' => $statusFilter])->links() }}
    </div>

</div>

{{-- MODAL --}}
<div class="modal-overlay" id="statusModal">
    <div class="modal-content alert-modal">
        <div class="alert-title" id="modalTitle">Konfirmasi</div>
        <p class="alert-desc" id="modalDesc"></p>

        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
            <form id="statusForm" method="POST">
                @csrf
                @method('PUT')
                <button type="submit" class="btn-confirm-delete">Ya, Lanjutkan</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal(userId, action) {
        const modal = document.getElementById('statusModal');
        const title = document.getElementById('modalTitle');
        const desc = document.getElementById('modalDesc');
        const form = document.getElementById('statusForm');

        form.action = `/kelola-user/status/${userId}`;

        if(action === 'nonaktifkan') {
            title.innerText = 'Nonaktifkan User';
            desc.innerText = 'Apakah Anda yakin ingin menonaktifkan user ini?';
        } else {
            title.innerText = 'Aktifkan User';
            desc.innerText = 'Apakah Anda yakin ingin mengaktifkan user ini?';
        }

        modal.classList.add('active');
    }

    function closeModal() {
        document.getElementById('statusModal').classList.remove('active');
    }
</script>

@endsection