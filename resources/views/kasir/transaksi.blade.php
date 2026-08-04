@extends('layouts.app')

@section('content')
@vite('resources/css/pages/kasir.css')

<!-- CDN jQuery & Tom Select -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<style>
/* Styling Tom Select agar presisi & simetris dengan Layout Kasir */
.ts-control {
    height: 44px !important;
    border-radius: 8px !important;
    border: 1px solid var(--ks-border) !important;
    padding: 0 14px !important;
    display: flex !important;
    align-items: center !important;
    font-size: 14px !important;
    background-color: #ffffff !important;
    box-shadow: none !important;
}
.ts-wrapper.focus .ts-control {
    border-color: var(--ks-primary-light) !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
}
.ts-dropdown {
    border-radius: 8px !important;
    border: 1px solid var(--ks-border) !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
    margin-top: 4px !important;
    z-index: 9999 !important;
}
.ts-dropdown .option {
    padding: 10px 14px !important;
    font-size: 14px !important;
}
.ts-dropdown .active {
    background-color: #F1F5F9 !important;
    color: var(--ks-primary) !important;
    font-weight: 600 !important;
}
.ts-dropdown .option[data-disabled] {
    color: #EF4444 !important;
    background-color: #FEF2F2 !important;
    font-weight: 600 !important;
    opacity: 0.7;
    cursor: not-allowed;
}
</style>

