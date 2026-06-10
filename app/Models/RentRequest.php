<?php

namespace App\Models;

use App\Models\Rent;
use App\Models\RentMessage;
use App\Models\Space;
use App\Models\Status;
use App\Models\User;
use App\Traits\Filterable;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentRequest extends Model
{
    use HasFactory, Filterable, Searchable;

    protected $fillable = [
        'renter_id', 
        'space_id',
        'start_date',
        'end_date', 
        'visit_date', 
        'total_price', 
        'price_breakdown', 
        'status_id'
    ];

    protected $searchable = [
        'space.name',
        'space.location.city',
        'space.location.address'
    ];

    protected $casts = [
        'price_breakdown' => 'array',
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
        return $this->hasMany(RentMessage::class, 'request_id')->latest();
    }

    public function reschedules()
    {
        return $this->hasMany(RentReschedule::class, 'rent_request_id');
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->pricing || !$this->pricing->price) {
            return null;
        }
        return (int) round($this->total_price / $this->pricing->price);
    }

    public function getDurationUnitAttribute(): string
    {
        $type = $this->pricing->pricingType->code ?? null;
        return match ($type) {
            'daily' => 'day',
            'weekly' => 'week',
            'monthly' => 'month',
            default => 'unit',
        };
    }
}