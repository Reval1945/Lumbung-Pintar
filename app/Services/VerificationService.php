<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class VerificationService
{
    public function __construct(private UserRepositoryInterface $users) {}

    public function pending(int $perPage): LengthAwarePaginator
    {
        return $this->users->paginatePendingVerification($perPage);
    }

    public function approve(User $admin, int $userId): User
    {
        $user = $this->users->findOrFail($userId);

        if (! $user->hasRole(UserRole::AgenDesa, UserRole::Pembeli)) {
            throw ValidationException::withMessages(['user' => ['Hanya agen desa dan pembeli yang perlu diverifikasi.']]);
        }
        if ($user->verified_at !== null) {
            throw ValidationException::withMessages(['user' => ['Akun ini sudah terverifikasi.']]);
        }

        return $this->users->markVerified($user, $admin->id);
    }
}