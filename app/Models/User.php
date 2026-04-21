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

#[Fillable(['name', 'username', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
