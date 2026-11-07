<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function __construct(private UserRepositoryInterface $users) {}

    /** @return array{0: User, 1: string} */
    public function register(array $data): array
    {
        $user = $this->users->create($data);

        // Petani langsung aktif; agen desa dan pembeli menunggu verifikasi admin mitra.
        if ($user->role === UserRole::Petani) {
            $this->users->markVerified($user, null);
        }

        return [$user->refresh(), $user->createToken('app')->plainTextToken];
    }

    /** @return array{0: User, 1: string} */
    public function login(string $login, string $password): array
    {
        $user = $this->users->findByLogin($login);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['login' => ['Nomor HP/email atau kata sandi salah.']]);
        }
        if (! $user->is_active) {
            throw ValidationException::withMessages(['login' => ['Akun Anda dinonaktifkan.']]);
        }

        return [$user, $user->createToken('app')->plainTextToken];
    }

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        // Hanya token API yang tersimpan di database yang bisa dihapus.
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }
}
