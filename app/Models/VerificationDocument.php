<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationDocument extends Model
{
    use HasFactory;

    // Explicitly define table name if Laravel tries to pluralize unexpectedly
    protected $table = 'verification_documents';

    protected $fillable = [
        'logs_id',
        'document_type_id',
        'file_path',
        'description',
    ];

    /**
     * Parent staging log mapping.
     */
    public function log(): BelongsTo
    {
        return $this->belongsTo(VerificationLog::class, 'logs_id');
    }

    /**
     * Document classification metadata mapping.
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }
}