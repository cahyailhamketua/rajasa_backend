<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $table = 'company_profile';

    protected $fillable = [
        'nama',
        'tagline',
        'deskripsi',
        'logo',
        'foto_cover',
        'email',
        'nomor_telepon',
        'alamat',
    ];

    protected $appends = [
        'logo_url',
        'foto_cover_url',
    ];

    /**
     * Get logo URL.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        return Storage::disk('public')->url($this->logo);
    }

    /**
     * Get cover photo URL.
     */
    public function getFotoCoverUrlAttribute(): ?string
    {
        if (!$this->foto_cover) {
            return null;
        }

        return Storage::disk('public')->url($this->foto_cover);
    }
}