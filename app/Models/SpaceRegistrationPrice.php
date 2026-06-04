<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpaceRegistrationPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'space_registration_id',
        'pricing_type_id',
        'price'
    ];

    public function spaceRegistration(){
        return $this->belongsTo(SpaceRegistration::class);
    }

    public function pricingType(){
        return $this->belongsTo(PricingType::class);
    }
}