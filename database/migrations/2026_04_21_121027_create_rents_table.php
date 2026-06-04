<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

            // (to store space details at time of rent) 
            $table->string('space_name');
            $table->decimal('price', 15, 2); 
            $table->string('pricing_type');  
            $table->decimal('space_length', 8, 2)->nullable();
            $table->decimal('space_width', 8, 2)->nullable();
            $table->decimal('space_area', 8, 2);
            $table->text('space_address'); 
            $table->decimal('space_latitude', 10, 7);
            $table->decimal('space_longitude', 11, 7);

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

    public function down(): void
    {
        Schema::dropIfExists('rents');
    }
};