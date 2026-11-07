<?php

namespace App\Http\Controllers\Pembeli;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pembeli\AddToCartRequest;
use App\Http\Requests\Pembeli\UpdateCartItemRequest;
use App\Http\Resources\Pembeli\CartItemResource;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(Request $request)
    {
        return $this->respond($request->user());
    }

    public function store(AddToCartRequest $request)
    {
        $this->cart->add($request->user(), $request->validated('listing_id'), (float) $request->validated('quantity'));

        return $this->respond($request->user());
    }

    public function update(UpdateCartItemRequest $request, int $listingId)
    {
        $this->cart->setQuantity($request->user(), $listingId, (float) $request->validated('quantity'));

        return $this->respond($request->user());
    }

    public function destroy(Request $request, int $listingId)
    {
        $this->cart->remove($request->user(), $listingId);

        return response()->noContent();
    }

    public function clear(Request $request)
    {
        $this->cart->clear($request->user());

        return response()->noContent();
    }

    private function respond(User $user)
    {
        $cart = $this->cart->list($user);

        return CartItemResource::collection($cart['items'])->additional(['meta' => ['subtotal' => $cart['subtotal']]]);
    }
}