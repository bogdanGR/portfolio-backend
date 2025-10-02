<?php

namespace App\Filters;

class ProjectsFilter extends QueryFilter
{
    /**
     * Filter by name
     */
    public function name($value): void
    {
        $this->builder->where('name', 'like', "%{$value}%");
    }

    /**
     * Filter by short_description
     */
    public function short_description($value): void
    {
        $this->builder->where('short_description', 'like', "%{$value}%");
    }

    /**
     * Filter by link (Website URL column)
     * @param $value
     * @return void
     */
    public function link($value): void
    {
        $this->builder->where('link', 'like', "%{$value}%");
    }

    /**
     * Filter by github link
     * @param $value
     * @return void
     */
    public function github($value): void
    {
        $this->builder->where('github', 'like', "%{$value}%");
    }

    /**
     * Sort by column
     */
    public function sort($value): void
    {
        $direction = $this->request->input('direction', 'asc');

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $this->applySort($value, $direction);
    }

    /**
     * Allowed columns for sorting
     */
    protected function allowedSorts(): array
    {
        return ['name', 'short_description', 'link', 'github', 'created_at', 'updated_at'];
    }

    /**
     * Default sort column
     */
    protected function defaultSort(): string
    {
        return 'name';
    }
}
