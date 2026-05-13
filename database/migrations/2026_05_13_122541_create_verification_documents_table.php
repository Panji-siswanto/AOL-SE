<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_documents', function (Blueprint $table) {
            $table->id();
            
            // Parent Log Link (Cascade deletes assets if the staging log is wiped)
            $table->foreignId('logs_id')
                  ->constrained('verification_logs')
                  ->onDelete('cascade');
            
            // Document Type Classification Link
            $table->foreignId('document_type_id')
                  ->constrained('document_types')
                  ->onDelete('restrict'); // Prevents accidental deletion of active doc definitions
            
            $table->string('file_path');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_documents');
    }};