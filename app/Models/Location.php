<?php

namespace App\Models;

use App\Models\Space;
use App\Models\SpaceRegistration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'city',
        'province',
        'address',
        'latitude',
        'longitude',
    ];

    public function spaces(){
        return $this->hasMany(Space::class);
    }

    public function spaceRegistrations(){
        return $this->hasMany(SpaceRegistration::class);
    }
}