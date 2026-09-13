<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_histories', function (Blueprint $table) {
            $table->id();

            $table->year('tahun')->nullable();

            $table->string('judul')->nullable();

            $table->text('deskripsi')->nullable();

            $table->string('gambar')->nullable();

            $table->unsignedInteger('urutan')->default(0);

            $table->timestamps();

            $table->index([
                'tahun',
                'urutan',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_histories');
    }
};