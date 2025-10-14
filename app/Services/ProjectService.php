<?php

namespace App\Services;

use App\Models\File;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectService
{
    public function __construct(
        private readonly FileService $fileService,
        private readonly TechnologyService $technologyService
    ) {}

    /**
     * Handles create of project
     * @param array $data
     * @param array|null $images
     * @return Project
     * @throws \Throwable
     */
    public function createProject(array $data, ?array $images = null): Project
    {
        try {
            return DB::transaction(function () use ($data, $images) {
                $project = Project::create([
                    'name' => $data['name'],
                    'short_description' => $data['short_description'],
                    'long_description' => $data['long_description'],
                    'link' => $data['link'] ?? null,
                    'github' => $data['github'] ?? null,
                ]);

                if ($images) {
                    $this->fileService->upload($project, $images);
                }

                $this->technologyService->syncTechnologies($project, $data['technology_ids'] ?? []);

                return $project->fresh(['files', 'technologies']);
            });
        } catch (\Exception $e) {
            Log::error('Project creation failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Handles update of project
     * @param Project $project
     * @param array $data
     * @param array|null $images
     * @return Project
     * @throws \Throwable
     */
    public function updateProject(Project $project, array $data, ?array $images = null): Project
    {
        try {
            return DB::transaction(function () use ($project, $data, $images) {
                $project->update([
                    'name' => $data['name'],
                    'short_description' => $data['short_description'],
                    'long_description' => $data['long_description'],
                    'link' => $data['link'] ?? null,
                    'github' => $data['github'] ?? null,
                ]);

                $this->technologyService->syncTechnologies($project, $data['technology_ids'] ?? []);

                if ($images) {
                    $this->fileService->upload($project, $images);
                }

                return $project->fresh(['files', 'technologies']);
            });
        } catch (\Exception $e) {
            Log::error('Project update failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            throw $e;
        }
    }

    /**
     * Detach an image from project
     * @param Project $project
     * @param int $fileId
     * @return void
     * @throws \Throwable
     */
    public function detachImage(Project $project, int $fileId): void
    {
        try {
            DB::transaction(function () use ($project, $fileId) {
                $file = File::findOrFail($fileId);
                $this->fileService->deleteFile($file);

                $project->files()->detach($fileId);
            });
        } catch (\Exception $e) {
            Log::error('Failed to detach image', [
                'project_id' => $project->id,
                'file_id' => $fileId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Set featured image for project
     * @param Project $project
     * @param int $fileId
     * @return void
     * @throws \Throwable
     */
    public function setFeaturedImage(Project $project, int $fileId): void
    {
        try {
            DB::transaction(function () use ($project, $fileId) {
                // Remove featured flag from all images
                $project->files()->updateExistingPivot(
                    $project->files->pluck('id'),
                    ['is_featured' => false]
                );

                // Set the new featured image
                $project->files()->updateExistingPivot($fileId, ['is_featured' => true]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to set featured image', [
                'project_id' => $project->id,
                'file_id' => $fileId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Reorder project images
     * @param Project $project
     * @param array $fileIds
     * @return void
     * @throws \Throwable
     */
    public function reorderImages(Project $project, array $fileIds): void
    {
        try {
            DB::transaction(function () use ($project, $fileIds) {
                foreach ($fileIds as $index => $fileId) {
                    $project->files()->updateExistingPivot($fileId, [
                        'sort_order' => $index + 1
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::error('Failed to reorder images', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
