<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class QueryFilter
{
    protected Request $request;
    protected Builder $builder;

    /**
     * Filters that should be skipped during automatic application
     */
    protected array $skipFilters = ['direction'];
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        foreach ($this->filters() as $filter => $value) {
            if (in_array($filter, $this->skipFilters)) {
                continue;
            }

            if (method_exists($this, $filter) && $this->hasValue($filter)) {
                $this->$filter($value);
            }
        }

        if (!$this->request->has('sort')) {
            $this->builder->orderBy($this->defaultSort(), $this->defaultDirection());
        }

        return $this->builder;
    }

    public function filters(): array
    {
        return $this->request->all();
    }

    protected function hasValue(string $filter): bool
    {
        $value = $this->request->input($filter);

        if (is_array($value)) {
            return !empty($value);
        }

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
     * Get default sort direction
     */
    protected function defaultDirection(): string
    {
        return 'asc';
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
