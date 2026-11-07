<?php

namespace App\Http\Controllers\Pembeli;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pembeli\CancelOrderRequest;
use App\Http\Requests\Pembeli\CheckoutRequest;
use App\Http\Requests\Pembeli\DirectOrderRequest;
use App\Http\Resources\Pembeli\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index(Request $request)
    {
        return OrderResource::collection(
            $this->orders->paginateForBuyer($request->user(), min($request->integer('per_page', 15), 50))
        );
    }

    public function show(Request $request, int $id): OrderResource
    {
        return new OrderResource($this->orders->findForBuyer($request->user(), $id));
    }

    /** Beli 1 barang langsung, tanpa keranjang. */
    public function direct(DirectOrderRequest $request)
    {
        $order = $this->orders->buyDirect($request->user(), $request->validated());

        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    /** Beli semua isi keranjang sekaligus. */
    public function checkout(CheckoutRequest $request)
    {
        $order = $this->orders->checkoutCart($request->user(), $request->validated());

        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    public function cancel(CancelOrderRequest $request, int $id): OrderResource
    {
        return new OrderResource($this->orders->cancel($request->user(), $id, $request->validated('cancel_reason')));
    }

    public function complete(Request $request, int $id): OrderResource
    {
        return new OrderResource($this->orders->advance($request->user(), $id, OrderStatus::Selesai));
    }
}