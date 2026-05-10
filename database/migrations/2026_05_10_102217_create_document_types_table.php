<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g., 'ktp', 'surat_tanah'
            $table->string('name');           // e.g., 'Foto KTP', 'Surat Tanah'
            $table->string('context');        // e.g., 'user', 'space'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};