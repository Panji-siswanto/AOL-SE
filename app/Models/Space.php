<?php

namespace App\Models;

use App\Models\Bookmark;
use App\Models\Facility;
use App\Models\Rent;
use App\Models\RentRequest;
use App\Models\SpaceRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Space extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'location_id',
        'registration_id',
        'name',
        'description',
        'size',
        'price',
        'status_id',
    ];

    // belongs to owner
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // belongs to location
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // belongs to registration
    public function registration()
    {
        return $this->belongsTo(SpaceRegistration::class, 'registration_id');
    }

    // belongs to status
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    // many-to-many facilities
    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'space_facilities')
            ->withPivot('detail')
            ->withTimestamps();
    }

    // bookmarks
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    // rent requests
    public function rentRequests()
    {
        return $this->hasMany(RentRequest::class);
    }

    // rents
    public function rents()
    {
        return $this->hasMany(Rent::class);
    }
}