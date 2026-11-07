<?php

namespace App\Http\Controllers\Petani;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Petani\UpdateOrderStatusRequest;
use App\Http\Resources\Petani\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index(Request $request)
    {
        return OrderResource::collection(
            $this->orders->paginateForSeller($request->user(), min($request->integer('per_page', 15), 50))
        );
    }

    public function show(Request $request, int $id): OrderResource
    {
        return new OrderResource($this->orders->findForSeller($request->user(), $id));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $id): OrderResource
    {
        $order = $this->orders->advance(
            $request->user(), $id, OrderStatus::from($request->validated('status'))
        );

        return new OrderResource($order);
    }
}