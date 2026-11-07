<?php

namespace App\Services;

use App\Enums\FreshnessStatus;
use App\Models\Listing;
use App\Repositories\Contracts\ListingRepositoryInterface;
use Carbon\CarbonInterface;

class FreshnessService
{
    public function __construct(private ListingRepositoryInterface $listings) {}

    /**
     * Hari panen = hari ke-1. Contoh sawi (segar 3 hari): hari 1-2 fresh, hari 3 segera diskon,
     * hari ke-4 dst. mengikuti tabel freshness_discount_tiers.
     *
     * @return array{status: FreshnessStatus, discount: int}
     */
    public function evaluate(Listing $listing, ?CarbonInterface $today = null): array
    {
        if ($listing->grade === 'C') {
            return ['status' => FreshnessStatus::TidakLayakJual, 'discount' => 0];
        }

        $listing->loadMissing('vegetableType.discountTiers');
        $type = $listing->vegetableType;
        $today = ($today ?? today())->copy()->startOfDay();

        $dayNumber = (int) $listing->harvest_date->copy()->startOfDay()->diffInDays($today, false) + 1;

        if ($dayNumber < $type->fresh_days) {
            return ['status' => FreshnessStatus::Fresh, 'discount' => 0];
        }
        if ($dayNumber === $type->fresh_days) {
            return ['status' => FreshnessStatus::SegeraDiskon, 'discount' => 0];
        }

        $daysAfter = $dayNumber - $type->fresh_days;
        $tier = $type->discountTiers
            ->where('days_after_fresh', '<=', $daysAfter)
            ->sortByDesc('days_after_fresh')
            ->first();

        if (! $tier) {
            return ['status' => FreshnessStatus::DiskonKesegaran, 'discount' => 0];
        }

        return ['status' => FreshnessStatus::from($tier->label), 'discount' => (int) $tier->discount_percent];
    }

    /** Hari terakhir masih berstatus segar. */
    public function freshUntil(Listing $listing): \Carbon\CarbonInterface
    {
        $listing->loadMissing('vegetableType');

        return $listing->harvest_date->copy()->addDays($listing->vegetableType->fresh_days - 1);
    }

    /** Hitung ulang status + diskon dan simpan bila berubah. */
    public function sync(Listing $listing): Listing
    {
        $result = $this->evaluate($listing);

        $listing->fill([
            'freshness_status' => $result['status']->value,
            'current_discount_percent' => $result['discount'],
        ]);

        if ($listing->isDirty()) {
            $listing->save();
        }

        return $listing;
    }

    /** Dipakai command harian. Mengembalikan jumlah tawaran yang berubah. */
    public function refreshActive(): int
    {
        $changed = 0;

        $this->listings->chunkActive(200, function ($chunk) use (&$changed) {
            foreach ($chunk as $listing) {
                $this->sync($listing);
                if ($listing->wasChanged()) {
                    $changed++;
                }
            }
        });

        return $changed;
    }
}