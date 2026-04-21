<?php

namespace App\Models;

use App\Models\Rent;
use App\Models\RentRequest;
use App\Models\Space;
use App\Models\SpaceRegistration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'context',
        'code',
        'name',
    ];

    // relationships (we keep it minimal first)
    public function spaces()
    {
        return $this->hasMany(Space::class);
    }

    public function spaceRegistrations()
    {
        return $this->hasMany(SpaceRegistration::class);
    }

    public function rentRequests()
    {
        return $this->hasMany(RentRequest::class);
    }

    public function rents()
    {
        return $this->hasMany(Rent::class);
    }
}