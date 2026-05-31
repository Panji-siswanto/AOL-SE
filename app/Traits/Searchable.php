<?php

namespace App\Traits;

trait Searchable
{
    /**
     * Scope a query to search dynamically based on the model's $searchable array.
     */
    public function scopeSearch($query, $term)
    {
        // If there is no search term, or the model hasn't defined searchable fields, just return.
        if (!$term || empty($this->searchable)) {
            return $query;
        }

        $query->where(function ($q) use ($term) {
            foreach ($this->searchable as $field) {
                if (str_contains($field, '.')) {
                    // If the field is a relationship (e.g., 'location.city' or 'user.name')
                    [$relation, $column] = explode('.', $field);
                    
                    $q->orWhereHas($relation, function ($relQuery) use ($term, $column) {
                        $relQuery->where($column, 'like', "%{$term}%");
                    });
                } else {
                    // If the field is a direct column on the table (e.g., 'name' or 'description')
                    $q->orWhere($field, 'like', "%{$term}%");
                }
            }
        });

        return $query;
    }
}