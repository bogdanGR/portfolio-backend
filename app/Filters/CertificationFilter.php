<?php

namespace App\Filters;

class CertificationFilter extends QueryFilter
{
    /**
     * Filter by id
     * @param $value
     */
    public function id($value): void
    {
        $this->builder->where('id', '=', $value);
    }

    /**
     * Filter by name
     * @param $value
     */
    public function name($value): void
    {
        $this->builder->where('name', 'like', "%{$value}%");
    }

    /**
     * Filter by issuing_organization
     * @param $value
     */
    public function issuing_organization($value): void
    {
        $this->builder->where('issuing_organization', 'like', "%{$value}%");
    }

    /**
     * Filter by issue_date start (greater than or equal)
     * @param $value
     */
    public function issue_date_start($value): void
    {
        if (!empty($value)) {
            $this->builder->where('issue_date', '>=', $value);
        }
    }

    /**
     * Filter by issue_date end (less than or equal)
     * @param $value
     */
    public function issue_date_end($value): void
    {
        if (!empty($value)) {
            $this->builder->where('issue_date', '<=', $value);
        }
    }

    /**
     * Filter by expiration_date start (greater than or equal)
     * @param $value
     */
    public function expiration_date_start($value): void
    {
        if (!empty($value)) {
            $this->builder->where('expiration_date', '>=', $value);
        }
    }

    /**
     * Filter by expiration_date end (less than or equal)
     * @param $value
     */
    public function expiration_date_end($value): void
    {
        if (!empty($value)) {
            $this->builder->where('expiration_date', '<=', $value);
        }
    }

    /**
     * Filter by credential_id
     * @param $value
     */
    public function credential_id($value): void
    {
        $this->builder->where('credential_id', 'like', "%{$value}%");
    }

    /**
     * Filter by credential_id
     * @param $value
     */
    public function credential_url($value): void
    {
        $this->builder->where('credential_url', 'like', "%{$value}%");
    }

    /**
     * Filter by technologies (accepts comma-separated IDs or array)
     * @param $value
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
     * @param $value
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
     * @param string $direction
     */
    protected function sortByTechnology(string $direction): void
    {
        $this->builder->orderBy(
            \DB::table('certification_technology')
                ->join('technologies', 'certification_technology.technology_id', '=', 'technologies.id')
                ->select('technologies.name')
                ->whereColumn('certification_technology.certification_id', 'certifications.id')
                ->orderBy('certification_technology.sort_order')
                ->limit(1),
            $direction
        );
    }

    /**
     * Allowed columns for sorting
     * @return array
     */
    protected function allowedSorts(): array
    {
        return ['id','name', 'issuing_organization', 'issue_date', 'expiration_date', 'credential_id', 'credential_url', 'technology', 'created_at', 'updated_at'];
    }
}
