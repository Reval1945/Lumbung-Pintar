<?php

namespace App\Repositories\Eloquent;

use App\Models\Organization;
use App\Repositories\Contracts\OrganizationRepositoryInterface;

class OrganizationRepository extends BaseRepository implements OrganizationRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Organization());
    }

    public function markVerified(Organization $organization, int $verifiedBy): Organization
    {
        $organization->forceFill(['verified_at' => now(), 'verified_by' => $verifiedBy])->save();

        return $organization;
    }
}