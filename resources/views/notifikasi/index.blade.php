@extends('layouts.app')

@section('content')
{{-- Memanggil file CSS Notifikasi secara mutlak --}}
@vite('resources/css/notifikasi.css')

<div class="notifikasi-main-wrapper">
    
    <div class="notif-page-header">
        <h1>Pusat Notifikasi & Evaluasi Sistem</h1>
        <p>Log pemantauan kondisi stok harian dan hasil analisis perputaran barang periode minggu ini.</p>
    </div>

    <div class="notif-table-container">
        <table class="notif-table-flat">
            <thead>
                <tr>
                    <th width="6%" style="text-align: center;">Kategori</th>
                    <th width="12%">Waktu</th>
                    <th width="22%">Tipe Notifikasi</th>
                    <th width="48%">Isi Detail Pesan Evaluasi</th>
                    <th width="12%" style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groupedNotifications as $tanggal => $items)
                    {{-- SEPARATOR PEMISAH TANGGAL (FIX COLSPAN) --}}
                    <tr class="notif-date-divider-row">
                        <td colspan="5">
                            <div class="date-divider-content">
                                <i class="fa-regular fa-calendar-days"></i>
                                <span>{{ $tanggal }}</span>
                            </div>
                        </td>
                    </tr>

                    @foreach($items as $notif)
                        <tr class="page-row-item row-type-{{ $notif['tipe'] }}" data-id="{{ $notif['id_item'] }}">
                            <td style="text-align: center;">
                                <div class="table-icon-badge badge-{{ $notif['tipe'] }}">
                                    <i class="fa-solid {{ $notif['icon'] }}"></i>
                                </div>
                            </td>
                            <td class="col-date">{{ $notif['waktu'] }} WIB</td>
                            <td>
                                <span class="text-label-type label-{{ $notif['tipe'] }}">{{ $notif['judul'] }}</span>
                            </td>
                            <td>
                                <div class="col-message-box">
                                    <strong>{{ $notif['nama'] }}</strong> <small class="text-monospace">({{ $notif['kode'] }})</small>
                                    <p>{{ $notif['pesan'] }}</p>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <button class="btn-table-mark-read" data-id="{{ $notif['id_item'] }}">
                                    <i class="fa-solid fa-check"></i> Selesai
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="5" class="table-empty-state" style="text-align: center; padding: 40px; color: #94a3b8;">
                            <i class="fa-regular fa-bell-slash" style="font-size: 24px; margin-bottom: 8px; display: block;"></i>
                            <p>Tidak ada data log notifikasi atau rekomendasi sistem saat ini.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- CONTAINER PAGINATION --}}
        @if($allNotifications->hasPages())
            <div class="notif-table-pagination-footer">
                {{ $allNotifications->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let readItems = JSON.parse(localStorage.getItem('read_notif_ids') || "[]");

        readItems.forEach(id => {
            const targetRow = document.querySelector(`.page-row-item[data-id="${id}"]`);
            if(targetRow) {
                targetRow.classList.add('table-row-dimmed');
                const btn = targetRow.querySelector('.btn-table-mark-read');
                if(btn) { 
                    btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Dibaca'; 
                    btn.disabled = true; 
                }
            }
        });

        document.querySelectorAll('.btn-table-mark-read').forEach(btn => {
            btn.addEventListener('click', function() {
                let id = this.getAttribute('data-id');
                let currentRead = JSON.parse(localStorage.getItem('read_notif_ids') || "[]");
                
                if(!currentRead.includes(id)) {
                    currentRead.push(id);
                    localStorage.setItem('read_notif_ids', JSON.stringify(currentRead));
                }

                const parentRow = this.closest('.page-row-item');
                parentRow.classList.add('table-row-dimmed');
                this.innerHTML = '<i class="fa-solid fa-circle-check"></i> Dibaca';
                this.disabled = true;
                
                document.getElementById('pop-' + id)?.remove();
            });
        });
    });
</script>
@endsection