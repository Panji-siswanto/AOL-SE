<?php

namespace App\Models;

use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function spaces(){
        return $this->belongsToMany(Space::class, 'space_facilities')
            ->withPivot('detail')
            ->withTimestamps();
    }
}