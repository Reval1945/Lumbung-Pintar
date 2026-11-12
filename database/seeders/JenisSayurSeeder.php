<?php

namespace Database\Seeders;

use App\Models\VegetableType;
use Illuminate\Database\Seeder;

class JenisSayurSeeder extends Seeder
{
    public function run(): void
    {
        // Hanya sawi (3 hari) yang berasal dari proposal; masa segar lainnya perkiraan awal
        // dan harus divalidasi ke petani pada tahap validasi masalah.
        $sayur = [
            ['Sawi', 'sayur daun', 3],
            ['Bayam', 'sayur daun', 2],
            ['Kangkung', 'sayur daun', 2],
            ['Kol', 'sayur daun', 7],
            ['Wortel', 'umbi', 7],
            ['Tomat', 'buah', 5],
            ['Cabai rawit', 'buah', 4],
            ['Timun', 'buah', 5],
        ];

        // Diskon bertahap 10-40%, lalu tidak layak jual (hari ke-5 setelah masa segar).
        $tiers = [
            ['days_after_fresh' => 1, 'discount_percent' => 10, 'label' => 'diskon_kesegaran'],
            ['days_after_fresh' => 2, 'discount_percent' => 20, 'label' => 'diskon_kesegaran'],
            ['days_after_fresh' => 3, 'discount_percent' => 30, 'label' => 'diskon_kesegaran'],
            ['days_after_fresh' => 4, 'discount_percent' => 40, 'label' => 'diskon_kesegaran'],
            ['days_after_fresh' => 5, 'discount_percent' => 0, 'label' => 'tidak_layak_jual'],
        ];

        foreach ($sayur as [$nama, $kategori, $hariSegar]) {
            $type = VegetableType::updateOrCreate(['name' => $nama], [
                'category' => $kategori,
                'default_unit' => 'kg',
                'fresh_days' => $hariSegar,
                'is_active' => true,
            ]);

            $type->discountTiers()->delete();
            $type->discountTiers()->createMany($tiers);
        }
    }
}