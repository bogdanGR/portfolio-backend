<?php

namespace App\Actions;

use App\Models\Project;
use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteProjectAction
{
    public function __construct(
        private readonly FileService $fileService
    ) {}
    public function execute(Project $project): void
    {
        try {
            DB::transaction(function () use ($project) {
                // Get files before deletion
                $files = $project->files;

                // Delete the project (cascade deletes pivot records)
                $project->delete();

                // Clean up orphaned files
                $this->fileService->cleanupOrphanedFiles($files);
            });
        } catch (\Exception $e) {
            Log::error('Project deletion failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
