<?php

namespace App\Providers;

use App\Services\Ai\ClaudeProduceAnalyzer;
use App\Services\Ai\Contracts\ProduceAnalyzer;
use App\Services\Ai\GeminiProduceAnalyzer;
use Illuminate\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProduceAnalyzer::class, function () {
            $cfg = config('lumbungpintar.ai');
            $timeout = (int) $cfg['timeout'];

            return match ($cfg['driver']) {
                'claude' => new ClaudeProduceAnalyzer((string) $cfg['claude']['key'], $cfg['claude']['model'], $timeout),
                default => new GeminiProduceAnalyzer((string) $cfg['gemini']['key'], $cfg['gemini']['model'], $timeout),
            };
        });
    }
}