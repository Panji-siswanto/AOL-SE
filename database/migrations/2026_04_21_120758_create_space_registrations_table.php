<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('space_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('description');
            //size
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('area', 8, 2);
            
            $table->foreignId('status_id')->constrained('statuses');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('space_registrations');
    }
};