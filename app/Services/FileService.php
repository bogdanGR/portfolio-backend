<?php

namespace App\Services;

use App\Models\File;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    /**
     * Handle uploading multiple images for a project
     * @param Project $project
     * @param array $uploadedFiles
     * @return void
     */
    public function handleImageUploads(Project $project, array $uploadedFiles)
    {
        $maxSortOrder = $project->files()->max('project_files.sort_order') ?? 0;
        $isFirstImage = $project->files()->count() === 0;

        foreach ($uploadedFiles as $index => $uploadedFile) {
            $filename = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
            $directory = 'projects/' . $project->id;
            $path = $uploadedFile->storeAs($directory, $filename, 'public');

            $file = File::create([
                'original_name' => $uploadedFile->getClientOriginalName(),
                'filename' => $filename,
                'path' => $path,
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
                'type' => 'image'
            ]);

            $project->files()->attach($file->id, [
                'sort_order' => $maxSortOrder + $index + 1,
                'is_featured' => $isFirstImage && $index === 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Remove a file from storage and database
     * @param Project $project
     * @param int $fileId
     * @return void
     */
    public function removeFile(Project $project, int $fileId): void
    {
        $file = File::findOrFail($fileId);

        // Check if file is used by other projects
        if ($file->projects()->where('projects.id', '!=', $project->id)->count() === 0) {
            // Delete from storage
            Storage::delete($file->path);
            // Delete from database
            $file->delete();
        }
    }

    /**
     * Cleanup orphaned files after project deletion
     * @param iterable $files
     * @return void
     */
    public function cleanupOrphanedFiles(iterable $files): void
    {
        foreach ($files as $file) {
            if ($file->projects()->count() === 0) {
                Storage::delete($file->path);
                $file->delete();
            }
        }
    }
}
