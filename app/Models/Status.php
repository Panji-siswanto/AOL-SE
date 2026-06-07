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
    public const SPC_PAUSED = 5;
    public const SPC_UNLISTED = 6;
    public const SPC_SUSPENDED = 7;

    // Rent Request Statuses
    public const RNT_REQ_PENDING = 8;
    public const RNT_REQ_ACCEPTED = 9;
    public const RNT_REQ_REJECTED = 10;
    public const RNT_REQ_CANCELLED = 11;

    // Rent Statuses
    public const RNT_ONGOING = 12;
    public const RNT_COMPLETED = 13;
    public const RNT_CANCELLED = 14;

    // Message Statuses 
    public const MSG_PROPOSAL            = 15;
    public const MSG_RESPONSE            = 16;
    public const MSG_APPLICATION         = 21; // initial 
    public const MSG_DECLINE_REASON      = 22; // Owner's rejection reason
    public const MSG_RESCHEDULE_PROPOSAL = 23; // propose new date
    public const MSG_RESCHEDULE_ACCEPTED = 24; // Renter says yes
    public const MSG_RESCHEDULE_REJECTED = 25; // Renter says no

    // User Identity Verification Statuses
    public const USR_UNVERIFIED = 17;
    public const USR_VERIFY_PENDING = 18;
    public const USR_VERIFIED = 19;
    public const USR_REJECTED = 20;

    public function spaces(){
        return $this->hasMany(Space::class);
    }

    public function spaceRegistrations(){
        return $this->hasMany(SpaceRegistration::class);
    }

    public function rentRequests(){
        return $this->hasMany(RentRequest::class);
    }

    public function rents(){
        return $this->hasMany(Rent::class);
    }
}