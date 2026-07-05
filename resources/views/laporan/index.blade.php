@extends('layouts.app')

@section('content')
@vite('resources/css/pages/laporan.css')

<div class="laporan-main-container">
    <h1>Laporan</h1>

    {{-- KONTAINER ATAS: FILTER COMPONENT --}}
    <div class="filter-card-container">
        <form action="{{ route('laporan.index') }}" method="GET" id="formFilterLaporan">
            <div class="filter-form-grid">
                
                <div class="filter-group">
                    <label>Jenis Laporan</label>
                    <select name="jenis_laporan" onchange="this.form.submit()">
                        <option value="penjualan" {{ $jenisLaporan === 'penjualan' ? 'selected' : '' }}>Laporan Penjualan Keseluruhan</option>
                        <option value="barang_masuk" {{ $jenisLaporan === 'barang_masuk' ? 'selected' : '' }}>Laporan Barang Masuk</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="tgl_mulai" value="{{ $tglMulai }}" onchange="this.form.submit()">
                </div>

                <div class="filter-group">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="tgl_selesai" value="{{ $tglSelesai }}" onchange="this.form.submit()">
                </div>

                {{-- FILTER TIPE BARANG --}}
                @if($jenisLaporan === 'barang_masuk')
                <div class="filter-group">
                    <label>Tipe Barang</label>
                    <select name="tipe_barang" onchange="this.form.submit()">
                        <option value="all" {{ $tipeBarang === 'all' ? 'selected' : '' }}>Semua Tipe</option>
                        <option value="stok" {{ $tipeBarang === 'stok' ? 'selected' : '' }}>Tipe Stok</option>
                        <option value="non_stok" {{ $tipeBarang === 'non_stok' ? 'selected' : '' }}>Tipe Non-Stok</option>
                    </select>
                </div>
                @endif

                {{-- FILTER DROPDOWN KASIR --}}
                @if($jenisLaporan === 'penjualan')
                <div class="filter-group">
                    <label>Kasir</label>
                    <select name="kasir_id" onchange="this.form.submit()" {{ Auth::user()->role === 'kasir' ? 'disabled' : '' }}>
                        <option value="">Semua Kasir (Gabungan)</option>
                        @foreach($daftarKasir as $ksr)
                            <option value="{{ $ksr->id }}" {{ $kasirId == $ksr->id ? 'selected' : '' }}>
                                {{ $ksr->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

            </div>

            {{-- DOKUMEN DOWNLOAD BAR: Langsung mengunduh tanpa target="_blank" --}}
            <div class="export-button-action-row">
                <a href="{{ route('laporan.export.excel', request()->query()) }}" class="btn-export excel" style="text-decoration: none;">
                    <i class="fa-solid fa-file-excel"></i> Export Excel
                </a>
                <a href="{{ route('laporan.export.pdf', request()->query()) }}" class="btn-export pdf" style="text-decoration: none;">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
            </div>
        </form>
    </div>

    {{-- KONTAINER BAWAH: PREVIEW DATA --}}
    <div class="preview-table-container">
        <div class="table-header-title-section">
            <h3>Pratinjau Data: <span>{{ ucwords(str_replace('_', ' ', $jenisLaporan)) }}</span></h3>
        </div>

        <div class="responsive-table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th class="col-tanggal">Tanggal</th>
                        @if($jenisLaporan === 'penjualan')
                            <th class="col-ref">Invoice</th>
                        @endif
                        <th class="col-kode">Kode Barang</th>
                        <th class="col-nama">Nama Barang</th>
                        <th class="text-center col-qty">Qty</th>
                        <th class="text-right col-harga">Harga</th>
                        <th class="text-right col-subtotal">Subtotal</th>
                        @if($jenisLaporan === 'penjualan')
                            <th class="col-operator">Kasir</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php $totalKeuanganFinal = 0; @endphp
                    @forelse($results as $row)
                        @php $totalKeuanganFinal += $row->total; @endphp
                        <tr>
                            <td>{{ date('d/m/Y H:i', strtotime($row->tanggal)) }}</td>
                            @if($jenisLaporan === 'penjualan')
                                <td><span class="invoice-badge">{{ $row->invoice }}</span></td>
                            @endif
                            <td><span class="code-badge">{{ $row->kode }}</span></td>
                            <td class="bold-text-item-name">{{ $row->nama }}</td>
                            <td class="text-center">{{ $row->qty }}</td>
                            <td class="text-right">Rp {{ number_format($row->harga, 0, ',', '.') }}</td>
                            <td class="text-right bold-text-item-name">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                            @if($jenisLaporan === 'penjualan')
                                <td><span class="operator-badge">{{ $row->operator }}</span></td>
                            @endif
                        </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $jenisLaporan === 'penjualan' ? '8' : '6' }}" class="empty-data-text-row">
                            Tidak ada rekaman data laporan yang ditemukan untuk parameter filter tersebut.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($results) > 0)
                <tfoot>
                    <tr class="footer-summary-row">
                        <td colspan="{{ $jenisLaporan === 'penjualan' ? '6' : '4' }}" class="text-right grand-total-label">
                            TOTAL KESELURUHAN {{ $jenisLaporan === 'barang_masuk' ? 'PENGELUARAN RESTOK' : 'PENDAPATAN KOTOR' }} :
                        </td>
                        <td class="text-right grand-total-money">Rp {{ number_format($totalKeuanganFinal, 0, ',', '.') }}</td>
                        @if($jenisLaporan === 'penjualan')
                            <td></td>
                        @endif
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection