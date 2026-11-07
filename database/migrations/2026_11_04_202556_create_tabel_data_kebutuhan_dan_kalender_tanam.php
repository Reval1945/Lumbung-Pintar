<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kebutuhan pembeli institusional ("pasar dulu, tanam kemudian").
        Schema::create('buyer_demands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('vegetable_type_id')->constrained();
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 20)->default('kg');
            $table->date('needed_date');
            $table->enum('frequency', ['sekali', 'harian', 'mingguan', 'bulanan'])->default('sekali');
            $table->enum('min_grade', ['A', 'B', 'C'])->default('B');
            $table->decimal('max_price', 12, 2)->nullable();
            $table->enum('source', ['manual', 'kalender_menu'])->default('manual');
            $table->enum('status', ['terbuka', 'sebagian_terpenuhi', 'terpenuhi', 'dibatalkan'])->default('terbuka');
            $table->timestamps();

            $table->index(['status', 'needed_date']);
        });

        // Hasil prediksi kebutuhan (LightGBM); MAPE dihitung setelah aktual diketahui.
        Schema::create('demand_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vegetable_type_id')->constrained();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kabupaten')->default('Bojonegoro');
            $table->date('forecast_date');
            $table->decimal('predicted_qty', 12, 2);
            $table->decimal('lower_bound', 12, 2)->nullable();
            $table->decimal('upper_bound', 12, 2)->nullable();
            $table->string('unit', 20)->default('kg');
            $table->decimal('actual_qty', 12, 2)->nullable();
            $table->decimal('ape', 8, 4)->nullable();           // absolute percentage error
            $table->foreignId('ai_model_version_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['vegetable_type_id', 'forecast_date']);
        });

        // Agregasi panen beberapa kelompok tani.
        Schema::create('harvest_aggregations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete(); // koperasi/kelompok
            $table->foreignId('vegetable_type_id')->constrained();
            $table->date('harvest_week_start');
            $table->decimal('total_expected_qty', 12, 2)->default(0);
            $table->string('unit', 20)->default('kg');
            $table->timestamps();

            $table->unique(['organization_id', 'vegetable_type_id', 'harvest_week_start'], 'harvest_agg_unique');
        });

        // Kalender tanam-panen per petani.
        Schema::create('planting_calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vegetable_type_id')->constrained();
            $table->foreignId('demand_forecast_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('harvest_aggregation_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('land_area_m2', 10, 2)->nullable();
            $table->date('plant_date');
            $table->date('expected_harvest_start');
            $table->date('expected_harvest_end')->nullable();
            $table->decimal('expected_qty', 10, 2)->nullable();
            $table->date('actual_harvest_date')->nullable();
            $table->decimal('actual_qty', 10, 2)->nullable();
            $table->enum('status', ['direncanakan', 'ditanam', 'panen', 'selesai', 'dibatalkan'])->default('direncanakan');
            $table->timestamps();

            $table->index(['farmer_id', 'expected_harvest_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planting_calendars');
        Schema::dropIfExists('harvest_aggregations');
        Schema::dropIfExists('demand_forecasts');
        Schema::dropIfExists('buyer_demands');
    }
};