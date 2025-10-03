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
        return ['name', 'short_description', 'link', 'github', 'technology', 'created_at', 'updated_at'];
    }

    /**
     * Default sort column
     */
    protected function defaultSort(): string
    {
        return 'name';
    }
}
