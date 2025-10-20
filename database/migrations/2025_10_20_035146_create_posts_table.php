<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->enum('status', ['draft', 'scheduled', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->foreignId('featured_file_id')->nullable()->constrained('files')->onDelete('set null');
            $table->integer('reading_time')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->boolean('comments_enabled')->default(true);
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // indexes
            $table->index(['status', 'published_at']);
            $table->index('user_id');
            $table->index('category_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
