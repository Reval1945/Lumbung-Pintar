<?php

namespace App\Repositories\Eloquent;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new User());
    }

    public function findByLogin(string $login): ?User
    {
        return $this->model->newQuery()->where('email', $login)->orWhere('phone', $login)->first();
    }

    public function paginatePendingVerification(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->whereIn('role', [UserRole::AgenDesa->value, UserRole::Pembeli->value])
            ->whereNull('verified_at')
            ->latest('id')
            ->paginate($perPage);
    }

    public function markVerified(User $user, ?int $verifiedBy): User
    {
        $user->forceFill(['verified_at' => now(), 'verified_by' => $verifiedBy])->save();

        return $user;
    }
}