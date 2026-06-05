@extends('layouts.app')

@section('content')
@vite('resources/css/pages/kasir.css')

<div class="kasir-container">
    <div class="kasir-header">
        <h1>Kasir</h1>
        <div class="tab-container">
            <a href="{{ route('kasir.transaksi') }}" class="tab-button active">Transaksi</a>
            <a href="{{ route('kasir.riwayat') }}" class="tab-button">History</a>
        </div>
    </div>

    <div class="kasir-layout" id="kasirLayout">

        {{-- LEFT: DRAFT SIDEBAR --}}
        <div class="draft-container">
            <div class="draft-header">
                <h2>Draft Transaksi</h2>
                <button class="add-draft-btn" onclick="openNewTransaction()">+</button>
            </div>

            <div class="draft-list">
                @foreach($drafts as $draft)
                {{-- Saat draft diklik, panggil JS untuk me-load datanya --}}
                <div class="draft-card" onclick="loadDraft({{ $draft->id }})">
                    <div class="draft-top">
                        <h4>{{ $draft->jenis_motor ?? 'Motor' }}</h4>
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
        </div>

        {{-- RIGHT: TRANSACTION PANEL --}}
        <div class="transaction-panel" id="transactionPanel">
            <form action="{{ route('kasir.store') }}" method="POST" id="formTransaksi">
                @csrf
                {{-- Hidden input untuk mendeteksi apakah ini Update Draft atau Buat Baru --}}
                <input type="hidden" name="transaksi_id" id="formTransaksiId">
                
                {{-- INFO TRANSAKSI --}}
                <div class="panel-section">
                    <h3>Informasi Transaksi</h3>
                    <div class="form-grid-3"> <input type="text" name="jenis_motor" id="formJenisMotor" placeholder="Jenis Motor">
                        <select name="metode_pembayaran" id="formMetode">
                            <option value="Tunai">Tunai</option>
                            <option value="Transfer">Transfer</option>
                            <option value="QRIS">QRIS</option>
                        </select>
                        <input type="number" name="biaya_jasa_servis" id="formBiayaJasa" placeholder="Biaya Jasa Servis" value="0" onkeyup="kalkulasiTotal()" onchange="kalkulasiTotal()">
                    </div>
                </div>

                {{-- BARANG STOK --}}
                <div class="panel-section">
                    <h3>Tambah Barang Stok</h3>
                    <div class="form-grid-2">
                        <select id="selectBarangStok">
                            <option value="">Pilih Barang Stok</option>
                            @foreach($barangStok as $barang)
                                <option value="{{ $barang->id }}" data-nama="{{ $barang->nama_barang }}" data-harga="{{ $barang->harga_jual }}">
                                    {{ $barang->nama_barang }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn-blue" onclick="addBarangToTable('selectBarangStok', 'stok')">+ Tambah Stok</button>
                    </div>
                </div>

                {{-- BARANG NON STOK --}}
                <div class="panel-section">
                    <h3>Tambah Barang Non Stok</h3>
                    <div class="form-grid-2">
                        <select id="selectBarangNonStok">
                            <option value="">Pilih Barang Non Stok</option>
                            @foreach($barangNonStok as $barang)
                                <option value="{{ $barang->id }}" data-nama="{{ $barang->nama_barang }}" data-harga="{{ $barang->harga_jual }}">
                                    {{ $barang->nama_barang }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn-blue" onclick="addBarangToTable('selectBarangNonStok', 'non_stok')">+ Tambah Non Stok</button>
                    </div>
                </div>

                {{-- BARANG MANUAL (Auth Check) --}}
                @if(auth()->user()->can_input_manual_barang)
                <div class="panel-section manual-section">
                    <h3>Barang Manual</h3>
                    <div class="form-grid-3 mb-2">
                        <input type="text" id="manNama" placeholder="Nama Barang">
                        <input type="text" id="manSatuan" placeholder="Satuan">
                        <input type="text" id="manSupplier" placeholder="Supplier (Opsional)">
                    </div>
                    <div class="form-grid-3">
                        <input type="number" id="manHargaBeli" placeholder="Harga Beli">
                        <input type="number" id="manHargaJual" placeholder="Harga Jual">
                        <button type="button" class="btn-blue" onclick="addManualToTable()">+ Tambah Manual</button>
                    </div>
                </div>
                @endif

                {{-- TABEL ITEM TRANSAKSI --}}
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

                {{-- SUMMARY --}}
                <div class="summary-section">
                    <input type="hidden" name="total_tagihan" id="inputTotalTagihan" value="0">
                    
                    <div class="summary-row">
                        <span>Total Barang</span>
                        <span id="textTotalBarang">Rp. 0</span>
                    </div>
                    <div class="summary-row">
                        <span>Jasa Servis</span>
                        <span id="textJasaServis">Rp. 0</span>
                    </div>
                    <div class="summary-row">
                        <span>Nominal Dibayar</span>
                        <input type="number" id="inputNominal" class="input-nominal" onkeyup="hitungKembalian()" placeholder="0">
                    </div>
                    <div class="summary-row">
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
                        {{-- Bedakan Value Tombol agar dibaca oleh Controller --}}
                        <button type="submit" name="submit_type" value="draft" class="btn-dark-blue">Simpan Draft</button>
                        <button type="submit" name="submit_type" value="selesai" class="btn-green">Cetak</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- LOGIKA JAVASCRIPT --}}
