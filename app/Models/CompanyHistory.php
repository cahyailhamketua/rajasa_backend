<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}