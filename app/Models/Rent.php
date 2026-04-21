<?php

namespace App\Models;

use App\Models\RentRequest;
use App\Models\Space;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rent extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'space_id',
        'renter_id',
        'start_date',
        'end_date',
        'status_id',
    ];

    public function request()
    {
        return $this->belongsTo(RentRequest::class, 'request_id');
    }

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function renter()
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}