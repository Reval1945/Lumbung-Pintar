<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Langganan dasbor (model pendapatan selain biaya layanan 2-3%).
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->enum('plan', ['dasar', 'pro', 'institusi'])->default('dasar');
            $table->decimal('price', 12, 2)->default(0);
            $table->enum('billing_cycle', ['bulanan', 'tahunan'])->default('bulanan');
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->enum('status', ['aktif', 'berakhir', 'dibatalkan'])->default('aktif');
            $table->timestamps();

            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};