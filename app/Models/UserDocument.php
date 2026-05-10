<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'document_type_id', 
        'file_path', 
        'description'
    ];

    // Belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Belongs to a specific Document Type dictionary entry
    public function type()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }
}