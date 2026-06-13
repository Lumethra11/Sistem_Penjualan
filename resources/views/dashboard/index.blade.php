@extends('layouts.app')

@section('content')
@vite('resources/css/pages/dashboard.css')

<div class="dashboard-container">
    
    <div class="top-bar-dashboard">
        <h1>Dashboard</h1>
        
        <form method="GET" action="{{ route('dashboard') }}" id="formFilterWaktu" class="filter-select-wrapper">
            <select name="filter" onchange="document.getElementById('formFilterWaktu').submit();">
                <option value="hari" {{ $filter === 'hari' ? 'selected' : '' }}>Hari Ini</option>
                <option value="minggu" {{ $filter === 'minggu' ? 'selected' : '' }}>Minggu Ini</option>
                <option value="bulan" {{ $filter === 'bulan' ? 'selected' : '' }}>Bulan Ini</option>
            </select>
        </form>
    </div>

    <div class="dashboard-cards-grid">
        
        <div class="db-metric-card">
            <div class="card-text-block">
                <h3>Total Barang</h3>
                <p>{{ number_format($totalBarang, 0, ',', '.') }}</p>
            </div>
            <div class="card-icon-block c-blue">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m16 0v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
        </div>

        <div class="db-metric-card clickable" onclick="openModalBox('pemasukanModal')" title="Klik untuk rincian pemasukan">
            <div class="card-text-block">
                <h3>Pemasukan</h3>
                <p>Rp. {{ number_format($pemasukan, 0, ',', '.') }}</p>
            </div>
            <div class="card-icon-block c-green">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
        </div>

        <div class="db-metric-card clickable" onclick="openModalBox('pengeluaranModal')" title="Klik untuk rincian pengeluaran">
            <div class="card-text-block">
                <h3>Pengeluaran</h3>
                <p>Rp. {{ number_format($pengeluaran, 0, ',', '.') }}</p>
            </div>
            <div class="card-icon-block c-orange">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
            </div>
        </div>

        <div class="db-metric-card">
            <div class="card-text-block">
                <h3>Stok Rendah</h3>
                <p>{{ number_format($stokRendah, 0, ',', '.') }}</p>
            </div>
            <div class="card-icon-block c-red">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
        </div>

    </div>

    <div class="lower-content-grid">
        <div class="chart-container-box">
            <h2>Grafik Aktivitas Keuangan Bisnis</h2>
            <div style="position: relative; width: 100%; height: 320px;">
                <canvas id="mainFinanceChart"></canvas>
            </div>
        </div>

        <div class="ranking-container-box">
            <h2>Analisis K-Means</h2>
            <div style="text-align: center; color: #94A3B8; padding: 50px 0; font-size: 13px;">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin: 0 auto 10px; display: block; color: #cbd5e1;"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path></svg>
                Menunggu klasterisasi algoritma...
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="pemasukanModal">
    <div class="modal-content" style="max-width: 750px;">
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
                            <td style="font-weight: 600;">{{ $b->nama }}</td>
                            <td>{{ $b->total_qty }}</td>
                            <td>Rp. {{ number_format($b->harga_beli_satuan, 0, ',', '.') }}</td>
                            <td>Rp. {{ number_format($b->total_jual / $b->total_qty, 0, ',', '.') }}</td>
                            <td style="color: var(--mb-success); font-weight: 600;">Rp. {{ number_format($b->keuntungan, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align: center; color: #64748B;">Tidak ada transaksi produk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-title-divider" style="margin-top: 24px;">Daftar Layanan Jasa Servis</div>
        <div class="modal-table-holder">
            <table>
                <thead>
                    <tr>
                        <th>Deskripsi Unit Kendaraan</th>
                        <th>Nominal Biaya Jasa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detailJasaPemasukan as $j)
                        <tr>
                            <td>Layanan Servis Unit Motor {{ $j->jenis_motor ?? 'Umum' }}</td>
                            <td style="font-weight: 600; color: var(--mb-text);">Rp. {{ number_format($j->nominal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" style="text-align: center; color: #64748B;">Tidak ada transaksi layanan jasa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="pengeluaranModal">
    <div class="modal-content" style="max-width: 650px;">
        <div class="modal-header">
            <h2>Rincian Pengeluaran Modal Produk</h2>
            <button class="close-modal-btn" onclick="closeModalBox('pengeluaranModal')">&times;</button>
        </div>

        <div class="modal-table-holder" style="margin-top: 0;">
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
                            <td style="font-weight: 600;">{{ $p->nama }}</td>
                            <td>Rp. {{ number_format($p->harga_beli_satuan, 0, ',', '.') }}</td>
                            <td>{{ $p->total_qty }}</td>
                            <td style="color: var(--mb-danger); font-weight: 600;">Rp. {{ number_format($p->total_modal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align: center; color: #64748B;">Tidak ada beban modal pengeluaran.</td></tr>
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
                // FIXED LUAR BIASA: Label Sumbu X dicetak dinamis menyesuaikan data dari PHP Controller
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