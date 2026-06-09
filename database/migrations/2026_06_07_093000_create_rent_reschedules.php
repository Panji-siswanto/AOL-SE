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
        Schema::create('rent_reschedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rent_request_id')->constrained('rent_requests')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->date('proposed_visit_date')->nullable();
            $table->date('proposed_start_date');
            $table->date('proposed_end_date');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_reschedules');
    }
};
