<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\Technology;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProjectRepository
{
    /**
     * Return projects with pagination
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateWithRelations(int $perPage = 10): LengthAwarePaginator
    {
        return Project::query()
            ->with([
                'files' => fn ($q) => $q->orderBy('project_files.sort_order'),
                'technologies' => fn ($q) => $q->orderBy('project_technology.sort_order'),
            ])
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get all projects with related data
     * @return Collection
     */
    public function getAllWithRelations(): Collection
    {
        return Project::with([
            'files' => fn($q) => $q->orderBy('project_files.sort_order'),
            'technologies' => fn($q) => $q->orderBy('project_technology.sort_order')
        ])->get();
    }

    /**
     * Get all technologies for dropdown/select
     * @return Collection
     */
    public function getAllTechnologies(): Collection
    {
        return Technology::all();
    }

    /**
     * Get data needed for edit form
     * @param Project $project
     * @return array
     */
    public function getEditData(Project $project): array
    {
        $project->load([
            'technologies:id,name',
            'files' => fn($q) => $q->orderBy('project_files.sort_order'),
        ]);

        $technologiesAll = Technology::select('id', 'name', 'slug', 'category')
            ->orderBy('name')
            ->get();

        $technologySelectedIds = $project->technologies
            ->pluck('id')
            ->values()
            ->all();

        return [
            'project' => $project,
            'technologiesAll' => $technologiesAll,
            'technologySelectedIds' => $technologySelectedIds,
        ];
    }

    /**
     * Find project with relations or fail
     * @param int $id Project id
     * @return Project
     */
    public function findWithRelations(int $id): Project
    {
        return Project::with(['technologies:id,name', 'files'])
            ->findOrFail($id);
    }
}
