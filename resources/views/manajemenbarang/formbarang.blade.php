@extends('layouts.app')

@section('content')
@vite('resources/css/pages/manajemenbarang.css')

<div class="manajemen-barang-container">

    <h1>Manajemen Barang</h1>

    <div class="tab-container">
        <a href="{{ route('manajemenbarang.databarang') }}" class="tab-button">Data Barang</a>
        <a href="{{ route('manajemenbarang.formbarang') }}" class="tab-button active">Form Barang</a>
    </div>

    <div class="form-container">
        <div class="upload-wrapper">
            <a href="{{ route('manajemenbarang.template') }}" class="download-btn">Download Template</a>
            <form action="{{ route('manajemenbarang.import') }}" method="POST" enctype="multipart/form-data" class="upload-form">
                @csrf
                <input type="file" name="file" id="file-upload" class="hidden-input" accept=".xls,.xlsx" required onchange="document.getElementById('file-name').textContent = this.files[0] ? this.files[0].name : 'Belum ada file';">
                <label for="file-upload" class="choose-file-btn">Pilih File Excel</label>
                <span id="file-name" class="file-name-text">Belum ada file dipilih</span>
                <button type="submit" class="upload-btn">Upload File</button>
            </form>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--mb-border); margin: 32px 0;">

        <form action="{{ route('manajemenbarang.store') }}" method="POST" id="formTambahBarang">
            @csrf
            
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Barang <span style="color: var(--mb-danger);">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="nama_barang" class="validate-me" placeholder="Masukkan nama barang">
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <span class="error-text">Field ini wajib diisi!</span>
                </div>
                <div class="form-group">
                    <label>Tipe Barang <span style="color: var(--mb-danger);">*</span></label>
                    <div class="input-wrapper">
                        <select name="tipe_barang" class="validate-me">
                            <option value="stok" selected>Stok</option>
                            <option value="non_stok">Non-Stok</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Jumlah/Stok <span style="color: var(--mb-danger);">*</span></label>
                    <div class="input-wrapper">
                        <input type="number" name="stok" class="validate-me" placeholder="0">
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <span class="error-text">Stok tidak boleh kosong!</span>
                </div>
                <div class="form-group">
                    <label>Satuan <span style="color: var(--mb-danger);">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="satuan" class="validate-me" placeholder="Pcs, Box, dll">
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <span class="error-text">Satuan wajib diisi!</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Harga Beli <span style="color: var(--mb-danger);">*</span></label>
                    <div class="input-wrapper">
                        <input type="number" name="harga_beli" class="validate-me" placeholder="0">
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <span class="error-text">Harga beli wajib diisi!</span>
                </div>
                <div class="form-group">
                    <label>Harga Jual <span style="color: var(--mb-danger);">*</span></label>
                    <div class="input-wrapper">
                        <input type="number" name="harga_jual" class="validate-me" placeholder="0">
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <span class="error-text">Harga jual wajib diisi!</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Kategori <span style="color: var(--mb-danger);">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="kategori" class="validate-me" placeholder="Contoh: Makanan">
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <span class="error-text">Kategori wajib diisi!</span>
                </div>
                <div class="form-group">
                    <label>Supplier <span style="font-weight: 400; color: #94A3B8; font-size: 12px;">(Opsional)</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="supplier" placeholder="Nama supplier">
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn">Simpan Barang</button>
        </form>
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
        <button class="submit-btn" onclick="closeAlertModal()" style="width: 100%; margin-top: 32px;">Tutup</button>
    </div>
</div>
@endif

<script>
    function closeAlertModal() {
        const modal = document.getElementById('alertModal');
        if(modal) modal.classList.remove('active');
    }

    document.getElementById('formTambahBarang').addEventListener('submit', function(e) {
        let isValid = true;
        const inputs = this.querySelectorAll('.validate-me');
        
        inputs.forEach(input => {
            const wrapper = input.closest('.input-wrapper');
            if(input.value.trim() === '') {
                isValid = false;
                wrapper.classList.add('error');
            } else {
                wrapper.classList.remove('error');
            }
        });

        if(!isValid) { e.preventDefault(); }
    });

    document.querySelectorAll('.validate-me').forEach(input => {
        input.addEventListener('input', function() {
            this.closest('.input-wrapper').classList.remove('error');
        });
    });
</script>
@endsection