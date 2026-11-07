<?php

namespace App\Services;

use App\Enums\ListingStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ListingRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private ListingRepositoryInterface $listings,
        private CartRepositoryInterface $cart,
        private ListingService $listingService,
    ) {}

    // ---------- Baca ----------

    public function paginateForBuyer(User $buyer, int $perPage): LengthAwarePaginator
    {
        return $this->orders->paginateForBuyer($buyer->id, $perPage);
    }

    public function findForBuyer(User $buyer, int $id): Order
    {
        return $this->orders->findForBuyerOrFail($buyer->id, $id);
    }

    public function paginateForSeller(User $seller, int $perPage): LengthAwarePaginator
    {
        return $this->orders->paginateForSeller($seller->id, $perPage);
    }

    public function findForSeller(User $seller, int $id): Order
    {
        return $this->orders->findForSellerOrFail($seller->id, $id);
    }

    // ---------- Buat pesanan ----------

    /** Beli langsung 1 barang, tanpa keranjang. */
    public function buyDirect(User $buyer, array $data): Order
    {
        $lines = [['listing_id' => (int) $data['listing_id'], 'quantity' => (float) $data['quantity']]];

        return $this->createOrder($buyer, $lines, $data, clearCart: false);
    }

    /** Checkout seluruh isi keranjang menjadi satu pesanan. */
    public function checkoutCart(User $buyer, array $data): Order
    {
        $items = $this->cart->itemsFor($buyer->id);

        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => ['Keranjang masih kosong.']]);
        }

        $lines = $items->map(fn ($i) => [
            'listing_id' => $i->listing_id,
            'quantity' => (float) $i->quantity,
        ])->all();

        return $this->createOrder($buyer, $lines, $data, clearCart: true);
    }

    private function createOrder(User $buyer, array $lines, array $meta, bool $clearCart): Order
    {
        return DB::transaction(function () use ($buyer, $lines, $meta, $clearCart) {
            $listings = $this->listings->lockForUpdate(array_column($lines, 'listing_id'));

            $subtotal = 0.0;
            $itemsData = [];

            foreach ($lines as $line) {
                $listing = $listings->get($line['listing_id']);
                if (! $listing) {
                    throw ValidationException::withMessages(['listing_id' => ['Ada tawaran yang sudah tidak tersedia.']]);
                }

                $this->listingService->assertPurchasable($listing, $line['quantity'], $buyer);

                $lineTotal = round($listing->effective_price * $line['quantity'], 2);
                $subtotal += $lineTotal;

                $itemsData[] = [
                    'listing_id' => $listing->id,
                    'seller_id' => $listing->seller_id,
                    'quantity' => $line['quantity'],
                    'unit' => $listing->unit,
                    'unit_price' => $listing->base_price,
                    'discount_percent' => $listing->current_discount_percent,
                    'subtotal' => $lineTotal,
                    'grade_at_order' => $listing->grade,
                    'freshness_at_order' => $listing->freshness_status->value,
                ];

                $remaining = (float) $listing->quantity_available - $line['quantity'];
                $listing->quantity_available = $remaining;
                if ($remaining <= 0) {
                    $listing->status = ListingStatus::Habis;
                }
                $listing->save();
            }

            $feePercent = (float) config('lumbungpintar.service_fee_percent', 2.0);
            $fee = round($subtotal * $feePercent / 100, 2);

            $order = $this->orders->create([
                'order_number' => 'LP-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'buyer_id' => $buyer->id,
                'organization_id' => $buyer->organization_id,
                'status' => OrderStatus::MenungguKonfirmasi->value,
                'subtotal' => $subtotal,
                'service_fee_percent' => $feePercent,
                'service_fee_amount' => $fee,
                'total' => $subtotal + $fee,
                'delivery_address' => $meta['delivery_address'] ?? null,
                'needed_date' => $meta['needed_date'] ?? null,
                'notes' => $meta['notes'] ?? null,
            ]);

            $order->items()->createMany($itemsData);

            // Platform tidak memegang dana: hanya mencatat pembayaran (tahap awal: simulasi).
            $order->payment()->create([
                'method' => 'simulasi',
                'status' => 'menunggu',
                'amount' => $order->total,
                'is_simulated' => true,
            ]);

            if ($clearCart) {
                $this->cart->clear($buyer->id);
            }

            return $order->load('items.listing', 'payment', 'buyer:id,name,desa,kecamatan');
        });
    }

    // ---------- Ubah status ----------

    /** Pembeli boleh membatalkan selama masih menunggu konfirmasi; stok dikembalikan. */
    public function cancel(User $buyer, int $orderId, ?string $reason): Order
    {
        return DB::transaction(function () use ($buyer, $orderId, $reason) {
            $order = $this->orders->lockForUpdate($orderId);
            abort_unless($order->buyer_id === $buyer->id, 404);

            if ($order->isCancelled() || $order->status !== OrderStatus::MenungguKonfirmasi) {
                throw ValidationException::withMessages(['order' => ['Pesanan ini tidak dapat dibatalkan.']]);
            }

            $items = $order->items;
            $listings = $this->listings->lockForUpdate($items->pluck('listing_id')->all());

            foreach ($items as $item) {
                $listing = $listings->get($item->listing_id);
                if (! $listing) {
                    continue;
                }
                $listing->quantity_available = (float) $listing->quantity_available + (float) $item->quantity;
                if ($listing->status === ListingStatus::Habis) {
                    $listing->status = ListingStatus::Tayang;
                }
                $listing->save();
            }

            $order->payments()->where('status', 'menunggu')->update(['status' => 'gagal']);
            $order->update(['cancelled_at' => now(), 'cancel_reason' => $reason]);

            return $order->load('items.listing', 'payment');
        });
    }

    /**
     * Penjual: dikonfirmasi -> diproses -> dikirim. Pembeli: selesai.
     * Status harus berurutan.
     */
    public function advance(User $actor, int $orderId, OrderStatus $target): Order
    {
        return DB::transaction(function () use ($actor, $orderId, $target) {
            $order = $this->orders->lockForUpdate($orderId);

            if ($order->isCancelled()) {
                throw ValidationException::withMessages(['status' => ['Pesanan sudah dibatalkan.']]);
            }

            if ($actor->role === UserRole::Pembeli) {
                abort_unless($order->buyer_id === $actor->id, 404);
                $allowed = [OrderStatus::Selesai];
            } else {
                abort_unless($order->items()->where('seller_id', $actor->id)->exists(), 404);
                $allowed = [OrderStatus::Dikonfirmasi, OrderStatus::Diproses, OrderStatus::Dikirim];
            }

            if (! in_array($target, $allowed, true)) {
                throw ValidationException::withMessages(['status' => ['Anda tidak berhak mengubah ke status ini.']]);
            }
            if ($order->status->next() !== $target) {
                throw ValidationException::withMessages(['status' => ['Status harus berurutan dari status saat ini.']]);
            }

            $order->status = $target;
            if ($column = $target->timestampColumn()) {
                $order->{$column} = now();
            }
            $order->save();

            return $order->load('items.listing', 'payment', 'buyer:id,name,desa,kecamatan');
        });
    }
}