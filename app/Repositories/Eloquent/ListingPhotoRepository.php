<?php

namespace App\Repositories\Eloquent;

use App\Models\AiModelVersion;
use App\Models\ListingPhoto;
use App\Repositories\Contracts\ListingPhotoRepositoryInterface;

class ListingPhotoRepository extends BaseRepository implements ListingPhotoRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new ListingPhoto());
    }

    public function findForListingOrFail(int $listingId, int $photoId): ListingPhoto
    {
        return $this->model->newQuery()->where('listing_id', $listingId)->findOrFail($photoId);
    }

    public function firstOfListing(int $listingId): ?ListingPhoto
    {
        return $this->model->newQuery()->where('listing_id', $listingId)->orderBy('id')->first();
    }

    public function unsetPrimary(int $listingId, int $exceptId): void
    {
        $this->model->newQuery()
            ->where('listing_id', $listingId)->where('id', '!=', $exceptId)
            ->update(['is_primary' => false]);
    }

    /** Satu catatan versi per label model; dipakai KPI di ai_evaluations. */
    public function resolveModelVersion(string $label, string $framework): int
    {
        return AiModelVersion::firstOrCreate(
            ['model_name' => 'grade_classifier', 'version' => mb_substr($label, 0, 30)],
            ['framework' => $framework, 'is_active' => true, 'notes' => 'Penilaian jenis dan grade dari foto']
        )->id;
    }
}