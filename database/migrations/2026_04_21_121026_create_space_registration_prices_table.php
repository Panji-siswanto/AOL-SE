<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('space_registration_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pricing_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->timestamps();
            $table->unique(['space_registration_id', 'pricing_type_id'], 'space_reg_price_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('space_registration_prices');
    }
};