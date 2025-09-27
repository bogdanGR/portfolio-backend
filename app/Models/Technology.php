<?php

namespace App\Models;

use App\Enums\TechnologyCategory;
use Illuminate\Database\Eloquent\Model;

class Technology extends Model
{
    protected $fillable = ['name', 'slug', 'category'];

    /**
     * Returns Projects related to technology
     * @return Project
     */
    public function projects(): Project
    {
        return $this->belongsToMany(Project::class)
            ->withPivot(['sort_order'])
            ->withTimestamps();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => TechnologyCategory::class,
        ];
    }
}
