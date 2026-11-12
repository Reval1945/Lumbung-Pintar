<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Services\RatingService;

class SellerRatingController extends Controller
{
    public function __construct(private RatingService $ratings) {}

    public function show(int $sellerId)
    {
        return response()->json(['data' => $this->ratings->summary($sellerId)]);
    }
}