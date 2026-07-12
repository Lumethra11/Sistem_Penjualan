<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kode_barang')->unique();
            $table->string('nama_barang');
            $table->integer('stok')->default(0);
            $table->string('satuan');
            $table->integer('harga_beli');
            $table->integer('harga_jual');
            $table->string('kategori');
            $table->string('supplier')->nullable();
            $table->enum('tipe_barang', ['stok', 'non_stok'])->default('stok');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('barang');
    }
};