<?php

namespace App\Repositories;

use App\Models\Certification;
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
}
