<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CompanyHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun',
        'judul',
        'deskripsi',
        'gambar',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'urutan' => 'integer',
        ];
    }

    protected $appends = [
        'gambar_url',
    ];

    /**
     * Get public URL for history image.
     */
    public function getGambarUrlAttribute(): ?string
    {
        if (!$this->gambar) {
            return null;
        }

        return Storage::disk('public')->url($this->gambar);
    }
}
