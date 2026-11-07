<?php

namespace App\Http\Controllers\Pembeli;

use App\Http\Controllers\Controller;
use App\Http\Resources\Pembeli\ListingResource;
use App\Services\ListingService;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function __construct(private ListingService $listings) {}

    public function index(Request $request)
    {
        $filters = $request->only(['vegetable_type_id', 'category', 'grade', 'freshness_status', 'q']);

        return ListingResource::collection(
            $this->listings->browse($filters, min($request->integer('per_page', 15), 50))
        );
    }

    public function show(int $id): ListingResource
    {
        return new ListingResource($this->listings->findPublished($id));
    }
}