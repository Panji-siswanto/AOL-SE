<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RentReschedule extends Model
{
    protected $fillable = [
        'rent_request_id', 
        'sender_id',    
        'proposed_visit_date', 
        'proposed_start_date', 
        'proposed_end_date'
    ];

    public function request()
    {
        return $this->belongsTo(RentRequest::class, 'rent_request_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}