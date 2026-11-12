<?php

namespace App\Http\Resources\Shared;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'address' => $this->address,
            'desa' => $this->desa,
            'kecamatan' => $this->kecamatan,
            'kabupaten' => $this->kabupaten,
            'contact_person' => $this->contact_person,
            'is_verified' => $this->verified_at !== null,
            'verified_at' => $this->verified_at,
        ];
    }
}