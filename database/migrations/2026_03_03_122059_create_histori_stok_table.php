<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('histori_stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barang')->onDelete('cascade');
            $table->enum('jenis_pergerakan', ['masuk', 'keluar', 'penyesuaian']);
            $table->integer('jumlah');
            $table->integer('sisa_stok_saat_ini'); // Snapshot stok agar mudah dilacak
            $table->string('keterangan')->nullable(); // Misal: "Restock dari supplier", "Penjualan INV-001"
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('histori_stok');
    }
};