<div class="kasir-container">
    <div class="kasir-header">
        <h1>Kasir</h1>
        <div class="tab-container">
            <a href="{{ route('kasir.transaksi') }}" class="tab-button active">Transaksi</a>
            <a href="{{ route('kasir.riwayat') }}" class="tab-button">History</a>
        </div>
    </div>

    <div class="kasir-layout" id="kasirLayout">

        {{-- LEFT PANEL: DRAFT TRANSAKSI --}}
        <div class="draft-container">
            <div class="draft-header">
                <h2>Draft Transaksi</h2>
                <button class="add-draft-btn" id="togglePanelBtn" onclick="toggleTransactionPanel()">+</button>
            </div>

            <div class="draft-list-container">
                @if($drafts->isEmpty())
                    <div class="draft-empty-box">
                        <div class="empty-icon">📂</div>
                        <h4>Tidak Ada Draft</h4>
                        <p>Belum ada draft transaksi aktif saat ini. Silakan klik tombol di atas untuk membuat transaksi baru.</p>
                    </div>
                @else
                    <div class="draft-list">
                        @foreach($drafts as $draft)
                        <div class="draft-card" onclick="loadDraft({{ $draft->id }})">
                            <div class="draft-top">
                                <h4>{{ $draft->jenis_motor ?? 'Motor' }} {{ $draft->nomor_kendaraan ? '['.$draft->nomor_kendaraan.']' : '' }}</h4>
                                <span class="badge">Draft</span>
                            </div>
                            <div class="draft-info">
                                <small>{{ $draft->created_at->format('d F Y - H:i') }}</small>
                                <small>{{ $draft->metode_pembayaran ?? 'Tunai' }}</small>
                            </div>
                            <p class="draft-price">Rp. {{ number_format($draft->total_harga, 0, ',', '.') }}</p>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT PANEL: TRANSACTION INPUT --}}
        <div class="transaction-panel" id="transactionPanel">
            <form action="{{ route('kasir.store') }}" method="POST" id="formTransaksi">
                @csrf
                <input type="hidden" name="transaksi_id" id="formTransaksiId">
                
                <div class="panel-section">
                    <h3>Informasi Transaksi</h3>
                    <div class="form-grid-2" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;"> 
                        <input type="text" name="jenis_motor" id="formJenisMotor" placeholder="Jenis Motor" required>
                        <input type="text" name="nomor_kendaraan" id="formNomorKendaraan" placeholder="No. Kendaraan (Plat Nomor)">
                    </div>
                </div>

                {{-- TAMBAH BARANG STOK --}}
                <div class="panel-section">
                    <h3>Tambah Barang Stok</h3>
                    <div class="dropdown-row-grid">
                        <div class="select-wrapper">
                            <select id="selectBarangStok" placeholder="Pilih / Cari Barang Stok...">
                                @foreach($barangStok as $barang)
                                    <option value="{{ $barang->id }}" 
                                            data-nama="{{ $barang->nama_barang }}" 
                                            data-harga="{{ $barang->harga_jual }}"
                                            data-harga-beli="{{ $barang->harga_beli }}"
                                            {{ $barang->stok <= 0 ? 'disabled' : '' }}>
                                        {{ $barang->nama_barang }} (Stok: {{ $barang->stok }} {{ $barang->satuan }}) {{ $barang->stok <= 0 ? '- [HABIS]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn-blue btn-fixed-action" onclick="addBarangToTable('selectBarangStok')">+ Tambah Stok</button>
                    </div>
                </div>

                {{-- TAMBAH BARANG NON STOK --}}
                <div class="panel-section">
                    <h3>Tambah Barang Non Stok</h3>
                    <div class="dropdown-row-grid">
                        <div class="select-wrapper">
                            <select id="selectBarangNonStok" placeholder="Pilih / Cari Barang Non Stok...">
                                @foreach($barangNonStok as $barang)
                                    <option value="{{ $barang->id }}" 
                                            data-nama="{{ $barang->nama_barang }}" 
                                            data-harga="{{ $barang->harga_jual }}"
                                            data-harga-beli="{{ $barang->harga_beli }}"
                                            {{ $barang->stok <= 0 ? 'disabled' : '' }}>
                                        {{ $barang->nama_barang }} (Stok: {{ $barang->stok }} {{ $barang->satuan }}) {{ $barang->stok <= 0 ? '- [HABIS]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn-blue btn-fixed-action" onclick="addBarangToTable('selectBarangNonStok')">+ Tambah Non Stok</button>
                    </div>
                </div>

                @if(auth()->user()->can_input_manual_barang)
                <div class="panel-section manual-section">
                    <h3>Barang Manual</h3>
                    <div class="form-grid-3 mb-2">
                        <input type="text" id="manNama" placeholder="Nama Barang">
                        <input type="text" id="manSatuan" placeholder="Satuan (Misal: Pcs)">
                        <input type="text" id="manSupplier" placeholder="Supplier (Opsional)">
                    </div>
                    <div class="form-grid-3">
                        <input type="number" id="manHargaBeli" placeholder="Harga Beli">
                        <input type="number" id="manHargaJual" placeholder="Harga Jual">
                        <button type="button" class="btn-blue" onclick="addManualToTable()">+ Tambah Manual</button>
                    </div>
                </div>
                @endif

                <div class="items-table-container">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="cartTableBody">
                            <tr id="emptyRow">
                                <td colspan="5" class="empty-row text-center">Belum ada barang ditambahkan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- SUMMARY SECTION --}}
                <div class="summary-section">
                    <input type="hidden" name="total_tagihan" id="inputTotalTagihan" value="0">
                    
                    <div class="summary-row line-separator">
                        <span>Biaya Jasa Servis</span>
                        <input type="number" name="biaya_jasa_servis" id="formBiayaJasa" placeholder="0" onkeyup="kalkulasiTotal()" onchange="kalkulasiTotal()">
                    </div>

                    <div class="summary-row line-separator">
                        <span>Metode Pembayaran</span>
                        <select name="metode_pembayaran" id="formMetode" onchange="toggleMetodePembayaran()">
                            <option value="Tunai">Tunai</option>
                            <option value="Transfer">Transfer</option>
                            <option value="QRIS">QRIS</option>
                        </select>
                    </div>

                    <div class="summary-row">
                        <span>Total Barang</span>
                        <span id="textTotalBarang">Rp. 0</span>
                    </div>
                    <div class="summary-row">
                        <span>Jasa Servis</span>
                        <span id="textJasaServis">Rp. 0</span>
                    </div>
                    
                    <div class="summary-row" id="rowNominalBayar">
                        <span>Nominal Dibayar</span>
                        <input type="number" id="inputNominal" name="bayar" class="input-nominal" onkeyup="hitungKembalian()" placeholder="0">
                    </div>
                    
                    <div class="summary-row" id="rowKembalian">
                        <span>Kembalian</span>
                        <span id="textKembalian">Rp. 0</span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total Tagihan Akhir</span>
                        <span id="textTotalAkhir">Rp. 0</span>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn-red" onclick="clearFormAndTable()">Clear</button>
                    <div class="right-actions">
                        <button type="submit" name="submit_type" value="draft" class="btn-dark-blue">Simpan Draft</button>
                        <button type="submit" name="submit_type" value="selesai" class="btn-green">Cetak</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- POPUP MODAL ERROR --}}
