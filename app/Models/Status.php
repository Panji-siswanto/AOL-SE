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

    // Registration Statuses
    public const REG_PENDING = 1;
    public const REG_APPROVED = 2;
    public const REG_REJECTED = 3;

    // Space Statuses
    public const SPC_AVAILABLE = 4;
    public const SPC_UNAVAILABLE = 5;

    // Rent Request Statuses
    public const RNT_REQ_PENDING = 6;
    public const RNT_REQ_ACCEPTED = 7;
    public const RNT_REQ_REJECTED = 8;
    public const RNT_REQ_CANCELLED = 9;

    // Rent Statuses
    public const RNT_ONGOING = 10;
    public const RNT_COMPLETED = 11;
    public const RNT_CANCELLED = 12;

    // Message Statuses
    public const MSG_PROPOSAL = 13;
    public const MSG_RESPONSE = 14;

    // User & Log Verification Statuses
    public const USR_UNVERIFIED = 15;      
    public const USR_VERIFY_PENDING = 16;   
    public const USR_VERIFIED = 17;         
    public const USR_REJECTED = 18;

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