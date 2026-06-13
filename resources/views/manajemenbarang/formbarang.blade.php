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

        <hr class="divider-line">

        <form action="{{ route('manajemenbarang.store') }}" method="POST" id="formTambahBarang">
            @csrf
            
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Barang <span class="required-star">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="nama_barang" id="inputNamaBarang" class="validate-me" placeholder="Masukkan nama barang" autocomplete="off">
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        
                        {{-- Container list autocomplete berada di dalam input-wrapper agar posisinya presisi melayang di bawah input --}}
                        <div id="autocompleteList" class="autocomplete-items"></div>
                    </div>
                    <span class="error-text">Field ini wajib diisi!</span>
                </div>
                <div class="form-group">
                    <label>Tipe Barang <span class="required-star">*</span></label>
                    <div class="input-wrapper">
                        <select name="tipe_barang" id="inputTipeBarang" class="validate-me">
                            <option value="stok" selected>Stok</option>
                            <option value="non_stok">Non-Stok</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Jumlah/Stok <span class="required-star">*</span></label>
                    <div class="input-wrapper">
                        <input type="number" name="stok" id="inputStok" class="validate-me" placeholder="0">
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <span class="error-text">Stok tidak boleh kosong!</span>
                </div>
                <div class="form-group">
                    <label>Satuan <span class="required-star">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="satuan" id="inputSatuan" class="validate-me" placeholder="Pcs, Box, dll">
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <span class="error-text">Satuan wajib diisi!</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Harga Beli <span class="required-star">*</span></label>
                    <div class="input-wrapper">
                        <input type="number" name="harga_beli" id="inputHargaBeli" class="validate-me" placeholder="0">
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <span class="error-text">Harga beli wajib diisi!</span>
                </div>
                <div class="form-group">
                    <label>Harga Jual <span class="required-star">*</span></label>
                    <div class="input-wrapper">
                        <input type="number" name="harga_jual" id="inputHargaJual" class="validate-me" placeholder="0">
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <span class="error-text">Harga jual wajib diisi!</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Kategori <span class="required-star">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="kategori" id="inputKategori" class="validate-me" placeholder="Contoh: Oli">
                        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <span class="error-text">Kategori wajib diisi!</span>
                </div>
                <div class="form-group">
                    <label>Supplier <span class="optional-label">(Opsional)</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="supplier" id="inputSupplier" placeholder="Nama supplier">
                    </div>
                </div>
            </div>

            <div class="form-action-container">
                <button type="button" class="clear-form-btn" onclick="clearFormStorage()">Clear Form</button>
                <button type="submit" class="submit-form-btn">Simpan Barang</button>
            </div>
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
        <button class="submit-btn" onclick="closeAlertModal()">Tutup</button>
    </div>
</div>
@endif

<script>
    const formFields = ['inputNamaBarang', 'inputTipeBarang', 'inputStok', 'inputSatuan', 'inputHargaBeli', 'inputHargaJual', 'inputKategori', 'inputSupplier'];

    window.addEventListener('DOMContentLoaded', () => {
        formFields.forEach(fieldId => {
            const savedValue = localStorage.getItem(fieldId);
            const el = document.getElementById(fieldId);
            if (savedValue && el) { el.value = savedValue; }
        });
    });

    formFields.forEach(fieldId => {
        const el = document.getElementById(fieldId);
        if (el) {
            el.addEventListener('input', () => localStorage.setItem(fieldId, el.value));
            el.addEventListener('change', () => localStorage.setItem(fieldId, el.value));
        }
    });

    function clearFormStorage() {
        document.getElementById('formTambahBarang').reset();
        formFields.forEach(fieldId => localStorage.removeItem(fieldId));
        document.querySelectorAll('.input-wrapper').forEach(w => w.classList.remove('error'));
    }

    document.getElementById('formTambahBarang').addEventListener('submit', () => {
        formFields.forEach(fieldId => localStorage.removeItem(fieldId));
    });

    const namaInput = document.getElementById('inputNamaBarang');
    const autocompleteList = document.getElementById('autocompleteList');

    namaInput.addEventListener('input', function() {
        let val = this.value;
        autocompleteList.innerHTML = '';
        if (!val || val.length < 1) return false;

        fetch(`/manajemenbarang/rekomendasi?term=${encodeURIComponent(val)}`)
            .then(response => response.json())
            .then(data => {
                autocompleteList.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(item => {
                        let div = document.createElement('div');
                        div.className = 'autocomplete-item';
                        div.innerHTML = `<strong>${item.nama_barang}</strong> <small>(${item.tipe_barang == 'stok' ? 'Stok' : 'Non-Stok'})</small>`;
                        
                        div.addEventListener('click', function() {
                            namaInput.value = item.nama_barang;
                            document.getElementById('inputTipeBarang').value = item.tipe_barang;
                            document.getElementById('inputSatuan').value = item.satuan;
                            document.getElementById('inputHargaBeli').value = item.harga_beli;
                            document.getElementById('inputHargaJual').value = item.harga_jual;
                            document.getElementById('inputKategori').value = item.kategori;
                            document.getElementById('inputSupplier').value = item.supplier || '';
                            
                            formFields.forEach(fieldId => {
                                const el = document.getElementById(fieldId);
                                if (el) localStorage.setItem(fieldId, el.value);
                            });
                            autocompleteList.innerHTML = '';
                        });
                        autocompleteList.appendChild(div);
                    });
                }
            });
    });

    document.addEventListener('click', function (e) {
        if (e.target !== namaInput) { autocompleteList.innerHTML = ''; }
    });

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

    function closeAlertModal() {
        const modal = document.getElementById('alertModal');
        if(modal) modal.classList.remove('active');
    }
</script>
@endsection