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

    public const USR_UNVERIFIED = 1;
    public const USR_VERIFY_PENDING = 2;
    public const USR_VERIFIED = 3;
    public const USR_REJECTED = 4;

    public const REG_PENDING = 5;
    public const REG_APPROVED = 6;
    public const REG_REJECTED = 7;

    public const SPC_AVAILABLE = 8;
    public const SPC_PAUSED = 9;
    public const SPC_UNLISTED = 10;
    public const SPC_SUSPENDED = 11;

    public const RNT_REQ_PENDING = 12;
    public const RNT_AWAITING_PAYMENT = 13;
    public const RNT_REQ_REJECTED = 14;
    public const RNT_REQ_CANCELLED = 15;

    public const RNT_ONGOING = 16;
    public const RNT_COMPLETED = 17;
    public const RNT_CANCELLED = 18;

    public const MSG_APPLICATION = 19;            
    public const MSG_APPROVAL_NOTE = 20;      
    public const MSG_DECLINE_REASON = 21;      
    public const MSG_RESCHEDULE_PROPOSAL = 22;  
    public const MSG_RESCHEDULE_ACCEPTED = 23;  
    public const MSG_RESCHEDULE_REJECTED = 24;  

    public const MSG_FINISH_REQUEST = 25;  
    public const MSG_FINISH_ACCEPTED = 26;  
    public const MSG_FINISH_REJECTED = 27;  

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