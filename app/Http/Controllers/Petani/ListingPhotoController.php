<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Http\Requests\Petani\UploadListingPhotoRequest;
use App\Http\Resources\Petani\ListingPhotoResource;
use App\Services\ListingPhotoService;
use Illuminate\Http\Request;

class ListingPhotoController extends Controller
{
    public function __construct(private ListingPhotoService $photos) {}

    public function store(UploadListingPhotoRequest $request, int $listingId)
    {
        $photo = $this->photos->upload(
            $request->user(), $listingId, $request->file('photo'), $request->safe()->except('photo')
        );

        return (new ListingPhotoResource($photo))->response()->setStatusCode(201);
    }

    public function destroy(Request $request, int $listingId, int $photoId)
    {
        $this->photos->destroy($request->user(), $listingId, $photoId);

        return response()->noContent();
    }
}