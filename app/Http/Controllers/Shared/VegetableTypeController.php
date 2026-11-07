<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shared\VegetableTypeResource;
use App\Services\VegetableTypeService;

class VegetableTypeController extends Controller
{
    public function __construct(private VegetableTypeService $types) {}

    public function index()
    {
        return VegetableTypeResource::collection($this->types->active());
    }
}