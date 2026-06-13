@extends('layouts.app')

@section('content')
@vite('resources/css/pages/manajemenbarang.css')

<div class="manajemen-barang-container">

    <h1>Manajemen Barang</h1>

    <div class="top-bar">
        <div class="tab-container">
            <a href="{{ route('manajemenbarang.databarang') }}" class="tab-button active">Data Barang</a>
            <a href="{{ route('manajemenbarang.formbarang') }}" class="tab-button">Form Barang</a>
        </div>

        <form action="{{ route('manajemenbarang.databarang') }}" method="GET" class="search-form">
            <input type="hidden" name="tipe" value="{{ $tipe }}">
            <input type="text" name="search" placeholder="Cari barang atau kode..." value="{{ request('search') }}">
            <button type="submit">Cari</button>
        </form>
    </div>

    <div class="filter-tabs">
        <a href="{{ route('manajemenbarang.databarang', ['tipe' => 'stok', 'search' => request('search')]) }}" 
           class="filter-btn {{ $tipe == 'stok' ? 'active' : '' }}">
            Barang Stok
        </a>
        <a href="{{ route('manajemenbarang.databarang', ['tipe' => 'non_stok', 'search' => request('search')]) }}" 
           class="filter-btn {{ $tipe == 'non_stok' ? 'active' : '' }}">
            Barang Non-Stok
        </a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Satuan</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Supplier</th>
                    <th class="text-center-th">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($barang as $item)
                <tr>
                    <td>{{ $item->kode_barang }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->kategori }}</td>
                    <td>{{ $item->stok }}</td>
                    <td>{{ $item->satuan }}</td>
                    <td>Rp. {{ number_format($item->harga_beli,0,',','.') }}</td>
                    <td>Rp. {{ number_format($item->harga_jual,0,',','.') }}</td>
                    <td>{{ $item->supplier ?? '-' }}</td>
                    <td>
                        <div class="action-buttons">
                            @if($tipe == 'stok')
                                <button type="button" class="btn-icon edit" onclick="openEditModal({{ json_encode($item) }})" title="Edit">
                                    <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </button>
                            @else
                                {{-- BUTTON PINDAH SUDAH BERBENTUK ICON SVG (MIGRATE ICON) --}}
                                <form action="/manajemenbarang/{{ $item->id }}/pindah" method="POST" class="inline-form">
                                    @csrf
                                    <button type="submit" class="btn-icon move" title="Pindahkan ke tipe Stok">
                                        <svg viewBox="0 0 24 24"><path d="M17 21v-4H7v-2h10v-4l5 5zM7 3v4h10v2H7v4l-5-5z"/></svg>
                                    </button>
                                </form>
                            @endif
                            
                            <form action="{{ route('manajemenbarang.destroy', $item->id) }}" method="POST" onsubmit="confirmDelete(event, this);">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon delete" title="Hapus">
                                    <svg viewBox="0 0 24 24">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-muted-empty">Data barang tidak ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="pagination-wrapper">
            {{ $barang->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

{{-- MODAL FORM EDIT BARANG STOK --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Data Barang Stok</h2>
            <button class="close-modal-btn" onclick="closeEditModal()">&times;</button>
        </div>

        <form id="formEdit" method="POST" action="">
            @csrf
            @method('PUT') 
            
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Barang</label>
                    <div class="input-wrapper">
                        <input type="text" name="nama_barang" id="edit_nama" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Jumlah/Stok</label>
                    <div class="input-wrapper">
                        <input type="number" name="stok" id="edit_stok" required>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Satuan</label>
                    <div class="input-wrapper">
                        <input type="text" name="satuan" id="edit_satuan" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <div class="input-wrapper">
                        <input type="text" name="kategori" id="edit_kategori" required>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Harga Beli</label>
                    <div class="input-wrapper">
                        <input type="number" name="harga_beli" id="edit_hbeli" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Harga Jual</label>
                    <div class="input-wrapper">
                        <input type="number" name="harga_jual" id="edit_hjual" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Supplier</label>
                <div class="input-wrapper">
                    <input type="text" name="supplier" id="edit_supplier">
                </div>
            </div>

            <button type="submit" class="submit-btn-update">Update Barang</button>
        </form>
    </div>
</div>

{{-- POPUP ALERT CONFIRM DELETE --}}
<div class="modal-overlay" id="deleteModal">
    <div class="modal-content alert-modal">
        <div class="alert-icon error">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
        </div>
        <h2 class="alert-title">Hapus Barang?</h2>
        <p class="alert-desc">Apakah Anda yakin ingin menghapus data barang ini? Tindakan ini tidak dapat dibatalkan.</p>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Batal</button>
            <button type="button" class="btn-confirm-delete" id="confirmDeleteBtn">Ya, Hapus</button>
        </div>
    </div>
</div>

@if(session('success') || session('error'))
<div class="modal-overlay active" id="alertModal">
    <div class="modal-content alert-modal">
        @if(session('success'))
            <div class="alert-icon success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
            <h2 class="alert-title">Berhasil!</h2>
            <p class="alert-desc">{{ session('success') }}</p>
        @else
            <div class="alert-icon error"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg></div>
            <h2 class="alert-title">Oops!</h2>
            <p class="alert-desc">{{ session('error') }}</p>
        @endif
        <button class="submit-btn" onclick="closeAlertModal()">Tutup</button>
    </div>
</div>
@endif

<script>
    function openEditModal(data) {
        document.getElementById('editModal').classList.add('active');
        document.getElementById('formEdit').action = `/manajemenbarang/${data.id}`;
        document.getElementById('edit_nama').value = data.nama_barang;
        document.getElementById('edit_stok').value = data.stok;
        document.getElementById('edit_satuan').value = data.satuan;
        document.getElementById('edit_hbeli').value = data.harga_beli;
        document.getElementById('edit_hjual').value = data.harga_jual;
        document.getElementById('edit_kategori').value = data.kategori;
        document.getElementById('edit_supplier').value = data.supplier || '';
    }

    function closeEditModal() { document.getElementById('editModal').remove('active'); }

    let formToDelete = null;
    function confirmDelete(e, form) {
        e.preventDefault();
        formToDelete = form;
        document.getElementById('deleteModal').classList.add('active');
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
        formToDelete = null;
    }
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (formToDelete) formToDelete.submit();
    });

    function closeAlertModal() {
        const modal = document.getElementById('alertModal');
        if(modal) modal.classList.remove('active');
    }
</script>
@endsection