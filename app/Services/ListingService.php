<?php

namespace App\Services;

use App\Enums\FreshnessStatus;
use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\User;
use App\Repositories\Contracts\ListingRepositoryInterface;
use App\Repositories\Contracts\VegetableTypeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ListingService
{
    public function __construct(
        private ListingRepositoryInterface $listings,
        private VegetableTypeRepositoryInterface $vegetableTypes,
        private FreshnessService $freshness,
    ) {}

    // ---------- Sisi penjual ----------

    public function paginateForSeller(User $seller, array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->listings->paginateBySeller($seller->id, $filters, $perPage);
    }

    public function findForSeller(User $seller, int $id): Listing
    {
        return $this->listings->findOwnedOrFail($seller->id, $id);
    }

    public function create(User $seller, array $data): Listing
    {
        $listing = $this->listings->create($data + [
            'seller_id' => $seller->id,
            'status' => ListingStatus::Draft->value,
            'created_via' => 'manual',
            'freshness_status' => FreshnessStatus::Fresh->value,
            'current_discount_percent' => 0,
        ]);

        return $this->recalculate($listing);
    }

    public function update(User $seller, int $id, array $data): Listing
    {
        $listing = $this->listings->findOwnedOrFail($seller->id, $id);

        if (! in_array($listing->status, [ListingStatus::Draft, ListingStatus::Tayang], true)) {
            throw ValidationException::withMessages(['status' => ['Tawaran dengan status ini tidak dapat diubah.']]);
        }

        $listing->update($data);

        return $this->recalculate($listing->refresh());
    }

    /** Tawaran tayang hanya setelah dikonfirmasi petani; grade C tidak layak jual. */
    public function confirm(User $seller, int $id): Listing
    {
        $listing = $this->listings->findOwnedOrFail($seller->id, $id);

        if (! in_array($listing->status, [ListingStatus::Draft, ListingStatus::MenungguKonfirmasi], true)) {
            throw ValidationException::withMessages(['status' => ['Tawaran ini sudah tayang atau tidak bisa dikonfirmasi.']]);
        }
        if ($listing->grade === 'C') {
            throw ValidationException::withMessages(['grade' => ['Grade C tidak layak jual.']]);
        }
        if ((float) $listing->quantity_available <= 0) {
            throw ValidationException::withMessages(['quantity_available' => ['Stok harus lebih dari 0.']]);
        }

        $listing->update([
            'status' => ListingStatus::Tayang->value,
            'confirmed_by_seller_at' => now(),
        ]);

        return $listing->refresh()->load('vegetableType', 'photos');
    }

    public function withdraw(User $seller, int $id): Listing
    {
        $listing = $this->listings->findOwnedOrFail($seller->id, $id);
        $listing->update(['status' => ListingStatus::Ditarik->value]);

        return $listing->refresh();
    }

    // ---------- Sisi pembeli ----------

    public function browse(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->listings->paginatePublished($filters, $perPage);
    }

    public function findPublished(int $id): Listing
    {
        return $this->freshness->sync($this->listings->findPublishedOrFail($id));
    }

    /** Dipakai keranjang dan pesanan. Menyegarkan diskon dulu agar harga akurat. */
    public function assertPurchasable(Listing $listing, float $quantity, User $buyer): void
    {
        $this->freshness->sync($listing);

        $fail = fn (string $message) => throw ValidationException::withMessages(['listing_id' => [$message]]);

        if ($listing->status !== ListingStatus::Tayang) {
            $fail("Tawaran \"{$listing->title}\" tidak tersedia.");
        }
        if ($listing->freshness_status === FreshnessStatus::TidakLayakJual) {
            $fail("Tawaran \"{$listing->title}\" tidak layak jual.");
        }
        if ($listing->seller_id === $buyer->id) {
            $fail('Anda tidak dapat membeli tawaran milik sendiri.');
        }
        if ($quantity < (float) $listing->min_order_qty) {
            $fail("Jumlah minimum pembelian \"{$listing->title}\" adalah {$listing->min_order_qty} {$listing->unit}.");
        }
        if ($quantity > (float) $listing->quantity_available) {
            $fail("Stok \"{$listing->title}\" tersisa {$listing->quantity_available} {$listing->unit}.");
        }
    }

    private function recalculate(Listing $listing): Listing
    {
        $listing->load('vegetableType.discountTiers');
        $listing->fresh_until = $this->freshness->freshUntil($listing);
        $this->freshness->sync($listing);   // menyimpan fresh_until + status kesegaran

        return $listing->load('photos');
    }
}