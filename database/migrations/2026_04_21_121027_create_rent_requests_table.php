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
    Schema::create('rent_requests', function (Blueprint $table) {
        $table->id();

        $table->foreignId('renter_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('space_id')->constrained('spaces')->cascadeOnDelete();
        $table->date('start_date');
        $table->date('end_date');
        $table->date('visit_date')->nullable();
        $table->foreignId('pricing_id')
            ->nullable()
            ->constrained('space_registration_prices') 
            ->nullOnDelete();
        $table->decimal('total_price', 15, 2)->default(0);
        $table->foreignId('status_id')->constrained('statuses');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_requests');
    }
};