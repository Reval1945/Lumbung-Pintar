<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pembeli institusional (SPPG, katering, koperasi/KDMP, UMKM olahan) dan kelompok tani.
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['sppg', 'katering', 'koperasi_kdmp', 'umkm_olahan', 'kelompok_tani']);
            $table->text('address')->nullable();
            $table->string('desa')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kabupaten')->default('Bojonegoro');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('contact_person')->nullable();
            $table->timestamp('verified_at')->nullable();      // diverifikasi admin mitra
            // FK ke users ditambahkan di migration buat_tabel_users (users dibuat setelah tabel ini)
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'kabupaten']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};