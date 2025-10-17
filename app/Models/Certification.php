<?php

namespace App\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Project Model
 *
 * Represents a project with associated files and technologies.
 *
 * @property int $id
 * @property string $name
 * @property string $issuing_organization
 * @property Date $issue_date
 * @property Date|null $expiration_date
 * @property string|null $credential_id
 * @property string $credential_url
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\File[] $certificationImage
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Technology[] $technologies
 */
class Certification extends Model
{
    protected $table = 'certifications';

    protected $fillable = [
        'name',
        'issuing_organization',
        'issue_date',
        'expiration_date',
        'credential_id',
        'credential_url',
        'certification_image_id',
    ];

    protected $casts = [
        'issue_date' => 'date:Y-m-d',
        'expiration_date'   => 'date:Y-m-d',
    ];

    protected $appends = ['formatted_issue_date', 'formatted_expiration_date',];

    /**
     * The relationships that should always be loaded.
     *
     * @var array<string>
     */
    protected $with = ['certificationImage', 'technologies'];

    /**
     * Get all files associated with this project.
     *
     * @return BelongsTo
     */
    public function certificationImage(): BelongsTo
    {
        return $this->belongsTo(File::class, 'certification_image_id');
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
            ->orderBy('certification_technology.sort_order');
    }

    /**
     * Returns formatted start date
     * @return string
     */
    public function getFormattedIssueDateAttribute(): string
    {
        return Carbon::parse($this->issue_date)->format('d/m/Y');
    }

    /**
     * Returns formatted end date
     * @return string
     */
    public function getFormattedExpirationDateAttribute(): string
    {
        return Carbon::parse($this->expiration_date)->format('d/m/Y');
    }
}
