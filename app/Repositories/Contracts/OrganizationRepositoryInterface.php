<?php

namespace App\Repositories\Contracts;

use App\Models\Organization;

interface OrganizationRepositoryInterface extends BaseRepositoryInterface
{
    public function markVerified(Organization $organization, int $verifiedBy): Organization;
}