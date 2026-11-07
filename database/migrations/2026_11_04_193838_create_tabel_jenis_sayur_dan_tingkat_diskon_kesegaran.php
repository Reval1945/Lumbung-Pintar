<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vegetable_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();                  // mis. Sawi
            $table->string('category')->nullable();            // sayur daun, umbi, buah, dll.
            $table->string('default_unit', 20)->default('kg');
            $table->unsignedSmallInteger('fresh_days');        // masa segar, mis. sawi = 3 hari
            $table->string('storage_notes')->nullable();
            $table->string('pihps_commodity_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Diskon bertahap 10-40% setelah masa segar lewat.
        Schema::create('freshness_discount_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vegetable_type_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('days_after_fresh');  // 1 = hari ke-4 untuk sawi
            $table->unsignedTinyInteger('discount_percent');   // 10..40
            $table->enum('label', ['fresh', 'segera_diskon', 'diskon_kesegaran', 'tidak_layak_jual']);
            $table->timestamps();

            // Nama index dibuat manual karena nama otomatis Laravel melebihi batas 64 karakter MySQL.
            $table->unique(['vegetable_type_id', 'days_after_fresh'], 'fdt_sayur_hari_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freshness_discount_tiers');
        Schema::dropIfExists('vegetable_types');
    }
};