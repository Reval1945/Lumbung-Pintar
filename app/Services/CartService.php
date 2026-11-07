<?php

namespace App\Services;

use App\Enums\FreshnessStatus;
use App\Enums\ListingStatus;
use App\Models\CartItem;
use App\Models\User;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ListingRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartService
{
    public function __construct(
        private CartRepositoryInterface $cart,
        private ListingRepositoryInterface $listings,
        private ListingService $listingService,
        private FreshnessService $freshness,
    ) {}

    /** @return array{items: \Illuminate\Database\Eloquent\Collection, subtotal: float} */
    public function list(User $buyer): array
    {
        $items = $this->cart->itemsFor($buyer->id);
        $subtotal = 0.0;

        foreach ($items as $item) {
            $listing = $this->freshness->sync($item->listing);
            $qty = (float) $item->quantity;

            $available = $listing->status === ListingStatus::Tayang
                && $listing->freshness_status !== FreshnessStatus::TidakLayakJual
                && $qty <= (float) $listing->quantity_available;

            $item->setAttribute('line_total', round($listing->effective_price * $qty, 2));
            $item->setAttribute('is_available', $available);

            if ($available) {
                $subtotal += $item->line_total;
            }
        }

        return ['items' => $items, 'subtotal' => round($subtotal, 2)];
    }

    /** Menambah barang yang sama = menambah jumlahnya. */
    public function add(User $buyer, int $listingId, float $quantity): CartItem
    {
        $listing = $this->listings->findPublishedOrFail($listingId);
        $existing = $this->cart->findItem($buyer->id, $listingId);
        $newQty = $quantity + (float) ($existing?->quantity ?? 0);

        $this->listingService->assertPurchasable($listing, $newQty, $buyer);

        return $this->cart->setQuantity($buyer->id, $listingId, $newQty);
    }

    public function setQuantity(User $buyer, int $listingId, float $quantity): CartItem
    {
        if (! $this->cart->findItem($buyer->id, $listingId)) {
            throw new ModelNotFoundException();
        }

        $listing = $this->listings->findPublishedOrFail($listingId);
        $this->listingService->assertPurchasable($listing, $quantity, $buyer);

        return $this->cart->setQuantity($buyer->id, $listingId, $quantity);
    }

    public function remove(User $buyer, int $listingId): void
    {
        $this->cart->remove($buyer->id, $listingId);
    }

    public function clear(User $buyer): void
    {
        $this->cart->clear($buyer->id);
    }
}