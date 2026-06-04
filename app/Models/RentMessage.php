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
        'proposed_start_date',
        'proposed_end_date',
        'proposed_visit_date',
        'note',
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
}