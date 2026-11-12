<?php

namespace App\Repositories\Contracts;

use App\Models\ListingPhoto;

interface ListingPhotoRepositoryInterface extends BaseRepositoryInterface
{
    public function findForListingOrFail(int $listingId, int $photoId): ListingPhoto;
    public function firstOfListing(int $listingId): ?ListingPhoto;
    public function unsetPrimary(int $listingId, int $exceptId): void;
    public function resolveModelVersion(string $label, string $framework): int;
}