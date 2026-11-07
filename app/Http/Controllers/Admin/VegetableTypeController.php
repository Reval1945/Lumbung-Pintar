<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveVegetableTypeRequest;
use App\Http\Resources\Shared\VegetableTypeResource;
use App\Services\VegetableTypeService;

class VegetableTypeController extends Controller
{
    public function __construct(private VegetableTypeService $types) {}

    public function store(SaveVegetableTypeRequest $request)
    {
        $type = $this->types->create($request->validated());

        return (new VegetableTypeResource($type))->response()->setStatusCode(201);
    }

    public function update(SaveVegetableTypeRequest $request, int $id): VegetableTypeResource
    {
        return new VegetableTypeResource($this->types->update($id, $request->validated()));
    }
}