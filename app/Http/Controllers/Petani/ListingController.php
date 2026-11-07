<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Http\Requests\Petani\StoreListingRequest;
use App\Http\Requests\Petani\UpdateListingRequest;
use App\Http\Resources\Petani\ListingResource;
use App\Services\ListingService;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function __construct(private ListingService $listings) {}

    public function index(Request $request)
    {
        $filters = $request->only(['vegetable_type_id', 'category', 'grade', 'freshness_status', 'status', 'q']);
        $perPage = min($request->integer('per_page', 15), 50);

        return ListingResource::collection($this->listings->paginateForSeller($request->user(), $filters, $perPage));
    }

    public function store(StoreListingRequest $request)
    {
        $listing = $this->listings->create($request->user(), $request->validated());

        return (new ListingResource($listing))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $id): ListingResource
    {
        return new ListingResource($this->listings->findForSeller($request->user(), $id));
    }

    public function update(UpdateListingRequest $request, int $id): ListingResource
    {
        return new ListingResource($this->listings->update($request->user(), $id, $request->validated()));
    }

    public function confirm(Request $request, int $id): ListingResource
    {
        return new ListingResource($this->listings->confirm($request->user(), $id));
    }

    public function withdraw(Request $request, int $id): ListingResource
    {
        return new ListingResource($this->listings->withdraw($request->user(), $id));
    }
}