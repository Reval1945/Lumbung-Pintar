<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pesanan dengan lima status.
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('buyer_id')->constrained('users');
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('buyer_demand_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['menunggu_konfirmasi', 'dikonfirmasi', 'diproses', 'dikirim', 'selesai'])
                  ->default('menunggu_konfirmasi');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('service_fee_percent', 4, 2)->default(2.00);   // biaya layanan 2-3%
            $table->decimal('service_fee_amount', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->text('delivery_address')->nullable();
            $table->date('needed_date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamps();

            $table->index(['buyer_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained();
            $table->foreignId('seller_id')->constrained('users');
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 20)->default('kg');
            $table->decimal('unit_price', 12, 2);
            $table->unsignedTinyInteger('discount_percent')->default(0);
            $table->decimal('subtotal', 14, 2);
            $table->enum('grade_at_order', ['A', 'B', 'C'])->nullable();
            $table->enum('freshness_at_order', ['fresh', 'segera_diskon', 'diskon_kesegaran', 'tidak_layak_jual'])->nullable();
            $table->decimal('rejected_qty', 10, 2)->default(0);   // KPI penolakan panen
            $table->string('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'order_id']);
        });

        Schema::table('pickup_route_stops', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pickup_route_stops', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};