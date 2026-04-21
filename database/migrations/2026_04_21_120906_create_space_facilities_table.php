<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('space_facilities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('space_id')
                ->constrained('spaces')
                ->cascadeOnDelete();

            $table->foreignId('facility_id')
                ->constrained('facilities')
                ->cascadeOnDelete();

            $table->string('detail', 255)->nullable();

            $table->unique(['space_id', 'facility_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('space_facilities');
    }
};
