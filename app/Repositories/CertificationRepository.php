<?php

namespace App\Repositories;

use App\Models\Certification;
use App\Models\Technology;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CertificationRepository
{

    /**
     * Return projects with pagination
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search($filter, int $perPage = 10): LengthAwarePaginator
    {
        return Certification::filter($filter)
            ->with([
                'technologies' => fn ($q) => $q->orderBy('certification_technology.sort_order'),
            ])
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get data needed for edit form
     * @param Certification $certification
     * @return array
     */
    public function getEditData(Certification $certification): array
    {
        $certification->load([
            'technologies:id,name',
            'certificationImage',
        ]);

        $technologiesAll = Technology::select('id', 'name', 'slug', 'category')
            ->orderBy('name')
            ->get();

        $technologySelectedIds = $certification->technologies
            ->pluck('id')
            ->values()
            ->all();

        return [
            'certification' => $certification,
            'technologiesAll' => $technologiesAll,
            'technologySelectedIds' => $technologySelectedIds,
        ];
    }
}
