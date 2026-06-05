@extends('layouts.app')

@section('content')

@vite('resources/css/pages/kasir.css')

{{-- Jika user baru saja mengklik "Cetak" di Kasir, maka trigger Pop-Up Struk Otomatis --}}
@if(session('cetak_nota'))
<script>
    // Membuka nota di tab baru
    window.open("{{ route('kasir.nota', session('cetak_nota')) }}", "_blank");
</script>
@endif

<div class="kasir-container">
    <div class="kasir-header">
        <h1>Kasir</h1>
        <div class="tab-container">
            <a href="{{ route('kasir.transaksi') }}" class="tab-button">Transaksi</a>
            <a href="{{ route('kasir.riwayat') }}" class="tab-button active">History</a>
        </div>
    </div>

    <div class="table-container">
        <h3>Riwayat Transaksi</h3>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Tanggal</th>
                    <th>Kendaraan</th>
                    <th>Pembayaran</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($riwayat as $item)
                <tr>
                    <td>{{ $item->no_invoice }}</td>
                    <td>{{ $item->created_at->format('d F Y - H:i') }}</td>
                    <td>{{ $item->jenis_motor ?? '-' }}</td>
                    <td>{{ $item->metode_pembayaran }}</td>
                    <td>Rp. {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                    <td>
                        {{-- Tombol untuk download/print nota ulang --}}
                        <a href="{{ route('kasir.nota', $item->id) }}" target="_blank" class="btn-print-nota">
                            Cetak Nota
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper">
            {{ $riwayat->links() }}
        </div>
    </div>
</div>

@endsection