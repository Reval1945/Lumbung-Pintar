<?php

namespace App\Http\Controllers\AdminMitra;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminMitra\AttachOrganizationUserRequest;
use App\Http\Requests\AdminMitra\SaveOrganizationRequest;
use App\Http\Resources\Shared\OrganizationResource;
use App\Http\Resources\Shared\UserResource;
use App\Services\OrganizationService;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function __construct(private OrganizationService $organizations) {}

    public function index(Request $request)
    {
        return OrganizationResource::collection($this->organizations->paginate(min($request->integer('per_page', 15), 50)));
    }

    public function store(SaveOrganizationRequest $request)
    {
        return (new OrganizationResource($this->organizations->create($request->validated())))
            ->response()->setStatusCode(201);
    }

    public function update(SaveOrganizationRequest $request, int $id): OrganizationResource
    {
        return new OrganizationResource($this->organizations->update($id, $request->validated()));
    }

    public function verify(Request $request, int $id): OrganizationResource
    {
        return new OrganizationResource($this->organizations->verify($request->user(), $id));
    }

    public function attachUser(AttachOrganizationUserRequest $request, int $id): UserResource
    {
        return new UserResource($this->organizations->attachUser($id, $request->validated('user_id')));
    }
}