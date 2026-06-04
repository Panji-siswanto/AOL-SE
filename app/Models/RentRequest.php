<?php

namespace App\Models;

use App\Models\Rent;
use App\Models\RentMessage;
use App\Models\Space;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'renter_id',
        'space_id',
        'start_date',
        'end_date',
        'visit_date',
        'status_id',
    ];

    public function renter(){
        return $this->belongsTo(User::class, 'renter_id');
    }

    public function space(){
        return $this->belongsTo(Space::class);
    }

    public function status(){
        return $this->belongsTo(Status::class);
    }

    public function rent(){
        return $this->hasOne(Rent::class, 'request_id');
    }

    public function messages(){
        return $this->hasMany(RentMessage::class, 'request_id');
    }
}