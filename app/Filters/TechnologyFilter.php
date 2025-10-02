<?php

namespace App\Filters;


class TechnologyFilter extends QueryFilter
{
    /**
     * Filter by name
     */
    public function name($value): void
    {
        $this->builder->where('name', 'like', "%{$value}%");
    }

    /**
     * Filter by slug
     */
    public function slug($value): void
    {
        $this->builder->where('slug', 'like', "%{$value}%");
    }

    /**
     * Filter by category
     */
    public function category($value): void
    {
        $this->builder->where('category', $value);
    }

    /**
     * Sort by column
     */
    public function sort($value): void
    {
        $direction = $this->request->input('direction', $this->defaultDirection());

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = $this->defaultDirection();
        }

        $this->applySort($value, $direction);
    }

    /**
     * Allowed columns for sorting
     */
    protected function allowedSorts(): array
    {
        return ['name', 'slug', 'category', 'created_at', 'updated_at'];
    }

    /**
     * Default sort column
     */
    protected function defaultSort(): string
    {
        return 'name';
    }
}
