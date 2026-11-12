<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function __construct(private UserRepositoryInterface $users) {}

    public function update(User $user, array $data, ?UploadedFile $photo): User
    {
        if ($photo) {
            $newPath = $photo->store('profile-photos', 'public');

            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);   // hapus foto lama
            }
            $data['photo'] = $newPath;
        }

        return $this->users->update($user, $data);
    }
}