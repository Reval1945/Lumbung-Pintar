<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pencocokan adil: satu kebutuhan dibagi ke banyak petani dengan kuota petani kecil.
        Schema::create('demand_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_demand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('farmer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('planting_calendar_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('allocated_qty', 10, 2);
            $table->boolean('small_farmer_quota_applied')->default(false);
            $table->decimal('fairness_score', 6, 4)->nullable();
            $table->enum('matched_by', ['ai', 'manual'])->default('ai');
            $table->enum('status', ['usulan', 'diterima_petani', 'ditolak_petani', 'terpenuhi'])->default('usulan');
            $table->foreignId('ai_model_version_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['buyer_demand_id', 'farmer_id', 'listing_id']);
        });

        // Rute pengambilan (Google OR-Tools). baseline_distance_km untuk KPI jarak turun >=20%.
        Schema::create('pickup_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('route_date');
            $table->decimal('total_distance_km', 8, 2)->nullable();
            $table->decimal('baseline_distance_km', 8, 2)->nullable();
            $table->unsignedSmallInteger('estimated_duration_min')->nullable();
            $table->enum('status', ['direncanakan', 'berjalan', 'selesai', 'dibatalkan'])->default('direncanakan');
            $table->foreignId('ai_model_version_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('pickup_route_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pickup_route_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sequence');
            $table->foreignId('farmer_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('order_id')->nullable();  // FK ditambahkan setelah tabel orders ada
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('planned_qty', 10, 2)->nullable();
            $table->timestamp('planned_arrival')->nullable();
            $table->timestamp('actual_arrival')->nullable();
            $table->enum('status', ['menunggu', 'diambil', 'dilewati'])->default('menunggu');
            $table->timestamps();

            $table->unique(['pickup_route_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_route_stops');
        Schema::dropIfExists('pickup_routes');
        Schema::dropIfExists('demand_matches');
    }
};