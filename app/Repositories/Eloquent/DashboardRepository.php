<?php

namespace App\Repositories\Eloquent;

use App\Enums\ListingStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\CartItem;
use App\Models\Listing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Rating;
use App\Models\User;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function sellerSummary(int $sellerId): array
    {
        // Closure agar setiap pemakaian mendapat query baru (tidak saling memengaruhi).
        $orders = fn () => Order::query()->whereHas('items', fn ($q) => $q->where('seller_id', $sellerId));

        $rating = Rating::query()->where('ratee_id', $sellerId)
            ->selectRaw('COUNT(*) as total, AVG(score) as average')->first();

        $revenue = OrderItem::query()
            ->where('seller_id', $sellerId)
            ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::Selesai->value)->whereNull('cancelled_at'))
            ->sum('subtotal');

        $recent = $orders()
            ->with(['buyer:id,name', 'items' => fn ($q) => $q->where('seller_id', $sellerId)])
            ->latest('id')->limit(5)->get()
            ->map(fn (Order $o) => $this->recentRow($o, $o->buyer?->name ?? '-', (float) $o->items->sum('subtotal')))
            ->all();

        return [
            'listings_active' => Listing::query()->where('seller_id', $sellerId)
                ->where('status', ListingStatus::Tayang->value)->count(),
            'orders_pending' => $orders()->whereNull('cancelled_at')
                ->where('status', OrderStatus::MenungguKonfirmasi->value)->count(),
            'revenue' => (float) $revenue,
            'rating_avg' => round((float) ($rating->average ?? 0), 2),
            'rating_count' => (int) ($rating->total ?? 0),
            'per_day' => $this->perDay($orders()),
            'status_breakdown' => $this->statusCounts($orders()),
            'recent' => $recent,
        ];
    }

    public function buyerSummary(int $buyerId): array
    {
        $orders = fn () => Order::query()->where('buyer_id', $buyerId);

        $recent = $orders()->withCount('items')->latest('id')->limit(5)->get()
            ->map(fn (Order $o) => $this->recentRow($o, $o->items_count . ' barang', (float) $o->total))
            ->all();

        return [
            'orders_active' => $orders()->whereNull('cancelled_at')
                ->where('status', '!=', OrderStatus::Selesai->value)->count(),
            'payments_waiting' => Payment::query()->where('status', 'menunggu')
                ->whereHas('order', fn ($q) => $q->where('buyer_id', $buyerId)->whereNull('cancelled_at'))->count(),
            'cart_items' => CartItem::query()->where('user_id', $buyerId)->count(),
            'spent' => (float) $orders()->whereNull('cancelled_at')
                ->where('status', OrderStatus::Selesai->value)->sum('total'),
            'per_day' => $this->perDay($orders()),
            'status_breakdown' => $this->statusCounts($orders()),
            'recent' => $recent,
        ];
    }

    public function platformSummary(): array
    {
        $orders = fn () => Order::query();

        $recent = $orders()->with('buyer:id,name')->latest('id')->limit(5)->get()
            ->map(fn (Order $o) => $this->recentRow($o, $o->buyer?->name ?? '-', (float) $o->total))
            ->all();

        return [
            'pending_verification' => User::query()
                ->whereIn('role', [UserRole::AgenDesa->value, UserRole::Pembeli->value])
                ->whereNull('verified_at')->count(),
            'orgs_verified' => Organization::query()->whereNotNull('verified_at')->count(),
            'orgs_total' => Organization::query()->count(),
            'orders_done' => $orders()->whereNull('cancelled_at')->where('status', OrderStatus::Selesai->value)->count(),
            'gmv' => (float) $orders()->whereNull('cancelled_at')->where('status', OrderStatus::Selesai->value)->sum('total'),
            'per_day' => $this->perDay($orders()),
            'status_breakdown' => $this->statusCounts($orders()),
            'recent' => $recent,
        ];
    }

    /** @return array<string,int> tanggal (Y-m-d) => jumlah pesanan, 7 hari terakhir. */
    private function perDay(Builder $query): array
    {
        return $query->whereNull('cancelled_at')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupByRaw('DATE(created_at)')
            ->pluck('c', 'd')->all();
    }

    /** @return array<string,int> status => jumlah */
    private function statusCounts(Builder $query): array
    {
        return $query->whereNull('cancelled_at')
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')->all();
    }

    private function recentRow(Order $o, string $party, float $amount): array
    {
        return [
            'id' => $o->id,
            'order_number' => $o->order_number,
            'party' => $party,
            'status' => $o->status->value,
            'cancelled' => $o->isCancelled(),
            'amount' => $amount,
            'date' => $o->created_at?->toDateString(),
        ];
    }
}