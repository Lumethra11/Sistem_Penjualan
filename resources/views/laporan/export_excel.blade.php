<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <h2>Laporan {{ $jenisLaporan === 'penjualan' ? 'Penjualan Keseluruhan' : 'Barang Masuk' }}</h2>
    <p>Tanggal Unduh: {{ date('d-m-Y H:i') }}</p>
    
    <table border="1">
        <thead>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <th>Tanggal</th>
                @if($jenisLaporan === 'penjualan') <th>Invoice</th> @endif
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
                @if($jenisLaporan === 'penjualan') <th>Kasir</th> @endif
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($results as $row)
            @php $total += $row->total; @endphp
            <tr>
                <td>{{ date('d/m/Y H:i', strtotime($row->tanggal)) }}</td>
                @if($jenisLaporan === 'penjualan') <td>{{ $row->invoice }}</td> @endif
                <td>{{ $row->kode }}</td>
                <td>{{ $row->nama }}</td>
                <td align="center">{{ $row->qty }}</td>
                <td align="right">{{ $row->harga }}</td>
                <td align="right">{{ $row->total }}</td>
                @if($jenisLaporan === 'penjualan') <td>{{ $row->operator }}</td> @endif
            </tr>
            @endforeach
            <tr style="font-weight: bold; background-color: #f9f9f9;">
                <td colspan="{{ $jenisLaporan === 'penjualan' ? '6' : '4' }}" align="right">TOTAL:</td>
                <td align="right">{{ $total }}</td>
                @if($jenisLaporan === 'penjualan') <td></td> @endif
            </tr>
        </tbody>
    </table>
</body>
</html>