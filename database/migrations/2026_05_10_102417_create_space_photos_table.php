<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('space_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_registration_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('space_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('description')->nullable(); // Captions (e.g., "Main entrance")
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('space_photos');
    }
};