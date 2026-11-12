<?php

namespace App\Http\Resources\Shared;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isSelf = $request->user()?->id === $this->id;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->role->value,
            'organization_id' => $this->organization_id,
            'desa' => $this->desa,
            'kecamatan' => $this->kecamatan,
            'kabupaten' => $this->kabupaten,
            'is_verified' => $this->verified_at !== null,
            // Nomor HP dan email hanya terlihat oleh pemiliknya.
            'phone' => $this->when($isSelf, $this->phone),
            'email' => $this->when($isSelf, $this->email),
            'photo' => $this->photo ? Storage::url($this->photo) : null,
        ];
    }
}