<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingType extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name'];

    public function spaceRegistrationPrices()
    {
        return $this->hasMany(SpaceRegistrationPrice::class);
    }
}