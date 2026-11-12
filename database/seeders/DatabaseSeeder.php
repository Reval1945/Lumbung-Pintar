<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(JenisSayurSeeder::class);

        // Akun demo dengan kata sandi "password": jangan dibuat di produksi.
        if (! app()->environment('production')) {
            $this->call(PenggunaDemoSeeder::class);
        }
    }
}