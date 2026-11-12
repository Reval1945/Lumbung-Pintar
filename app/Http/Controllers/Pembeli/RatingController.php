<?php

namespace App\Http\Controllers\Pembeli;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pembeli\StoreRatingRequest;
use App\Http\Resources\Shared\RatingResource;
use App\Services\RatingService;

class RatingController extends Controller
{
    public function __construct(private RatingService $ratings) {}

    public function store(StoreRatingRequest $request, int $orderId)
    {
        $rating = $this->ratings->create($request->user(), $orderId, $request->validated());

        return (new RatingResource($rating))->response()->setStatusCode(201);
    }
}