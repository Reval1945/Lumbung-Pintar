<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_model_versions', function (Blueprint $table) {
            $table->id();
            $table->enum('model_name', [
                'vegetable_classifier', 'grade_classifier', 'demand_forecaster',
                'matcher', 'route_optimizer', 'voice_assistant',
            ]);
            $table->string('version', 30);
            $table->string('framework')->nullable();           // TensorFlow Lite, LightGBM, OR-Tools
            $table->string('license')->nullable();             // MIT / Apache 2.0 (kepatuhan Pakta Integritas no.6)
            $table->string('artifact_path')->nullable();
            $table->timestamp('trained_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('metrics')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['model_name', 'version']);
        });

        // KPI proposal: akurasi jenis >=90%, grade >=85%, MAPE <=20%, pemenuhan >=85%, dst.
        Schema::create('ai_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_model_version_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('metric', [
                'akurasi_jenis', 'akurasi_grade', 'mape', 'pemenuhan_pesanan',
                'penurunan_jarak_tempuh', 'kenaikan_harga_petani', 'penurunan_penolakan_panen',
            ]);
            $table->decimal('value', 8, 4);
            $table->decimal('target', 8, 4)->nullable();
            $table->unsignedInteger('sample_size')->nullable();
            $table->string('phase')->nullable();               // pilot-1, pilot-2, ...
            $table->timestamp('evaluated_at')->useCurrent();
            $table->timestamps();

            $table->index(['metric', 'evaluated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_evaluations');
        Schema::dropIfExists('ai_model_versions');
    }
};