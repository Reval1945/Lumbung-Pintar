<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    /**
     * Pembayaran SIMULASI (tahap awal). Platform tidak memegang dana, jadi tidak ada
     * pergerakan uang di sini; hanya status dan referensi.
     */
    public function simulatePay(User $buyer, int $orderId, string $method): Order
    {
        return DB::transaction(function () use ($buyer, $orderId, $method) {
            $order = $this->orders->lockForUpdate($orderId);
            abort_unless($order->buyer_id === $buyer->id, 404);

            if ($order->isCancelled()) {
                throw ValidationException::withMessages(['order' => ['Pesanan sudah dibatalkan.']]);
            }

            $payment = $order->payments()->where('status', 'menunggu')->latest('id')->first();
            if (! $payment) {
                throw ValidationException::withMessages(['payment' => ['Tidak ada tagihan yang menunggu pembayaran.']]);
            }

            $payment->update([
                'method' => $method,
                'status' => 'dibayar',
                'is_simulated' => true,
                'provider' => 'simulasi',
                'provider_reference' => 'SIM-' . strtoupper(Str::random(10)),
                'paid_at' => now(),
            ]);

            return $order->load('items.listing', 'payment', 'buyer:id,name,desa,kecamatan');
        });
    }
}