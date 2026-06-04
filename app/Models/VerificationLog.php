<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationLog extends Model
{
    use HasFactory, Filterable, Searchable;

    protected $fillable = [
        'user_id',
        'admin_id',
        'status_id',
        'note',
    ];

    protected $searchable = [
        'user.name',
        'user.email',
        'note',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function admin(){
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function status(){
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function documents(){
        return $this->hasMany(VerificationDocument::class, 'logs_id');
    }
}