<?php

namespace App\Models;

use App\Models\Space;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'space_id',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function space(){
        return $this->belongsTo(Space::class);
    }
}