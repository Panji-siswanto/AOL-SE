<?php

namespace App\Traits;

trait Searchable
{
    public function scopeSearch($query, $term)
    {
        if (!$term || empty($this->searchable)) {
            return $query;
        }

        $query->where(function ($q) use ($term) {
            $relations = [];

            foreach ($this->searchable as $field) {
                if (str_contains($field, '.')) {
                    $lastDotPosition = strrpos($field, '.');
                    $relation = substr($field, 0, $lastDotPosition);
                    $column = substr($field, $lastDotPosition + 1);
                    
                    $relations[$relation][] = $column;
                } else {
                    $q->orWhere($field, 'like', "%{$term}%");
                }
            }

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