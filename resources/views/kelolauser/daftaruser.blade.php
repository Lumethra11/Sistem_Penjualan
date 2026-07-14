@extends('layouts.app')

@section('title', 'Daftar Kasir')

@vite('resources/css/pages/kelolauser.css')

@section('content')

<div class="kelola-user-container">

    <h1>Kelola Kasir</h1>

    {{-- TAB UTAMA --}}
    <div class="tab-container">
        <a href="{{ route('kelolauser.tambah') }}" class="tab-button">Tambah Kasir</a>
        <a href="{{ route('kelolauser.daftar') }}" class="tab-button active">Daftar Kasir</a>
    </div>

    @php
        $statusFilter = request('status', 'aktif');
    @endphp

    {{-- FILTER TABS --}}
    <div class="filter-tabs">
        <a href="{{ route('kelolauser.daftar', ['status' => 'aktif']) }}" 
           class="filter-btn {{ $statusFilter == 'aktif' ? 'active' : '' }}">
            Kasir Aktif
        </a>
        <a href="{{ route('kelolauser.daftar', ['status' => 'nonaktif']) }}" 
           class="filter-btn {{ $statusFilter == 'nonaktif' ? 'active' : '' }}">
            Kasir Tidak Aktif
        </a>
    </div>

    {{-- KONDISI FILTER AKTIF --}}
    @if($statusFilter == 'aktif')
        <div class="user-grid">
            @forelse($users->where('is_active', true) as $user)
                <div class="user-card">
                    <span class="status-badge aktif">Aktif</span>

                    <div class="user-top">
                        <div class="user-avatar">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="user-meta">
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
                        <div class="info-item-row">
                            <span>Izin Input Manual</span>
                            <span class="inline-privilege {{ $user->can_input_manual_barang ? 'allowed' : 'denied' }}">
                                {{ $user->can_input_manual_barang ? 'Diizinkan' : 'Tidak Diizinkan' }}
                            </span>
                        </div>
                    </div>

                    <div class="user-footer">
                        <div class="action-group">
                            <form action="{{ route('kelolauser.manual.permission', $user->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                @if($user->can_input_manual_barang)
                                    <button type="submit" class="action-btn warning-btn">Cabut Izin</button>
                                @else
                                    <button type="submit" class="action-btn primary-btn">Izinkan Manual</button>
                                @endif
                            </form>
                            <button type="button" class="action-btn danger-btn" onclick="openModal({{ $user->id }}, 'nonaktifkan')">
                                Nonaktifkan
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-user">Tidak ada kasir aktif saat ini.</div>
            @endforelse
        </div>

    {{-- KONDISI FILTER NONAKTIF --}}
    @else
        <div class="user-grid">
            @forelse($users->where('is_active', false) as $user)
                <div class="user-card inactive-card">
                    <span class="status-badge nonaktif">Tidak Aktif</span>

                    <div class="user-top">
                        <div class="user-avatar">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="user-meta">
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
                    </div>

                    <div class="user-footer">
                        <div class="action-group">
                            <form action="{{ route('kelolauser.manual.permission', $user->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                @if($user->can_input_manual_barang)
                                    <button type="submit" class="action-btn warning-btn">Cabut Izin</button>
                                @else
                                    <button type="submit" class="action-btn primary-btn">Izinkan Manual</button>
                                @endif
                            </form>
                            <button type="button" class="action-btn success-btn" onclick="openModal({{ $user->id }}, 'aktifkan')">
                                Aktifkan
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-user">Tidak ada Kasir nonaktif saat ini.</div>
            @endforelse
        </div>
    @endif

    {{-- PAGINATION --}}
    <div class="pagination-wrapper">
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
            title.innerText = 'Nonaktifkan Kasir';
            desc.innerText = 'Apakah Anda yakin ingin menonaktifkan kasir ini?';
        } else {
            title.innerText = 'Aktifkan Kasir';
            desc.innerText = 'Apakah Anda yakin ingin mengaktifkan kasir ini?';
        }

        modal.classList.add('active');
    }

    function closeModal() {
        document.getElementById('statusModal').classList.remove('active');
    }
</script>

@endsection