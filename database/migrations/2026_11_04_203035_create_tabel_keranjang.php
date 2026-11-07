<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keranjang: penyimpanan sementara sebelum checkout.
        // "Beli langsung" tidak memakai tabel ini; pesanan langsung dibuat di orders/order_items.
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();     // pembeli
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->timestamps();

            // Barang yang sama tidak boleh dobel; menambah lagi cukup menaikkan quantity.
            $table->unique(['user_id', 'listing_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};