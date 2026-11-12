<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class PenggunaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->user('Admin LumbungPintar', '081200000001', 'admin');
        $this->user('Admin Mitra Demo', '081200000002', 'admin_mitra');

        $org = Organization::updateOrCreate(['name' => 'SPPG Demo Bojonegoro'], [
            'type' => 'sppg',
            'kabupaten' => 'Bojonegoro',
            'contact_person' => 'Pengelola SPPG Demo',
            'verified_at' => now(),
            'verified_by' => $admin->id,
        ]);

        $this->user('Petani Demo', '081200000003', 'petani', ['is_small_farmer' => true, 'desa' => 'Desa Demo', 'kecamatan' => 'Kedungadem']);
        $this->user('Agen Desa Demo', '081200000004', 'agen_desa', ['desa' => 'Desa Demo', 'kecamatan' => 'Kedungadem']);
        $this->user('Pembeli SPPG Demo', '081200000005', 'pembeli', ['organization_id' => $org->id]);
    }

    private function user(string $nama, string $hp, string $role, array $extra = []): User
    {
        $user = User::updateOrCreate(['phone' => $hp], [
            'name' => $nama,
            'role' => $role,
            'password' => 'password',        // di-hash otomatis oleh cast "hashed"
            'kabupaten' => 'Bojonegoro',
        ] + $extra);

        // Kolom verifikasi tidak fillable, jadi diisi lewat forceFill.
        $user->forceFill(['verified_at' => now(), 'is_active' => true])->save();

        return $user;
    }
}