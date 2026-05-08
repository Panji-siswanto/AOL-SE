<?php
namespace App\Traits;
trait Filterable
{
    /**
     * Scope a query to filterby syatus
     */
    public function scopeWithStatus($query, $statusCode)
    {
        if ($statusCode) {
            $query->whereHas('status', function ($q) use ($statusCode) {
                $q->where('code', $statusCode);
            });
        }
    }

}