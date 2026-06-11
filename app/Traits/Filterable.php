<?php
namespace App\Traits;

trait Filterable
{
    public function scopeWithStatus($query, $statusCode)
    {
        if ($statusCode && $statusCode !== 'all') {
            $query->whereHas('status', function ($q) use ($statusCode) {
                $q->where('code', $statusCode);
            });
        }
        return $query;
    }
}