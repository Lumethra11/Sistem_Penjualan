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
                    <th width="15%">Waktu & Tanggal</th>
                    <th width="22%">Tipe Notifikasi</th>
                    <th width="45%">Isi Detail Pesan Evaluasi</th>
                    <th width="12%" style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groupedNotifications as $tanggal => $items)
                    {{-- SEPARATOR PEMISAH TANGGAL --}}
                    <tr class="notif-date-divider-row">
                        <td colspan="5">
                            <div class="date-divider-content">
                                <i class="fa-regular fa-calendar-days"></i>
                                <span>{{ $tanggal }}</span>
                            </div>
                        </td>
                    </tr>

                    @foreach($items as $notif)
                        <tr class="page-row-item row-type-{{ $notif->tipe }} {{ $notif->is_read ? 'table-row-dimmed' : '' }}" data-id="{{ $notif->id_item }}">
                            <td style="text-align: center;">
                                <div class="table-icon-badge badge-{{ $notif->tipe }}">
                                    <i class="fa-solid {{ $notif->icon }}"></i>
                                </div>
                            </td>
                            <td class="col-date">
                                <div>{{ \Carbon\Carbon::parse($notif->created_at)->format('H:i') }} WIB</div>
                                <small style="color:#94a3b8; font-size:11px;">{{ \Carbon\Carbon::parse($notif->created_at)->translatedFormat('l') }}</small>
                            </td>
                            <td>
                                <span class="text-label-type label-{{ $notif->tipe }}">{{ $notif->judul }}</span>
                            </td>
                            <td>
                                <div class="col-message-box">
                                    <p>{{ $notif->pesan }}</p>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <button class="btn-table-mark-read" data-id="{{ $notif->id_item }}" {{ $notif->is_read ? 'disabled' : '' }}>
                                    @if($notif->is_read)
                                        <i class="fa-solid fa-circle-check"></i> Dibaca
                                    @else
                                        <i class="fa-solid fa-check"></i> Selesai
                                    @endif
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
        document.querySelectorAll('.btn-table-mark-read').forEach(btn => {
            btn.addEventListener('click', function() {
                let id = this.getAttribute('data-id');
                let token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                
                fetch(`/notifikasi/mark-read/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    }
                })
                .then(response => response.json())
                .then(res => {
                    if(res.status === 'success') {
                        const parentRow = this.closest('.page-row-item');
                        parentRow.classList.add('table-row-dimmed');
                        this.innerHTML = '<i class="fa-solid fa-circle-check"></i> Dibaca';
                        this.disabled = true;
                        
                        // Menghapus elemen popup navbar jika sedang terbuka
                        document.getElementById('pop-' + id)?.remove();
                    }
                })
                .catch(err => console.error("Gagal memperbarui database status baca:", err));
            });
        });
    });
</script>
@endsection