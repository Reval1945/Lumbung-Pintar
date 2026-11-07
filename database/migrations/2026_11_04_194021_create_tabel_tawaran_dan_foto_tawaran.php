<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete(); // petani / agen desa
            $table->foreignId('vegetable_type_id')->constrained();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('quantity_available', 10, 2);
            $table->string('unit', 20)->default('kg');
            $table->decimal('min_order_qty', 10, 2)->default(1);
            $table->decimal('base_price', 12, 2);
            $table->enum('category', ['ecer', 'non_ecer'])->default('ecer'); // non_ecer = grosir

            // Masa kesegaran (usia dihitung otomatis dari harvest_date)
            $table->date('harvest_date');
            $table->enum('storage_method', ['suhu_ruang', 'dingin', 'lainnya'])->default('suhu_ruang');
            $table->date('fresh_until')->nullable();
            $table->enum('freshness_status', ['fresh', 'segera_diskon', 'diskon_kesegaran', 'tidak_layak_jual'])
                  ->default('fresh');
            $table->unsignedTinyInteger('current_discount_percent')->default(0);

            $table->enum('grade', ['A', 'B', 'C'])->nullable();   // C = tidak layak jual
            $table->enum('status', [
                'draft', 'menunggu_konfirmasi_petani', 'tayang', 'habis', 'kedaluwarsa', 'ditarik',
            ])->default('draft');
            $table->timestamp('confirmed_by_seller_at')->nullable(); // tawaran tayang setelah dikonfirmasi petani
            $table->enum('created_via', ['manual', 'voice'])->default('manual');

            $table->string('pickup_desa')->nullable();
            $table->decimal('pickup_latitude', 10, 7)->nullable();
            $table->decimal('pickup_longitude', 10, 7)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'vegetable_type_id', 'category']);
            $table->index(['freshness_status', 'fresh_until']);
        });

        // Foto + hasil AI offline (TFLite) + label manusia untuk dataset >=3.000 foto berlabel.
        Schema::create('listing_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->boolean('is_primary')->default(false);

            $table->foreignId('predicted_vegetable_type_id')->nullable()->constrained('vegetable_types')->nullOnDelete();
            $table->decimal('type_confidence', 5, 4)->nullable();
            $table->enum('predicted_grade', ['A', 'B', 'C'])->nullable();
            $table->decimal('grade_confidence', 5, 4)->nullable();
            $table->boolean('is_sellable')->nullable();
            $table->foreignId('ai_model_version_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('captured_offline')->default(false);
            $table->timestamp('synced_at')->nullable();

            $table->foreignId('labeled_vegetable_type_id')->nullable()->constrained('vegetable_types')->nullOnDelete();
            $table->enum('labeled_grade', ['A', 'B', 'C'])->nullable();
            $table->foreignId('labeled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_training_sample')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_photos');
        Schema::dropIfExists('listings');
    }
};