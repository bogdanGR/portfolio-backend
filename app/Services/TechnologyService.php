<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Database\Eloquent\Model;

class TechnologyService
{
    /**
     * Sync technologies with proper sort order
     * @param Model $model
     * @param array $technologyIds
     * @return void
     */
    public function syncTechnologies(Model $model, array $technologyIds): void
    {
        $data = [];
        foreach ($technologyIds as $index => $techId) {
            $data[$techId] = ['sort_order' => $index + 1];
        }

        $model->technologies()->sync($data);
    }
}
