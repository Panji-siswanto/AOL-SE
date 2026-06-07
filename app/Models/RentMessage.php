<?php

namespace App\Models;

use App\Models\RentRequest;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id', 
        'sender_id', 
        'type_id', 
        'message'
    ];

    public function request(){
        return $this->belongsTo(RentRequest::class, 'request_id');
    }

    public function sender(){
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function type(){
        return $this->belongsTo(Status::class, 'type_id');
    }

    public function reschedule()
    {
        return $this->hasOne(RentReschedule::class, 'rent_message_id');
    }
}