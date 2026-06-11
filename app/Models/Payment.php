<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'rent_request_id',
        'amount',
        'method',
        'paid_at'
    ];
    protected $casts = ['paid_at' => 'datetime'];

    public function rentRequest() {
        return $this->belongsTo(RentRequest::class);
    }
}