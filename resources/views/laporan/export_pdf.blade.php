<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan PDF</title>
    <style>
        body { 
            font-family: sans-serif; 
            font-size: 11px; 
            color: #333; 
            line-height: 1.4;
        }
        .header { 
            margin-bottom: 20px; 
            border-bottom: 2px solid #333; 
            padding-bottom: 8px; 
        }
        .header h2 { 
            margin: 0; 
            color: #1E293B; 
            font-size: 18px; 
        }
        .header p { 
            margin: 4px 0 0 0; 
            color: #64748B; 
            font-size: 10px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        th, td { 
            border: 1px solid #CBD5E1; 
            padding: 7px 9px; 
            text-align: left; 
        }
        th { 
            background-color: #F8FAFC; 
            color: #475569; 
            font-weight: bold; 
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { 
            background-color: #F8FAFC; 
            font-weight: bold; 
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan {{ $jenisLaporan === 'penjualan' ? 'Penjualan Keseluruhan' : 'Barang Masuk' }}</h2>
        <p>Diunduh otomatis pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                @if($jenisLaporan === 'penjualan') <th>Invoice</th> @endif
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Subtotal</th>
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
                <td class="text-center">{{ $row->qty }}</td>
                <td class="text-right">Rp {{ number_format($row->harga, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                @if($jenisLaporan === 'penjualan') <td>{{ $row->operator }}</td> @endif
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="{{ $jenisLaporan === 'penjualan' ? '6' : '4' }}" class="text-right">TOTAL KESELURUHAN:</td>
                <td class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</td>
                @if($jenisLaporan === 'penjualan') <td></td> @endif
            </tr>
        </tbody>
    </table>
</body>
</html>