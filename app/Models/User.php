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

    // app/Models/User.php

    public function getActionBtnAttribute(): ?object
    {
        $status = $this->ver_status;
        $isOwner = $this->hasRole('owner');

        if ($status == \App\Models\Status::USR_UNVERIFIED || $status == \App\Models\Status::USR_REJECTED) {
            return (object) [
                'label' => 'Verify Now!',
                'color' => 'bg-orange-500 hover:bg-orange-600 shadow-orange-500/30',
                'url' => route('verification.index') 
            ];
        }

        if ($status == \App\Models\Status::USR_VERIFIED && !$isOwner) {
            return (object) [
                'label' => 'List Your Space',
                'color' => 'bg-teal-600 hover:bg-teal-700 shadow-teal-500/30',
                'url' => route('space-registrations.create') 
            ];
        }

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
