<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('gallery_id')
                ->constrained('galleries')
                ->cascadeOnDelete();

            $table->string('image');

            $table->unsignedInteger('urutan')->default(0);

            $table->timestamps();

            $table->index([
                'gallery_id',
                'urutan',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_images');
    }
};