@if(session('error'))
<div class="modal-overlay active" id="alertModalError" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;">
    <div class="modal-content alert-modal" style="background: #fff; padding: 30px; border-radius: 12px; width: 400px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div class="alert-icon error" style="background: #FEE2E2; color: #EF4444; width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 24px; font-weight: bold;">&times;</div>
        @if(str_contains(strtolower(session('error')), 'akses') || str_contains(strtolower(session('error')), 'ditolak'))
            <h2 style="font-size: 20px; font-weight: 700; color: #0F172A; margin-bottom: 8px;">Akses Ditolak!</h2>
        @else
            <h2 style="font-size: 20px; font-weight: 700; color: #0F172A; margin-bottom: 8px;">Transaksi Gagal!</h2>
        @endif
        <p style="font-size: 14px; color: #64748B; margin-bottom: 24px; line-height: 1.5;">{{ session('error') }}</p>
        <button type="button" class="btn-blue" onclick="document.getElementById('alertModalError').remove()" style="width: 100%; background: #2563EB; color: #fff; height: 44px; border: none; border-radius: 8px; font-weight: 600;">Tutup</button>
    </div>
</div>
@endif

{{-- POPUP MODAL SUKSES --}}
@if(session('success'))
<div class="modal-overlay active" id="alertModalSuccess" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;">
    <div class="modal-content alert-modal" style="background: #fff; padding: 30px; border-radius: 12px; width: 400px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div class="alert-icon success" style="background: #DCFCE7; color: #22C55E; width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 24px; font-weight: bold;">&#10003;</div>
        <h2 style="font-size: 20px; font-weight: 700; color: #0F172A; margin-bottom: 8px;">Berhasil!</h2>
        <p style="font-size: 14px; color: #64748B; margin-bottom: 24px; line-height: 1.5;">{{ session('success') }}</p>
        <button type="button" class="btn-blue" onclick="document.getElementById('alertModalSuccess').remove()" style="width: 100%; background: #2563EB; color: #fff; height: 44px; border: none; border-radius: 8px; font-weight: 600;">Tutup</button>
    </div>
</div>
@endif

