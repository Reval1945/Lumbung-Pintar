<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // verified_at, verified_by, is_active sengaja tidak fillable (hanya diubah lewat repository).
    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role', 'organization_id',
        'desa', 'kecamatan', 'kabupaten', 'latitude', 'longitude',
        'is_small_farmer', 'prefers_voice_ui' , 'photo',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'verified_at' => 'datetime',
            'is_small_farmer' => 'boolean',
            'prefers_voice_ui' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function hasRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function verifier(): BelongsTo { return $this->belongsTo(self::class, 'verified_by'); }
    public function listings(): HasMany { return $this->hasMany(Listing::class, 'seller_id'); }
    public function cartItems(): HasMany { return $this->hasMany(CartItem::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class, 'buyer_id'); }
    public function plantingCalendars(): HasMany { return $this->hasMany(PlantingCalendar::class, 'farmer_id'); }
    public function voiceOffers(): HasMany { return $this->hasMany(VoiceOffer::class); }
    public function ratingsReceived(): HasMany { return $this->hasMany(Rating::class, 'ratee_id'); }
}