<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpacePhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'space_registration_id', 
        'space_id', 
        'file_path', 
        'description', 
        'is_primary'
    ];

    public function spaceRegistration(){
        return $this->belongsTo(SpaceRegistration::class);
    }

    public function space(){
        return $this->belongsTo(Space::class);
    }
}