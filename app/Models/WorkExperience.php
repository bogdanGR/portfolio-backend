<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkExperience extends Model
{
    use HasFactory;

    protected $table = 'work_experience';

    protected $fillable = [
        'job_title',
        'company_name',
        'company_website',
        'description',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    /**
     * Return duration of work experience
     * @return string
     */
    public function getDurationAttribute(): string
    {
        $start = $this->start_date?->format('M Y');
        $end   = $this->end_date?->format('M Y');
        return trim(($start ?? '') . ' — ' . ($end ?? ''));
    }
}
