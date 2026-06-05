<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('riwayat_clustering', function (Blueprint $table) {
            $table->id('id_riwayat');
            $table->date('tanggal_proses');
            $table->string('periode');
            $table->string('kode_barang');
            $table->integer('nilai_x_stok');
            $table->integer('nilai_y_terjual');
            $table->enum('label_cluster', ['Laris', 'Sedang', 'Kurang Laris']);
            $table->timestamps();

            // Setup Foreign Key ke tabel barang
            // (Sesuaikan 'barang' dan 'kode_barang' dengan nama tabel/kolom asli kamu)
            $table->foreign('kode_barang')
                  ->references('kode_barang')
                  ->on('barang')
                  ->onDelete('cascade'); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('riwayat_clustering');
    }
};