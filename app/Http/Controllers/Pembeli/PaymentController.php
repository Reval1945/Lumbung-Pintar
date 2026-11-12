<?php

namespace App\Http\Controllers\Pembeli;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pembeli\PayOrderRequest;
use App\Http\Resources\Pembeli\OrderResource;
use App\Services\PaymentService;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    public function pay(PayOrderRequest $request, int $id): OrderResource
    {
        return new OrderResource($this->payments->simulatePay($request->user(), $id, $request->validated('method')));
    }
}