<script>
    // Tarik data drafts dari Backend ke JavaScript
    const draftsData = @json($drafts);
    let cartIndex = 0;

    // Buka panel kosong untuk transaksi baru
    function openNewTransaction() {
        clearFormAndTable();
        document.getElementById('kasirLayout').classList.add('panel-open');
    }

    // Load data dari draft yang diklik
    function loadDraft(id) {
        clearFormAndTable(); // Kosongkan dulu
        
        let draft = draftsData.find(d => d.id === id);
        if(draft) {
            // Isi Identitas Draft
            document.getElementById('formTransaksiId').value = draft.id;
            document.getElementById('formJenisMotor').value = draft.jenis_motor || '';
            document.getElementById('formMetode').value = draft.metode_pembayaran || 'Tunai';
            document.getElementById('formBiayaJasa').value = draft.biaya_jasa_servis || 0;

            // Load Detail Barang
            if(draft.details && draft.details.length > 0) {
                draft.details.forEach(detail => {
                    renderRowHtml({
                        id: detail.barang_id,
                        nama: detail.barang_id ? (detail.barang ? detail.barang.nama_barang : '-') : detail.nama_barang_manual,
                        harga: parseFloat(detail.harga_jual_satuan),
                        harga_beli: parseFloat(detail.harga_modal),
                        qty: parseInt(detail.jumlah)
                    }, detail.barang_id === null); // Jika barang_id null, berarti manual
                });
            }

            kalkulasiTotal();
            document.getElementById('kasirLayout').classList.add('panel-open');
        }
    }

    // Tambah dari Dropdown
    function addBarangToTable(selectElementId, tipe) {
        let select = document.getElementById(selectElementId);
        if(select.value === "") return alert("Pilih barang terlebih dahulu!");

        let option = select.options[select.selectedIndex];
        renderRowHtml({
            id: option.value,
            nama: option.getAttribute('data-nama'),
            harga: parseFloat(option.getAttribute('data-harga')) || 0,
            harga_beli: 0,
            qty: 1
        }, false);
        
        select.value = "";
    }

    // Tambah Manual
    function addManualToTable() {
        let nama = document.getElementById('manNama').value;
        let satuan = document.getElementById('manSatuan').value;
        let hargaBeli = document.getElementById('manHargaBeli').value;
        let hargaJual = document.getElementById('manHargaJual').value;
        
        if(!nama || !hargaJual) return alert("Nama dan Harga Jual wajib diisi!");

        renderRowHtml({
            id: null, 
            nama: nama + (satuan ? ` (${satuan})` : ''),
            harga: parseFloat(hargaJual),
            harga_beli: parseFloat(hargaBeli) || 0,
            qty: 1
        }, true);

        // Reset inputs manual
        document.getElementById('manNama').value = '';
        document.getElementById('manSatuan').value = '';
        document.getElementById('manSupplier').value = '';
        document.getElementById('manHargaBeli').value = '';
        document.getElementById('manHargaJual').value = '';
    }

    function renderRowHtml(item, isManual) {
        document.getElementById('emptyRow').style.display = 'none';
        
        let sub = item.harga * item.qty;
        let tr = document.createElement('tr');
        tr.id = `row-${cartIndex}`;
        
        let hiddenId = isManual ? '' : `<input type="hidden" name="items[${cartIndex}][barang_id]" value="${item.id}">`;
        
        tr.innerHTML = `
            <td>
                ${item.nama}
                ${hiddenId}
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
        let qty = document.querySelector(`#row-${index} .qty-input`).value;
        let sub = harga * qty;
        let subTd = document.getElementById(`subtotal-text-${index}`);
        subTd.setAttribute('data-subtotal', sub);
        subTd.innerText = `Rp. ${formatRupiah(sub)}`;
        kalkulasiTotal();
    }

    // Kalkulasi Total yang sudah mengakomodir Jasa Servis
    function kalkulasiTotal() {
        // Hitung total barang saja
        let subtotals = document.querySelectorAll('.subtotal-val');
        let totalBarang = 0;
        subtotals.forEach(el => {
            totalBarang += parseFloat(el.getAttribute('data-subtotal'));
        });

        // Ambil Jasa Servis
        let biayaJasa = parseFloat(document.getElementById('formBiayaJasa').value) || 0;
        
        // Total Seluruhnya
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

    // Clear sepenuhnya untuk new transaction
    function clearFormAndTable() {
        document.getElementById('formTransaksi').reset();
        document.getElementById('formTransaksiId').value = ''; // Reset ID
        document.querySelectorAll('tr[id^="row-"]').forEach(tr => tr.remove());
        document.getElementById('emptyRow').style.display = '';
        kalkulasiTotal();
    }

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }
</script>
@endsection