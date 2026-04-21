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
        Schema::create('rents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('request_id')
                ->unique()
                ->constrained('rent_requests')
                ->cascadeOnDelete();

            $table->foreignId('space_id')
                ->constrained('spaces')
                ->cascadeOnDelete();

            $table->foreignId('renter_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->date('start_date');
            $table->date('end_date');

            $table->foreignId('status_id')
                ->constrained('statuses');

            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rents');
    }
};
