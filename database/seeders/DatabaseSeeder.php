<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Memanggil TestingKMeansSeeder agar otomatis berjalan saat db:seed dijalankan
        $this->call([
            TestingKMeansSeeder::class,
        ]);
    }
}