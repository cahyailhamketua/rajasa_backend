<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'nama_lengkap',
        'email',
        'nickname',
        'foto_profil',
        'hobi',
        'pendidikan',
        'pengalaman',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'hobi' => 'array',
            'pendidikan' => 'array',
            'pengalaman' => 'array',
            'password' => 'hashed',
        ];
    }

    protected $appends = [
        'foto_profil_url',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function socialMedia(): HasMany
    {
        return $this->hasMany(UserSocialMedia::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }

    public function passwordResetOtps(): HasMany
    {
        return $this->hasMany(PasswordResetOtp::class);
    }

    public function getFotoProfilUrlAttribute(): ?string
    {
        if (!$this->foto_profil) {
            return null;
        }

        return Storage::disk('public')->url($this->foto_profil);
    }
}