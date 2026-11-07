<?php

namespace App\Console\Commands;

use App\Services\FreshnessService;
use Illuminate\Console\Command;

class SegarkanKesegaran extends Command
{
    protected $signature = 'lumbungpintar:segarkan-kesegaran';

    protected $description = 'Hitung ulang status kesegaran dan diskon seluruh tawaran yang tayang';

    public function handle(FreshnessService $freshness): int
    {
        $this->info("Selesai. {$freshness->refreshActive()} tawaran diperbarui.");

        return self::SUCCESS;
    }
}