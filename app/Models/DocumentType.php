<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'context'];

    public const KTP = 1;
    public const SELFIE_KTP = 2;

    public const SURAT_TANAH = 3;
    public const SURAT_IZIN = 4;
    public const PERJANJIAN_SEWA = 5;
}