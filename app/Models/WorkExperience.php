<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class WorkExperience extends Model
{
    use HasFactory, Filterable;

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

    protected $appends = ['formatted_start_date', 'formatted_end_date'];

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

    /**
     * Returns formatted start date
     * @return string
     */
    public function getFormattedStartDateAttribute(): string
    {
        return Carbon::parse($this->start_date)->format('d/m/Y');
    }

    /**
     * Returns formatted end date
     * @return string
     */
    public function getFormattedEndDateAttribute(): string
    {
        return Carbon::parse($this->end_date)->format('d/m/Y');
    }
}
