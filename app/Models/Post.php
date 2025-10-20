<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'featured_file_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'status',
        'is_featured',
        'reading_time',
        'view_count',
        'comments_enabled',
        'meta_title',
        'meta_description',
        'canonical_url',
        'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'comments_enabled' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function featuredFile()
    {
        return $this->belongsTo(File::class, 'featured_file_id');
    }

    // Scope for published posts
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
