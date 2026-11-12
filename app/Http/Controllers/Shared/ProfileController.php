<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\UpdateProfileRequest;
use App\Http\Resources\Shared\UserResource;
use App\Services\ProfileService;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profile) {}

    public function update(UpdateProfileRequest $request): UserResource
    {
        $user = $this->profile->update(
            $request->user(),
            $request->safe()->except('photo'),
            $request->file('photo'),
        );

        return new UserResource($user);
    }
}