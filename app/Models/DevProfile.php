<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevProfile extends Model
{
    /**
     * responsible table of Model
     * @var string
     */
    protected $table = 'dev_profile';

    protected $fillable = [
        'job_title',
        'short_bio',
        'long_description',
        'professional_summary',
        'avatar_file_id',
        'resume_file_id',
        'email',
        'phone',
        'location',
        'github_url',
        'linkedin_url',
        'website_url',
        'years_experience',
        'university',
        'degree',
        'start_date_uni',
        'end_date_uni',
        'degree_url',
        'diploma_thesis_url',
        'languages',
    ];

    protected $casts = [
        'languages' => 'array',
    ];

     // load relations
    protected $with = ['avatar', 'resume'];

    /**
     * Relation with File table
     * @return BelongsTo
     */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(File::class, 'avatar_file_id');
    }

    /**
     * Relation with File table
     * @return BelongsTo
     */
    public function resume(): BelongsTo
    {
        return $this->belongsTo(File::class, 'resume_file_id');
    }

    /**
     * Current state of Dev Profile
     * @return mixed
     */
    public static function current()
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'job_title' => 'Software Engineer',
                'short_bio' => 'Short bio here',
                'professional_summary' => 'Short bio here',
                'long_description' => 'Your story here',
                'email' => 'bogdanvaskan450@gmail.com',
            ]
        );
    }
}
