<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_profile', function (Blueprint $table) {
            $table->id();

            $table->string('nama')->nullable();
            $table->string('tagline')->nullable();

            $table->text('deskripsi_singkat')->nullable();
            $table->longText('deskripsi')->nullable();

            $table->string('logo')->nullable();
            $table->string('foto_cover')->nullable();

            $table->string('email')->nullable();
            $table->string('nomor_telepon', 50)->nullable();

            $table->text('alamat')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_profile');
    }
};