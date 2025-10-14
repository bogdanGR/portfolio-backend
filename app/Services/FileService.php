<?php

namespace App\Services;

use App\Models\File;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    /**
     * Upload files with flexible relationship handling
     *
     * @param Model $model
     * @param array $uploadedFiles
     * @param array $options
     * @return array
     */
    public function upload(Model $model, array $uploadedFiles, array $options = []): array
    {
        $config = array_merge([
            'type' => 'image',
            'directory' => $this->getDirectory($model),
            'relationship_type' => 'many_to_many',
            'relationship_name' => 'files',
            'foreign_key' => null,
            'with_sort_order' => true,
            'with_featured' => true,
            'disk' => 'public',
        ], $options);

        $uploadedFileRecords = [];

        foreach ($uploadedFiles as $index => $uploadedFile) {
            $file = $this->storeFile($uploadedFile, $config['directory'], $config['type'], $config['disk']);
            $uploadedFileRecords[] = $file;

            if ($config['relationship_type'] === 'many_to_many') {
                $this->attachManyToMany($model, $file, $index, $config);
            } elseif ($config['relationship_type'] === 'one_to_one') {
                $this->attachOneToOne($model, $file, $config);
                break; // Only one file for 1:1 relationships
            }
        }

        return $uploadedFileRecords;
    }

    /**
     * Store the uploaded file
     */
    protected function storeFile(UploadedFile $uploadedFile, string $directory, string $type, string $disk): File
    {
        $filename = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
        $path = $uploadedFile->storeAs($directory, $filename, $disk);

        return File::create([
            'original_name' => $uploadedFile->getClientOriginalName(),
            'filename' => $filename,
            'path' => $path,
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'type' => $type
        ]);
    }

    /**
     * Attach file with many-to-many relationship
     */
    protected function attachManyToMany(Model $model, File $file, int $index, array $config): void
    {
        $pivotData = [
            'created_at' => now(),
            'updated_at' => now()
        ];

        if ($config['with_sort_order']) {
            $maxSortOrder = $model->{$config['relationship_name']}()->max('sort_order') ?? 0;
            $pivotData['sort_order'] = $maxSortOrder + $index + 1;
        }

        if ($config['with_featured']) {
            $isFirstImage = $model->{$config['relationship_name']}()->count() === 0;
            $pivotData['is_featured'] = $isFirstImage && $index === 0;
        }

        $model->{$config['relationship_name']}()->attach($file->id, $pivotData);
    }

    /**
     * Attach file with one-to-one relationship
     */
    protected function attachOneToOne(Model $model, File $file, array $config): void
    {
        if (!$config['foreign_key']) {
            throw new \InvalidArgumentException('foreign_key is required for one_to_one relationships');
        }

        // Delete old file if exists
        if ($model->{$config['foreign_key']}) {
            $oldFile = File::find($model->{$config['foreign_key']});
            if ($oldFile) {
                $this->deleteFile($oldFile);
            }
        }

        $model->update([
            $config['foreign_key'] => $file->id
        ]);
    }

    /**
     * Delete file from storage and database
     */
    public function deleteFile(File $file, string $disk = 'public'): void
    {
        \Storage::disk($disk)->delete($file->path);
        $file->delete();
    }

    /**
     * Get directory path for the model
     */
    protected function getDirectory(Model $model): string
    {
        $tableName = $model->getTable();
        return $tableName . '/' . $model->id;
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
