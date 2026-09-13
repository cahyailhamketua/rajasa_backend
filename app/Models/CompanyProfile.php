<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $table = 'company_profile';

    protected $fillable = [
        'nama',
        'tagline',
        'deskripsi_singkat',
        'deskripsi',
        'logo',
        'foto_cover',
        'email',
        'nomor_telepon',
        'alamat',
    ];
}