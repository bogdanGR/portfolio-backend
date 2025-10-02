<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class QueryFilter
{
    protected Request $request;
    protected Builder $builder;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        foreach ($this->filters() as $filter => $value) {
            if (method_exists($this, $filter) && $this->hasValue($filter)) {
                $this->$filter($value);
            }
        }

        return $this->builder;
    }

    public function filters(): array
    {
        return $this->request->all();
    }

    protected function hasValue(string $filter): bool
    {
        return $this->request->filled($filter);
    }

    /**
     * Get allowed sorts for this filter
     */
    abstract protected function allowedSorts(): array;

    /**
     * Get default sort column
     */
    protected function defaultSort(): string
    {
        return 'id';
    }

    /**
     * Apply sorting
     */
    protected function applySort(string $column, string $direction = 'asc'): void
    {
        if (in_array($column, $this->allowedSorts())) {
            $this->builder->orderBy($column, $direction);
        }
    }
}
