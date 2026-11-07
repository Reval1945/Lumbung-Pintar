<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sumber fitur peramalan LightGBM: PIHPS, kalender menu, cuaca BMKG.
        Schema::create('price_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vegetable_type_id')->constrained()->cascadeOnDelete();
            $table->string('source')->default('PIHPS');
            $table->string('market_name')->nullable();
            $table->string('kabupaten')->default('Bojonegoro');
            $table->date('price_date');
            $table->decimal('price', 12, 2);
            $table->timestamps();

            $table->unique(['vegetable_type_id', 'source', 'market_name', 'price_date'], 'price_ref_unique');
        });

        Schema::create('menu_calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->date('menu_date');
            $table->string('menu_name')->nullable();
            $table->unsignedInteger('portions');
            $table->timestamps();

            $table->unique(['organization_id', 'menu_date', 'menu_name']);
        });

        Schema::create('menu_calendar_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_calendar_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vegetable_type_id')->constrained();
            $table->decimal('qty_per_portion', 8, 4);
            $table->string('unit', 20)->default('kg');
            $table->timestamps();
        });

        Schema::create('weather_observations', function (Blueprint $table) {
            $table->id();
            $table->string('kecamatan');
            $table->string('kabupaten')->default('Bojonegoro');
            $table->date('observed_date');
            $table->decimal('temp_min', 5, 2)->nullable();
            $table->decimal('temp_max', 5, 2)->nullable();
            $table->decimal('rainfall_mm', 7, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();
            $table->boolean('is_forecast')->default(false);
            $table->string('source')->default('BMKG');
            $table->timestamps();

            $table->unique(['kecamatan', 'observed_date', 'is_forecast']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_observations');
        Schema::dropIfExists('menu_calendar_items');
        Schema::dropIfExists('menu_calendars');
        Schema::dropIfExists('price_references');
    }
};