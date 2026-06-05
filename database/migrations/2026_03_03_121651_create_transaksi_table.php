<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('no_invoice')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->string('jenis_motor')->nullable();
            $table->integer('biaya_jasa_servis')->default(0);
            $table->integer('total_harga');
            $table->string('metode_pembayaran');
            $table->enum('status', ['draft', 'selesai', 'dibatalkan'])->default('draft');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaksi');
    }
};