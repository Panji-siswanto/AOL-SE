<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpaceDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'space_registration_id', 
        'document_type_id', 
        'file_path', 
        'description'
    ];



    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }
    
    public function registration()
    {
        return $this->belongsTo(SpaceRegistration::class, 'space_registration_id');
    }
}