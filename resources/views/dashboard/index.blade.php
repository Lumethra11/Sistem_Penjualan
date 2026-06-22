@extends('layouts.app')

@section('content')
@vite('resources/css/pages/dashboard.css')

<div class="dashboard-container">
    
    <div class="top-bar-dashboard">
        <h1>Dashboard Analisis</h1>
        
        <form method="GET" action="{{ route('dashboard') }}" id="formFilterWaktu" class="filter-select-wrapper">
            <select name="filter" onchange="document.getElementById('formFilterWaktu').submit();">
                <option value="hari" {{ $filter === 'hari' ? 'selected' : '' }}>Hari Ini</option>
                <option value="minggu" {{ $filter === 'minggu' ? 'selected' : '' }}>Minggu Ini</option>
                <option value="bulan" {{ $filter === 'bulan' ? 'selected' : '' }}>Bulan Ini</option>
            </select>
        </form>
    </div>

    {{-- METRIC CARDS GRID --}}
    <div class="dashboard-cards-grid">
        <div class="db-metric-card">
            <div class="card-text-block">
                <h3>Total Ragam Barang</h3>
                <p>{{ number_format($totalBarang, 0, ',', '.') }}</p>
            </div>
            <div class="card-icon-block c-blue">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m16 0v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
        </div>

        <div class="db-metric-card clickable" onclick="openModalBox('pemasukanModal')">
            <div class="card-text-block">
                <h3>Pemasukan</h3>
                <p>Rp. {{ number_format($pemasukan, 0, ',', '.') }}</p>
            </div>
            <div class="card-icon-block c-green">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
        </div>

        <div class="db-metric-card clickable" onclick="openModalBox('pengeluaranModal')">
            <div class="card-text-block">
                <h3>Pengeluaran (Modal)</h3>
                <p>Rp. {{ number_format($pengeluaran, 0, ',', '.') }}</p>
            </div>
            <div class="card-icon-block c-orange">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
            </div>
        </div>

        <div class="db-metric-card">
            <div class="card-text-block">
                <h3>Stok Hampir Habis</h3>
                <p>{{ number_format($stokRendah, 0, ',', '.') }}</p>
            </div>
            <div class="card-icon-block c-red">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
        </div>
    </div>

    <div class="lower-content-grid">
        {{-- GRAFIK KEUANGAN --}}
        <div class="chart-container-box">
            <h2>Grafik Aktivitas Keuangan Bisnis</h2>
            <div class="canvas-chart-holder">
                <canvas id="mainFinanceChart"></canvas>
            </div>
        </div>

        {{-- RANKING PANEL PREVIEW --}}
        <div class="ranking-container-box clickable-panel" onclick="window.location.href='{{ route('dashboard.clustering_detail') }}'" title="Klik untuk membuka riwayat analisis perputaran lengkap">
            <div class="ranking-header-inline">
                <h2>Ranking Penjualan Teratas (Minggu Ini)</h2>
                <span class="view-all-link-badge">Detail Riwayat <i class="fa-solid fa-arrow-right"></i></span>
            </div>

            <div class="ranking-scroll-list-holder">
                @forelse($barangTerlaris as $index => $bt)
                    <div class="ranking-item-card">
                        <div class="ranking-number-badge">{{ $index + 1 }}</div>
                        <div class="ranking-item-meta">
                            <h4>{{ $bt['nama'] }}</h4>
                            <small>Kode: {{ $bt['kode'] }} | Sisa Stok: {{ $bt['stok'] }}</small>
                        </div>
                        <div class="ranking-item-metric">
                            <span class="badge-cluster {{ Str::slug($bt['label']) }}">{{ $bt['label'] }}</span>
                            <p>{{ $bt['terjual'] }} Terjual</p>
                        </div>
                    </div>
                @empty
                    <div class="empty-kmeans-box">Tidak ada rekaman transaksi penjualan barang pada minggu ini.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- MODAL BOX DETAILS PEMASUKAN --}}
<div class="modal-overlay" id="pemasukanModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Rincian Pendapatan Komprehensif</h2>
            <button class="close-modal-btn" onclick="closeModalBox('pemasukanModal')">&times;</button>
        </div>
        
        <div class="modal-summary-strip">
            <div class="summary-badge">
                <small>Pendapatan Produk</small>
                <span>Rp. {{ number_format($pendapatanBarang, 0, ',', '.') }}</span>
            </div>
            <div class="summary-badge">
                <small>Pendapatan Jasa Servis</small>
                <span>Rp. {{ number_format($pendapatanJasa, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="table-title-divider">Daftar Produk / Sparepart Terjual</div>
        <div class="modal-table-holder">
            <table>
                <thead>
                    <tr>
                        <th>Nama Item</th>
                        <th>Qty</th>
                        <th>Harga Modal (Satuan)</th>
                        <th>Harga Jual (Rata-rata)</th>
                        <th>Keuntungan (Profit)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detailBarangPemasukan as $b)
                        <tr>
                            <td class="bold-row-item-title">{{ $b->nama }}</td>
                            <td>{{ $b->total_qty }}</td>
                            <td>Rp. {{ number_format($b->harga_beli_satuan, 0, ',', '.') }}</td>
                            <td>Rp. {{ number_format($b->total_qty > 0 ? ($b->total_jual / $b->total_qty) : 0, 0, ',', '.') }}</td>
                            <td class="profit-text-green">Rp. {{ number_format($b->keuntungan, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center-empty">Tidak ada transaksi produk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL PENGELUARAN --}}
<div class="modal-overlay" id="pengeluaranModal">
    <div class="modal-content max-width-pengeluaran">
        <div class="modal-header">
            <h2>Rincian Pengeluaran Modal Produk</h2>
            <button class="close-modal-btn" onclick="closeModalBox('pengeluaranModal')">&times;</button>
        </div>
        <div class="modal-table-holder margin-top-zero">
            <table>
                <thead>
                    <tr>
                        <th>Nama Item Sparepart</th>
                        <th>Harga Modal Satuan</th>
                        <th>Jumlah Terjual</th>
                        <th>Total Beban Modal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detailBarangPemasukan as $p)
                        <tr>
                            <td class="bold-row-item-title">{{ $p->nama }}</td>
                            <td>Rp. {{ number_format($p->harga_beli_satuan, 0, ',', '.') }}</td>
                            <td>{{ $p->total_qty }}</td>
                            <td class="loss-text-red">Rp. {{ number_format($p->total_modal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center-empty">Tidak ada beban modal pengeluaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function openModalBox(id) { document.getElementById(id).classList.add('active'); }
    function closeModalBox(id) { document.getElementById(id).classList.remove('active'); }

    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('mainFinanceChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: @json($chartPemasukan),
                        borderColor: '#22C55E', 
                        backgroundColor: 'rgba(34, 197, 94, 0.04)',
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#22C55E',
                        pointRadius: 4
                    },
                    {
                        label: 'Pengeluaran',
                        data: @json($chartPengeluaran),
                        borderColor: '#EF4444', 
                        backgroundColor: 'rgba(239, 68, 68, 0.04)',
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#EF4444',
                        pointRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { font: { family: 'Inter', size: 12 } } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { family: 'Inter', size: 11 },
                            callback: value => 'Rp ' + new Intl.NumberFormat('id-ID').format(value)
                        }
                    },
                    x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 11 } } }
                }
            }
        });
    });
</script>
@endsection