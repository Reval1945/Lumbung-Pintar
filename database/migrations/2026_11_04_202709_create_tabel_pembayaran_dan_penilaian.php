<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Platform TIDAK memegang dana: tabel ini hanya mencatat status/referensi pembayaran.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('method', ['simulasi', 'transfer', 'qris', 'virtual_account', 'cod'])->default('simulasi');
            $table->enum('status', ['menunggu', 'dibayar', 'gagal', 'dikembalikan'])->default('menunggu');
            $table->decimal('amount', 14, 2);
            $table->boolean('is_simulated')->default(true);      // tahap awal: pembayaran simulasi
            $table->string('provider')->nullable();
            $table->string('provider_reference')->nullable();
            $table->string('proof_path')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });

        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rater_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ratee_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('score');                 // 1-5
            $table->unsignedTinyInteger('quality_score')->nullable();
            $table->unsignedTinyInteger('punctuality_score')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'rater_id', 'ratee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
        Schema::dropIfExists('payments');
    }
};