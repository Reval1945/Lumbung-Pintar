<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\DashboardRepositoryInterface;

class DashboardService
{
    public function __construct(private DashboardRepositoryInterface $dashboard) {}

    public function forUser(User $user): array
    {
        $data = match (true) {
            $user->hasRole(UserRole::Petani, UserRole::AgenDesa) => $this->seller($user),
            $user->hasRole(UserRole::Pembeli) => $this->buyer($user),
            default => $this->platform(),
        };

        $data['verified'] = $user->verified_at !== null;

        return $data;
    }

    private function seller(User $user): array
    {
        $s = $this->dashboard->sellerSummary($user->id);

        return $this->payload('seller', [
            $this->card('Tawaran tayang', $s['listings_active'], 'number', 'fa-seedling', 'success'),
            $this->card('Pesanan menunggu', $s['orders_pending'], 'number', 'fa-hourglass-half', 'warning'),
            $this->card('Pendapatan (selesai)', $s['revenue'], 'currency', 'fa-money-bill-wave', 'primary'),
            $this->card('Rating', $s['rating_avg'], 'rating', 'fa-star', 'info', " / 5 ({$s['rating_count']})"),
        ], $s);
    }

    private function buyer(User $user): array
    {
        $s = $this->dashboard->buyerSummary($user->id);

        return $this->payload('buyer', [
            $this->card('Pesanan aktif', $s['orders_active'], 'number', 'fa-box-open', 'success'),
            $this->card('Menunggu pembayaran', $s['payments_waiting'], 'number', 'fa-wallet', 'warning'),
            $this->card('Isi keranjang', $s['cart_items'], 'number', 'fa-shopping-cart', 'info'),
            $this->card('Total belanja (selesai)', $s['spent'], 'currency', 'fa-receipt', 'primary'),
        ], $s);
    }

    private function platform(): array
    {
        $s = $this->dashboard->platformSummary();

        return $this->payload('platform', [
            $this->card('Menunggu verifikasi', $s['pending_verification'], 'number', 'fa-user-clock', 'warning'),
            $this->card('Organisasi terverifikasi', $s['orgs_verified'], 'number', 'fa-building', 'success', " dari {$s['orgs_total']}"),
            $this->card('Pesanan selesai', $s['orders_done'], 'number', 'fa-check-circle', 'primary'),
            $this->card('Nilai transaksi selesai', $s['gmv'], 'currency', 'fa-coins', 'info'),
        ], $s);
    }

    private function card(string $label, float|int $value, string $type, string $icon, string $color, string $suffix = ''): array
    {
        return compact('label', 'value', 'type', 'icon', 'color', 'suffix');
    }

    private function payload(string $kind, array $cards, array $summary): array
    {
        return [
            'kind' => $kind,
            'cards' => $cards,
            'charts' => [
                'per_day' => $this->fillDays($summary['per_day']),
                'status' => $this->statusSeries($summary['status_breakdown']),
            ],
            'recent' => $summary['recent'],
        ];
    }

    /** Hari tanpa pesanan tetap tampil dengan nilai 0. */
    private function fillDays(array $counts): array
    {
        $labels = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $labels[] = $day->format('d/m');
            $values[] = (int) ($counts[$day->toDateString()] ?? 0);
        }

        return compact('labels', 'values');
    }

    private function statusSeries(array $counts): array
    {
        $keys = [];
        $values = [];

        foreach (OrderStatus::cases() as $status) {
            $keys[] = $status->value;
            $values[] = (int) ($counts[$status->value] ?? 0);
        }

        return compact('keys', 'values');
    }
}