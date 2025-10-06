<?php

namespace App\Models;

use App\Enums\TechnologyCategory;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use \Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Technology Model
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property TechnologyCategory $category
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Collection<int, Project> $projects
 */
class Technology extends Model
{
    use Filterable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'slug', 'category'];

    /**
     * Returns Projects related to technology
     * @return BelongsToMany
     */
    public function projects(): BelongsToMany
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

    /**
     * Return all technologies as assosiative array id,name
     * @return \Illuminate\Support\Collection
     */
    public static function getMappedTechnologies(): Collection
    {
        return self::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn($tech) => [
                'id' => (string) $tech->id,
                'name' => $tech->name,
            ]);
    }
}
