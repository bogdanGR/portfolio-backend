<?php

namespace App\Filters;

use Carbon\Carbon;

class WorkExperienceFilter extends QueryFilter
{

    /**
     * Filter by job_title
     */
    public function job_title($value): void
    {
        $this->builder->where('job_title', 'like', "%{$value}%");
    }

    /**
     * Filter by company_name
     */
    public function company_name($value): void
    {
        $this->builder->where('company_name', 'like', "%{$value}%");
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
        return ['job_title', 'company_name', 'start_date', 'end_date', 'created_at', 'updated_at'];
    }

    /**
     * Default sort column
     */
    protected function defaultSort(): string
    {
        return 'start_date';
    }

    /**
     * Get default sort direction
     */
    protected function defaultDirection(): string
    {
        return 'desc';
    }

}
