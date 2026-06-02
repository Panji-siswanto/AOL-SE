<?php

namespace App\Models;

use App\Models\Space;
use App\Models\SpaceRegistration;
use App\Models\Bookmark;
use App\Models\RentRequest;
use App\Models\Rent;
use App\Models\RentMessage;
use App\Models\RegistrationLog;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
    
#[Fillable(['name',
    'username',
    'email',
    'phone',
    'password',
    'ver_status',
    'verified_at'])]
#[Hidden(['password', 'remember_token'])]

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    // spaces owned by user
    public function spaces()
    {
        return $this->hasMany(Space::class, 'owner_id');
}

    // space registrations submitted by user
    public function spaceRegistrations()
    {
        return $this->hasMany(SpaceRegistration::class, 'owner_id');
    }

    // bookmarks
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    // rent requests as renter
    public function rentRequests()
    {
        return $this->hasMany(RentRequest::class, 'renter_id');
    }

    // rents as renter
    public function rents()
    {
        return $this->hasMany(Rent::class, 'renter_id');
    }

    // messages sent
    public function rentMessages()
    {
        return $this->hasMany(RentMessage::class, 'sender_id');
    }

    // admin logs (if user is admin)
    public function registrationLogs()
    {
        return $this->hasMany(RegistrationLog::class, 'admin_id');
    }

    /**
     * Get all verification documents uploaded by the user.
     */
    public function documents()
    {
        return $this->hasMany(UserDocument::class);
    }

    public function verificationStatus()
    {
        return $this->belongsTo(Status::class, 'ver_status');
    }


    public function getIsOwnerAttribute(): bool
    {
        // A user is an owner if they have the 'owner' role via Spatie
        return $this->hasRole('owner');
    }

    public function getIsVerifiedAttribute(): bool
    {
        // A user is verified regardless of role if their status is verified
        return $this->ver_status === \App\Models\Status::USR_VERIFIED;
    }

    public function getIsPendingVerificationAttribute(): bool
    {
        return $this->ver_status === \App\Models\Status::USR_VERIFY_PENDING;
    }

    // app/Models/User.php

 public function getActionBtnAttribute(): ?object
    {
        $status = $this->ver_status;
        $isOwner = $this->hasRole('owner');
        
        // Check if the user has submitted at least one space registration
        $hasRegistrations = \App\Models\SpaceRegistration::where('owner_id', $this->id)->exists();

        // 1. Needs Identity Verification
        if ($status == \App\Models\Status::USR_UNVERIFIED || $status == \App\Models\Status::USR_REJECTED) {
            return (object) [
                'label' => 'Verify Now!',
                'color' => 'bg-orange-500 hover:bg-orange-600 shadow-orange-500/30',
                'url' => route('verification.index') 
            ];
        }

        // 2. Verified AND (Is an Owner OR has a pending application) -> Show Management Dashboard
        if ($isOwner || $hasRegistrations) {
            return (object) [
                'label' => 'My Listings',
                'color' => 'bg-gray-900 hover:bg-gray-800 shadow-gray-900/30',
                'url' => route('space-registrations.index') 
            ];
        }

        // 3. Verified, NOT an owner, and NO applications -> First Time Listing
        if ($status == \App\Models\Status::USR_VERIFIED && !$hasRegistrations) {
            return (object) [
                'label' => 'List Your Space',
                'color' => 'bg-[#009485] hover:bg-teal-700 shadow-teal-500/30',
                'url' => route('space-registrations.create') 
            ];
        }

        // Returns null for USR_VERIFY_PENDING
        return null;
    }
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verified_at' => 'datetime', 
            'password' => 'hashed',
        ];
    }
}
