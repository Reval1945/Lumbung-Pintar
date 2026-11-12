<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shared\RatingResource;
use App\Services\RatingService;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function __construct(private RatingService $ratings) {}

    public function index(Request $request)
    {
        return RatingResource::collection(
            $this->ratings->received($request->user(), min($request->integer('per_page', 15), 50))
        )->additional(['meta' => ['summary' => $this->ratings->summary($request->user()->id)]]);
    }
}