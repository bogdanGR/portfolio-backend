<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    protected $primaryKey = 'id';

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
//          unlink(storage_path($file->path));
            Storage::disk('public')->delete($file->path);
        });
    }
}
