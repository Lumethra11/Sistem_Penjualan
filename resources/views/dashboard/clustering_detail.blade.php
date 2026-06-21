@extends('layouts.app')

@section('content')
@vite('resources/css/pages/dashboard.css')

<div class="dashboard-container">
    
    <div class="top-bar-dashboard">
        <div class="back-nav-title-group">
            <a href="{{ route('dashboard') }}" class="btn-back-dashboard"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
            <h1>Riwayat Perputaran Sparepart</h1>
        </div>
    </div>

    {{-- KONTANIER ATAS: FILTERS CONTROL --}}
    <div class="chart-container-box" style="margin-bottom: 24px; padding: 24px;">
        <form method="GET" action="{{ route('dashboard.clustering_detail') }}" id="formFilterDetailCluster" class="filter-detail-grid-form">
            
            <div class="detail-filter-group">
                <label>Pilih Bulan & Tahun</label>
                <select name="bulan" onchange="document.getElementById('formFilterDetailCluster').submit()">
                    @forelse($opsiPeriodeBulan as $bln)
                        <option value="{{ $bln }}" {{ $bulanFilter === $bln ? 'selected' : '' }}>
                            {{ date('F Y', strtotime($bln . '-01')) }}
                        </option>
                    @empty
                        <option value="{{ date('Y-m') }}">{{ date('F Y') }}</option>
                    @endforelse
                </select>
            </div>

            <div class="detail-filter-group">
                <label>Pilih Urutan Minggu</label>
                <select name="minggu" onchange="document.getElementById('formFilterDetailCluster').submit()">
                    <option value="Minggu 1" {{ $mingguFilter === 'Minggu 1' ? 'selected' : '' }}>Minggu Pertama (W1)</option>
                    <option value="Minggu 2" {{ $mingguFilter === 'Minggu 2' ? 'selected' : '' }}>Minggu Kedua (W2)</option>
                    <option value="Minggu 3" {{ $mingguFilter === 'Minggu 3' ? 'selected' : '' }}>Minggu Ketiga (W3)</option>
                    <option value="Minggu 4" {{ $mingguFilter === 'Minggu 4' ? 'selected' : '' }}>Minggu Keempat (W4)</option>
                    <option value="Minggu 5" {{ $mingguFilter === 'Minggu 5' ? 'selected' : '' }}>Minggu Kelima (W5)</option>
                </select>
            </div>

            <div class="detail-filter-group">
                <label>Saring Kategori Penjualan</label>
                <select name="label_cluster" onchange="document.getElementById('formFilterDetailCluster').submit()">
                    <option value="all" {{ $labelFilter === 'all' ? 'selected' : '' }}>Semua Hasil Analisis</option>
                    <option value="Laris" {{ $labelFilter === 'Laris' ? 'selected' : '' }}>Hanya Barang Sangat Laku</option>
                    <option value="Sedang" {{ $labelFilter === 'Sedang' ? 'selected' : '' }}>Hanya Barang Laku Normal</option>
                    <option value="Kurang Laris" {{ $labelFilter === 'Kurang Laris' ? 'selected' : '' }}>Hanya Barang Kurang Laku</option>
                </select>
            </div>

        </form>
    </div>

    {{-- KONTANIER BAWAH: TABLE RECORDS PREVIEW --}}
    <div class="ranking-container-box" style="padding: 28px;">
        <div class="ranking-header-inline" style="border-bottom: 2px solid #F8FAFC; padding-bottom: 12px; margin-bottom: 20px;">
            <h2 style="font-size: 16px;">Hasil Penilaian Stok: <span style="color: var(--mb-primary); font-weight: 800;">{{ $stringPeriodeTarget }}</span></h2>
            <span class="badge-total-count-items" style="background: #F1F5F9; color: #475569; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700;">{{ $records->count() }} Macam Sparepart</span>
        </div>

        <div style="width: 100%; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; white-space: nowrap;">
                <thead>
                    <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                        <th style="padding: 12px; font-size: 13px; font-weight: 700; color: #475569; text-align: left;">Kode Sparepart</th>
                        <th style="padding: 12px; font-size: 13px; font-weight: 700; color: #475569; text-align: left;">Nama Item Barang</th>
                        <th style="padding: 12px; font-size: 13px; font-weight: 700; color: #475569; text-align: center;">Sisa Stok Gudang</th>
                        <th style="padding: 12px; font-size: 13px; font-weight: 700; color: #475569; text-align: center;">Kuantitas Terjual</th>
                        <th style="padding: 12px; font-size: 13px; font-weight: 700; color: #475569; text-align: center;">Status Perputaran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $r)
                        <tr style="border-bottom: 1px solid #F1F5F9;">
                            <td style="padding: 14px 12px; font-family: monospace; font-weight: 600; color: #2563EB;">{{ $r->kode_barang }}</td>
                            <td style="padding: 14px 12px; font-weight: 600; color: #1E293B;">{{ $r->nama_barang_live }}</td>
                            <td style="padding: 14px 12px; text-align: center; font-weight: 700;">{{ $r->nilai_x_stok }} Item</td>
                            <td style="padding: 14px 12px; text-align: center; font-weight: 700; color: #10B981;">{{ $r->nilai_y_terjual }} Pcs</td>
                            <td style="padding: 14px 12px; text-align: center;">
                                <span class="badge-cluster {{ Str::slug($r->label_cluster) }}" style="display: inline-block;">{{ $r->label_cluster }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #94A3B8; padding: 40px 0; font-style: italic;">
                                Belum ada rekap data perputaran barang yang dikunci pada periode filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection