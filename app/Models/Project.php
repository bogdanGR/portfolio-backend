<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Project Model
 *
 * Represents a project with associated files and technologies.
 *
 * @property int $id
 * @property string $name
 * @property string $short_description
 * @property string $long_description
 * @property string|null $link
 * @property string|null $github
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\File[] $files
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Technology[] $technologies
 * @property-read \App\Models\File|null $featured_image
 */
class Project extends Model
{
    use Filterable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['name', 'short_description', 'long_description', 'link', 'github'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array<string>
     */
    protected $with = ['files'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = ['featured_image'];

    /**
     * Get all files associated with this project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'project_files')
            ->withPivot(['sort_order', 'is_featured'])
            ->withTimestamps()
            ->orderBy('project_files.sort_order');
    }

    /**
     * Get only image files associated with this project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function images(): BelongsToMany
    {
        return $this->files()->where('type', 'image');
    }

    /**
     * Get the featured image for this project.
     * Returns the first image if no featured image is set.
     *
     * @return \App\Models\File|null
     */
    public function getFeaturedImageAttribute(): ?File
    {
        return $this->files()
            ->wherePivot('is_featured', true)
            ->first()  ?? $this->files()->first();
    }

    /**
     * Find a file by its ID that belongs to this project.
     *
     * @param int $fileId
     * @return \App\Models\File|null
     */
    public function findFileById(int $id): ?File
    {
        return  $this->files()->where(['project_files.id' => $id, 'project_files.project_id' => $this->id])->first();
    }

    /**
     * Get all technologies associated with this project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class)
            ->withPivot(['sort_order'])
            ->withTimestamps()
            ->orderBy('project_technology.sort_order');
    }
}
