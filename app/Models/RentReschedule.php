<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RentReschedule extends Model
{
    protected $fillable = [
        'rent_message_id', 'proposed_start_date', 'proposed_end_date', 'proposed_visit_date'
    ];

    public function message()
    {
        return $this->belongsTo(RentMessage::class, 'rent_message_id');
    }
}