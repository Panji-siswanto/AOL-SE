<?php
namespace App\Traits;
trait Searchable
{
    /**
     * Scope a query to search by name, owner name, or location.
     */
    public function scopeSearch($query, $term)
    {
        if ($term) {
            $query->where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhereHas('owner', function($ownerQuery) use ($term) {
                      $ownerQuery->where('name', 'like', "%{$term}%");
                  })
                  ->orWhereHas('location', function($locationQuery) use ($term) {
                        $locationQuery->where('city', 'like', "%{$term}%")
                                    ->orWhere('province', 'like', "%{$term}%")
                                    ->orWhere('address', 'like', "%{$term}%"); 

                    });
            });
        }
    }
}