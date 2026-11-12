<?php

namespace App\Repositories\Contracts;

interface DashboardRepositoryInterface
{
    public function sellerSummary(int $sellerId): array;
    public function buyerSummary(int $buyerId): array;
    public function platformSummary(): array;
}