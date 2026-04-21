<?php

namespace App\Models;

use App\Models\RegistrationLog;
use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpaceRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'location_id',
        'name',
        'description',
        'size',
        'price',
        'status_id',
    ];

    // belongs to user (owner)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // belongs to location
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // belongs to status
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    // one registration becomes one space
    public function space()
    {
        return $this->hasOne(Space::class, 'registration_id');
    }

    // logs
    public function logs()
    {
        return $this->hasMany(RegistrationLog::class, 'registration_id');
    }
}
