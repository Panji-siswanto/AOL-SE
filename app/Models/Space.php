<?php

namespace App\Models;

use App\Models\Bookmark;
use App\Models\Facility;
use App\Models\Rent;
use App\Models\RentRequest;
use App\Models\SpaceRegistration;
use App\Models\User;
use App\Traits\Filterable;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Space extends Model
{
    use HasFactory, Searchable, Filterable;

    protected $fillable = [
        'owner_id',
        'location_id',
        'registration_id',
        'name',
        'description',
        'length',
        'width',
        'area',
        'price',
        'status_id',
    ];

    public function getFormattedSizeAttribute()
    {
        if ($this->length && $this->width) {
            $l = $this->length + 0;
            $w = $this->width + 0;
            return "{$l} x {$w} m";
        }
        $a = $this->area + 0;
        return "{$a} m²";
    }

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

    public function photos()
    {
        return $this->hasMany(SpacePhoto::class);
    }
}