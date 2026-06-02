<?php

namespace App\Traits;

trait Searchable
{
    /**
     * Scope a query to search dynamically based on the model's $searchable array.
     */
    public function scopeSearch($query, $term)
    {
        if (!$term || empty($this->searchable)) {
            return $query;
        }

        $query->where(function ($q) use ($term) {
            $relations = [];

            foreach ($this->searchable as $field) {
                if (str_contains($field, '.')) {
                    // Group relation columns together (e.g., location => [city, province, address])
                    [$relation, $column] = explode('.', $field);
                    $relations[$relation][] = $column;
                } else {
                    // Direct table column
                    $q->orWhere($field, 'like', "%{$term}%");
                }
            }

            // Execute a single orWhereHas per relationship
            foreach ($relations as $relation => $columns) {
                $q->orWhereHas($relation, function ($relQuery) use ($columns, $term) {
                    $relQuery->where(function ($subQuery) use ($columns, $term) {
                        foreach ($columns as $column) {
                            $subQuery->orWhere($column, 'like', "%{$term}%");
                        }
                    });
                });
            }
        });

        return $query;
    }
}