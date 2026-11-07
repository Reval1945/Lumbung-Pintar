<?php

namespace App\Http\Controllers\AdminMitra;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shared\UserResource;
use App\Services\VerificationService;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function __construct(private VerificationService $verification) {}

    public function index(Request $request)
    {
        return UserResource::collection($this->verification->pending(min($request->integer('per_page', 15), 50)));
    }

    public function approve(Request $request, int $id): UserResource
    {
        return new UserResource($this->verification->approve($request->user(), $id));
    }
}