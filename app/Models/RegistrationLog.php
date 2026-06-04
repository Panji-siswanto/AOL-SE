<?php

namespace App\Models;

use App\Models\SpaceRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'admin_id',
        'note',
    ];

    public function registration(){
        return $this->belongsTo(SpaceRegistration::class, 'registration_id');
    }

    public function admin(){
        return $this->belongsTo(User::class, 'admin_id');
    }
}