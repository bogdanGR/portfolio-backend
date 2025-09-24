<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name', 'short_description', 'long_description', 'link', 'github'];

    protected $with = ['files'];

    public function files()
    {
        return $this->belongsToMany(File::class, 'project_files')
            ->withPivot(['sort_order', 'is_featured'])
            ->withTimestamps()
            ->orderBy('pivot_sort_order');
    }

    public function images()
    {
        return $this->files()->where('type', 'image');
    }

    public function getFeaturedImageAttribute()
    {
        return $this->files()->wherePivot('is_featured', true)->first()
            ?? $this->files()->first();
    }

    public function uploadFiles(array $files, string $type = 'image')
    {
        $uploadedFiles = [];
        $maxSortOrder = $this->files()->max('project_files.sort_order') ?? 0;

        foreach ($files as $index => $file) {
            if ($file instanceof UploadedFile) {
                $uploadedFile = $this->uploadSingleFile($file, $type, $maxSortOrder + $index + 1);
                $uploadedFiles[] = $uploadedFile;
            }
        }

        return $uploadedFiles;
    }

    public function uploadSingleFile(UploadedFile $uploadedFile, string $type = 'image', int $sortOrder = null)
    {
        // Check if file already exists (by hash or name)
        $filename = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
        $directory = 'projects/' . $this->id;
        $path = $uploadedFile->storeAs($directory, $filename, 'public');

        // Create file record
        $file = File::create([
            'original_name' => $uploadedFile->getClientOriginalName(),
            'filename' => $filename,
            'path' => $path,
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'type' => $type
        ]);

        // Attach to project
        $this->files()->attach($file->id, [
            'sort_order' => $sortOrder ?? ($this->files()->max('project_files.sort_order') + 1),
            'is_featured' => $this->files()->count() === 0 // First image is featured
        ]);

        return $file;
    }

    public function detachFile($fileId)
    {
        return $this->files()->detach($fileId);
    }

    public function setFeaturedImage($fileId)
    {
        // Remove featured from all
        $this->files()->updateExistingPivot(
            $this->files()->pluck('files.id')->toArray(),
            ['is_featured' => false]
        );

        // Set new featured
        return $this->files()->updateExistingPivot($fileId, ['is_featured' => true]);
    }

    public function reorderFiles(array $fileIds): void
    {
        foreach ($fileIds as $index => $fileId) {
            $this->files()->updateExistingPivot($fileId, ['sort_order' => $index + 1]);
        }
    }
}
