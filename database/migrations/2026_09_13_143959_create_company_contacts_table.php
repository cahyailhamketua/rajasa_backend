<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_contacts', function (Blueprint $table) {
            $table->id();

            $table->string('platform', 50);
            $table->string('username')->nullable();

            $table->string('url', 500)->nullable();

            $table->string('icon', 100)->nullable();

            $table->unsignedInteger('urutan')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index([
                'is_active',
                'urutan',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_contacts');
    }
};