<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Asisten suara: voice note -> tawaran terstruktur. Keputusan tetap di pengguna.
        Schema::create('voice_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('audio_path');
            $table->unsignedSmallInteger('duration_sec')->nullable();
            $table->longText('transcript')->nullable();
            $table->json('parsed_data')->nullable();           // jenis, jumlah, harga, tanggal panen
            $table->enum('price_check_status', ['wajar', 'terlalu_rendah', 'terlalu_tinggi', 'tidak_ada_referensi'])
                  ->nullable();
            $table->decimal('reference_price', 12, 2)->nullable();
            $table->json('suggested_alternatives')->nullable(); // alternatif bila stok kurang
            $table->enum('status', ['diproses', 'menunggu_konfirmasi', 'dikonfirmasi', 'ditolak', 'gagal'])
                  ->default('diproses');
            $table->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_offers');
    }
};