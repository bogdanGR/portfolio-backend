<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Expr\Cast\Bool_;

class File extends Model
{
    protected $fillable = [
        'original_name',
        'filename',
        'path',
        'mime_type',
        'size',
        'type',
        'alt_text'
    ];

    protected $appends = ['url'];
    protected $casts = ['meta' => 'array'];

    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }

    public function isImage()
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_files')
            ->withPivot(['sort_order', 'is_featured'])
            ->withTimestamps()
            ->orderBy('pivot_sort_order');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($file) {
            // Only delete file if not used elsewhere
            if ($file->projects()->count() === 0 && $file->blogs()->count() === 0) {
                Storage::delete($file->path);
            }
        });
    }
}
