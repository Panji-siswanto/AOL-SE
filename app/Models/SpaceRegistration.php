<?php

namespace App\Models;

use App\Models\RegistrationLog;
use App\Models\Space;
use App\Traits\Filterable;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SpaceRegistration extends Model
{
    use HasFactory, Searchable, Filterable;

    protected $fillable = [
        'owner_id',
        'location_id',
        'name',
        'description',
        'length',
        'width',
        'area',
        'status_id',
    ];

    protected $searchable = [
        'name',
        'owner.name',
        'location.city',
        'location.province',
        'location.address',
    ];

    // public function promoteToSpace(): Space
    // {
    //     return DB::transaction(function () {
    //         $space = Space::create([
    //             'owner_id'        => $this->owner_id,
    //             'location_id'     => $this->location_id,
    //             'registration_id' => $this->id,
    //             'name'            => $this->name,
    //             'description'     => $this->description,
    //             'length'          => $this->length,
    //             'width'           => $this->width,
    //             'area'            => $this->area,
    //             'price'           => $this->prices()->min('price') ?? 0,
    //             'status_id'       => Status::where('code', 'spc_available')->value('id'),
    //         ]);

    //         $this->photos()->update(['space_id' => $space->id]);
    //         if (!$this->owner->hasRole('owner')) {
    //             $this->owner->assignRole('owner');
    //         }
    //         $this->update(['status_id' => Status::where('code', 'reg_approved')->value('id')]);

    //         return $space;
    //     });
    // }

    public function getFormattedSizeAttribute(){
        if ($this->length && $this->width) {
            $l = $this->length + 0;
            $w = $this->width + 0;
            return "{$l} x {$w} m";
        }
        $a = $this->area + 0;
        return "{$a} m²";
    }

    public function owner(){
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function location(){
        return $this->belongsTo(Location::class);
    }

    public function status(){
        return $this->belongsTo(Status::class);
    }

    public function space(){
        return $this->hasOne(Space::class, 'registration_id');
    }

    public function logs(){
        return $this->hasMany(RegistrationLog::class, 'registration_id');
    }

    public function documents(){
        return $this->hasMany(SpaceDocument::class);
    }

    public function photos(){
        return $this->hasMany(SpacePhoto::class);
    }

    public function prices(){
        return $this->hasMany(SpaceRegistrationPrice::class);
    }
}