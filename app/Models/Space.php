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
use Illuminate\Support\Facades\Auth;

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

    public function getFormattedSizeAttribute(){
        if ($this->length && $this->width) {
            $l = $this->length + 0;
            $w = $this->width + 0;
            return "{$l} x {$w} m";
        }
        $a = $this->area + 0;
        return "{$a} m²";
    }

    public function getCoverPhotoUrlAttribute(){
        $photo = $this->photos->where('is_primary', true)->first() 
              ?? $this->photos->first()
              ?? $this->registration->photos->where('is_primary', true)->first() 
              ?? $this->registration->photos->first();

        if ($photo) {
            return asset('storage/' . $photo->file_path);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=E5E7EB&color=9CA3AF&size=512';
    }

    public function getHasActiveRequestAttribute(): bool{
        if (!Auth::check()) return false;
        
        return $this->rentRequests()
            ->where('renter_id', Auth::id())
            ->whereIn('status_id', [Status::RNT_REQ_PENDING, Status::RNT_AWAITING_PAYMENT])
            ->exists();
    }

    public function owner(){
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function location(){
        return $this->belongsTo(Location::class);
    }

    public function registration(){
        return $this->belongsTo(SpaceRegistration::class, 'registration_id');
    }

    public function status(){
        return $this->belongsTo(Status::class);
    }

    public function facilities(){
        return $this->belongsToMany(Facility::class, 'space_facilities')
            ->withPivot('detail')
            ->withTimestamps();
    }

    public function bookmarks(){
        return $this->hasMany(Bookmark::class);
    }

    public function rentRequests(){
        return $this->hasMany(RentRequest::class);
    }

    public function rents(){
        return $this->hasMany(Rent::class);
    }

    public function photos(){
        return $this->hasMany(SpacePhoto::class);
    }

  
}