<script>
    const draftsData = @json($drafts);
    let cartIndex = 0;
    let tsStok, tsNonStok;

    $(document).ready(function() {
        // Inisialisasi Tom Select dengan konfigurasi bersih
        tsStok = new TomSelect('#selectBarangStok', {
            create: false,
            maxItems: 1,
            placeholder: "Pilih / Cari Barang Stok...",
            noResultsMessage: "Barang tidak ditemukan"
        });

        tsNonStok = new TomSelect('#selectBarangNonStok', {
            create: false,
            maxItems: 1,
            placeholder: "Pilih / Cari Barang Non Stok...",
            noResultsMessage: "Barang tidak ditemukan"
        });

        // Kosongkan nilai bawaan agar memunculkan placeholder bersih
        tsStok.clear();
        tsNonStok.clear();
    });

    function toggleMetodePembayaran() {
        const metode = document.getElementById('formMetode').value;
        const rowNominalBayar = document.getElementById('rowNominalBayar');
        const rowKembalian = document.getElementById('rowKembalian');
        const inputNominal = document.getElementById('inputNominal');

        if (metode === 'Tunai') {
            rowNominalBayar.style.display = 'flex';
            rowKembalian.style.display = 'flex';
        } else {
            rowNominalBayar.style.display = 'none';
            rowKembalian.style.display = 'none';
            inputNominal.value = '';
        }
    }

    function toggleTransactionPanel() {
        const layout = document.getElementById('kasirLayout');
        const btn = document.getElementById('togglePanelBtn');
        
        if (layout.classList.contains('panel-open')) {
            layout.classList.remove('panel-open');
            btn.innerHTML = '+';
            btn.classList.remove('btn-close-state');
            clearFormAndTable();
        } else {
            clearFormAndTable();
            layout.classList.add('panel-open');
            btn.innerHTML = '&#10142;'; 
            btn.classList.add('btn-close-state');
        }
    }

    function loadDraft(id) {
        clearFormAndTable();
        
        let draft = draftsData.find(d => d.id === id);
        if(draft) {
            document.getElementById('formTransaksiId').value = draft.id;
            document.getElementById('formJenisMotor').value = draft.jenis_motor || '';
            document.getElementById('formNomorKendaraan').value = draft.nomor_kendaraan || '';
            document.getElementById('formMetode').value = draft.metode_pembayaran || 'Tunai';
            document.getElementById('formBiayaJasa').value = draft.biaya_jasa_servis || '';

            toggleMetodePembayaran();

            if(draft.details && draft.details.length > 0) {
                draft.details.forEach(detail => {
                    let namaItem = detail.barang_id ? (detail.barang ? detail.barang.nama_barang : '-') : detail.nama_barang_manual;
                    
                    renderRowHtml({
                        id: detail.barang_id,
                        nama: namaItem,
                        harga: parseFloat(detail.harga_jual_satuan) || 0,
                        harga_beli: parseFloat(detail.harga_modal) || 0,
                        qty: parseInt(detail.jumlah) || 1
                    }, detail.barang_id === null);
                });
            }

            kalkulasiTotal();
            
            document.getElementById('kasirLayout').classList.add('panel-open');
            const btn = document.getElementById('togglePanelBtn');
            btn.innerHTML = '&#10142;';
            btn.classList.add('btn-close-state');
        }
    }

    function addBarangToTable(selectElementId) {
        let tsInstance = selectElementId === 'selectBarangStok' ? tsStok : tsNonStok;
        let selectedValue = tsInstance.getValue();
        
        if(!selectedValue || selectedValue === "") return alert("Pilih barang terlebih dahulu!");

        let selectElem = document.getElementById(selectElementId);
        let option = selectElem.querySelector(`option[value="${selectedValue}"]`);
        
        if(option && option.hasAttribute('disabled')) {
            alert("Barang ini tidak dapat dipilih karena stok di toko sudah habis (0)!");
            tsInstance.clear();
            return;
        }

        renderRowHtml({
            id: selectedValue,
            nama: option.getAttribute('data-nama'),
            harga: parseFloat(option.getAttribute('data-harga')) || 0,
            harga_beli: parseFloat(option.getAttribute('data-harga-beli')) || 0,
            qty: 1
        }, false);
        
        tsInstance.clear();
    }

    function addManualToTable() {
        let nama = document.getElementById('manNama').value;
        let satuan = document.getElementById('manSatuan').value;
        let hargaBeli = document.getElementById('manHargaBeli').value;
        let hargaJual = document.getElementById('manHargaJual').value;
        let supplier = document.getElementById('manSupplier') ? document.getElementById('manSupplier').value : '';
        
        if(!nama || !hargaJual) return alert("Nama barang dan Harga Jual wajib diisi!");

        renderRowHtml({
            id: null, 
            nama: nama + (satuan ? ` (${satuan})` : ''),
            harga: parseFloat(hargaJual),
            harga_beli: parseFloat(hargaBeli) || 0,
            qty: 1,
            supplier: supplier
        }, true);

        document.getElementById('manNama').value = '';
        document.getElementById('manSatuan').value = '';
        if(document.getElementById('manSupplier')) document.getElementById('manSupplier').value = '';
        document.getElementById('manHargaBeli').value = '';
        document.getElementById('manHargaJual').value = '';
    }

    function renderRowHtml(item, isManual) {
        document.getElementById('emptyRow').style.display = 'none';
        
        let sub = item.harga * item.qty;
        let tr = document.createElement('tr');
        tr.id = `row-${cartIndex}`;
        
        let hiddenId = isManual ? '' : `<input type="hidden" name="items[${cartIndex}][barang_id]" value="${item.id}">`;
        let supplierInput = item.supplier ? `<input type="hidden" name="items[${cartIndex}][supplier]" value="${item.supplier}">` : '';
        
        tr.innerHTML = `
            <td>
                <strong>${item.nama}</strong> ${isManual ? '<span style="color:#2563EB; font-size:11px; font-weight:600;">(Manual)</span>' : ''}
                ${hiddenId}
                ${supplierInput}
                <input type="hidden" name="items[${cartIndex}][nama_barang]" value="${item.nama}">
                <input type="hidden" name="items[${cartIndex}][harga]" value="${item.harga}">
                <input type="hidden" name="items[${cartIndex}][harga_beli]" value="${item.harga_beli}">
            </td>
            <td>
                <input type="number" name="items[${cartIndex}][qty]" class="qty-input" value="${item.qty}" min="1" onkeyup="updateSubtotal(${cartIndex}, ${item.harga})" onchange="updateSubtotal(${cartIndex}, ${item.harga})">
            </td>
            <td>Rp. ${formatRupiah(item.harga)}</td>
            <td id="subtotal-text-${cartIndex}" class="subtotal-val" data-subtotal="${sub}">Rp. ${formatRupiah(sub)}</td>
            <td><button type="button" class="btn-del" onclick="removeRow(${cartIndex})">X</button></td>
        `;
        
        document.getElementById('cartTableBody').appendChild(tr);
        cartIndex++;
        kalkulasiTotal();
    }

    function removeRow(index) {
        document.getElementById(`row-${index}`).remove();
        if(document.querySelectorAll('tr[id^="row-"]').length === 0) {
            document.getElementById('emptyRow').style.display = '';
        }
        kalkulasiTotal();
    }

    function updateSubtotal(index, harga) {
        let qtyInput = document.querySelector(`#row-${index} .qty-input`);
        let qty = parseInt(qtyInput.value) || 1;
        if(qty < 1) {
            qty = 1;
            qtyInput.value = 1;
        }
        let sub = harga * qty;
        let subTd = document.getElementById(`subtotal-text-${index}`);
        subTd.setAttribute('data-subtotal', sub);
        subTd.innerText = `Rp. ${formatRupiah(sub)}`;
        kalkulasiTotal();
    }

    function kalkulasiTotal() {
        let subtotals = document.querySelectorAll('.subtotal-val');
        let totalBarang = 0;
        subtotals.forEach(el => {
            totalBarang += parseFloat(el.getAttribute('data-subtotal')) || 0;
        });

        let biayaJasa = parseFloat(document.getElementById('formBiayaJasa').value) || 0;
        let totalAkhir = totalBarang + biayaJasa;

        document.getElementById('textTotalBarang').innerText = `Rp. ${formatRupiah(totalBarang)}`;
        document.getElementById('textJasaServis').innerText = `Rp. ${formatRupiah(biayaJasa)}`;
        document.getElementById('inputTotalTagihan').value = totalAkhir;
        document.getElementById('textTotalAkhir').innerText = `Rp. ${formatRupiah(totalAkhir)}`;
        
        hitungKembalian();
    }

    function hitungKembalian() {
        let totalTagihan = parseFloat(document.getElementById('inputTotalTagihan').value) || 0;
        let dibayar = parseFloat(document.getElementById('inputNominal').value) || 0;
        let kembali = dibayar - totalTagihan;
        
        if(kembali < 0 || totalTagihan === 0) kembali = 0;
        document.getElementById('textKembalian').innerText = `Rp. ${formatRupiah(kembali)}`;
    }

    function clearFormAndTable() {
        document.getElementById('formTransaksi').reset();
        document.getElementById('formTransaksiId').value = '';
        document.querySelectorAll('tr[id^="row-"]').forEach(tr => tr.remove());
        document.getElementById('emptyRow').style.display = '';

        if(tsStok) tsStok.clear();
        if(tsNonStok) tsNonStok.clear();

        toggleMetodePembayaran();
        kalkulasiTotal();
    }

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }
</script>
@endsection