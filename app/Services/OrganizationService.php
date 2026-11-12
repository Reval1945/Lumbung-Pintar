<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class OrganizationService
{
    public function __construct(
        private OrganizationRepositoryInterface $organizations,
        private UserRepositoryInterface $users,
    ) {}

    public function paginate(int $perPage): LengthAwarePaginator
    {
        return $this->organizations->paginate($perPage);
    }

    public function create(array $data): Organization
    {
        return $this->organizations->create($data);
    }

    public function update(int $id, array $data): Organization
    {
        return $this->organizations->update($this->organizations->findOrFail($id), $data);
    }

    public function verify(User $admin, int $id): Organization
    {
        $org = $this->organizations->findOrFail($id);

        if ($org->verified_at !== null) {
            throw ValidationException::withMessages(['organization' => ['Organisasi ini sudah terverifikasi.']]);
        }

        return $this->organizations->markVerified($org, $admin->id);
    }

    /** Menautkan akun ke organisasi agar pesanannya tercatat atas nama organisasi. */
    public function attachUser(int $organizationId, int $userId): User
    {
        $org = $this->organizations->findOrFail($organizationId);

        return $this->users->update($this->users->findOrFail($userId), ['organization_id' => $org->id]);
    }
}