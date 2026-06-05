<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->enum('role', ['admin', 'kasir'])
                ->default('kasir');
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('nama_toko')->nullable();
            $table->string('no_telp')->nullable();
            $table->string('foto_profil')->nullable();
            $table->text('alamat')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('can_input_manual_barang')->default(false);
            $table->string('password');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};