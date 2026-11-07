<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByLogin(string $login): ?User;
    public function paginatePendingVerification(int $perPage = 15): LengthAwarePaginator;
    public function markVerified(User $user, ?int $verifiedBy): User;
}