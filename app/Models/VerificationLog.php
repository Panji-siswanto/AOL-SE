<?php

namespace App\Models;

use App\Models\VerificationDocument;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VerificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_id',
        'status_id',
        'note', // Renamed from admin_note to match your ERD diagram perfectly
    ];

    /**
     * Relationship to the user submitting the verification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship to the administrator who reviewed the submission.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Relationship to the processing status.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    /**
     * NEW: One-to-Many mapping to normalized staging documents.
     */
    public function documents(): HasMany
    {
        // Maps to VerificationDocument model where foreign key is 'logs_id'
        return $this->hasMany(VerificationDocument::class, 'logs_id');
    }
}