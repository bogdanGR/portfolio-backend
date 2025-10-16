<?php

namespace App\Filters;

class CertificationFilter extends QueryFilter
{
    /**
     * Filter by id
     */
    public function id($value): void
    {
        $this->builder->where('id', '=', $value);
    }

    /**
     * Filter by name
     */
    public function name($value): void
    {
        $this->builder->where('name', 'like', "%{$value}%");
    }

    /**
     * Filter by issuing_organization
     */
    public function issuing_organization($value): void
    {
        $this->builder->where('issuing_organization', 'like', "%{$value}%");
    }

    /**
     * Filter by credential_id
     */
    public function credential_id($value): void
    {
        $this->builder->where('credential_id', 'like', "%{$value}%");
    }

    /**
     * Filter by credential_id
     */
    public function credential_url($value): void
    {
        $this->builder->where('credential_url', 'like', "%{$value}%");
    }

    /**
     * Filter by technologies (accepts comma-separated IDs or array)
     */
    public function technologies($value): void
    {
        // Handle comma-separated string or array
        $technologyIds = is_array($value) ? $value : explode(',', $value);

        // Filter to only numeric IDs
        $technologyIds = array_filter($technologyIds, fn($id) => is_numeric($id));

        if (empty($technologyIds)) {
            return;
        }

        $this->builder->whereHas('technologies', function ($query) use ($technologyIds) {
            $query->whereIn('technologies.id', $technologyIds);
        });
    }

    /**
     * Sort by column
     */
    public function sort($value): void
    {
        $direction = $this->request->input('direction', 'asc');

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = $this->defaultDirection();
        }

        if ($value === 'technology') {
            $this->sortByTechnology($direction);
            return;
        }

        $this->applySort($value, $direction);
    }

    /**
     * Sort by the first technology name
     */
    protected function sortByTechnology(string $direction): void
    {
        $this->builder->orderBy(
            \DB::table('project_technology')
                ->join('technologies', 'project_technology.technology_id', '=', 'technologies.id')
                ->select('technologies.name')
                ->whereColumn('project_technology.project_id', 'projects.id')
                ->orderBy('project_technology.sort_order')
                ->limit(1),
            $direction
        );
    }

    /**
     * Allowed columns for sorting
     */
    protected function allowedSorts(): array
    {
        return ['id','name', 'issuing_organization', 'issue_date', 'expiration_date', 'credential_id', 'credential_url', 'technology', 'created_at', 'updated_at'];
    }
}
