<?php

namespace Database\Factories;

use App\Models\File;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{
    protected $model = File::class;

    public function definition(): array
    {
        $filename = fake()->uuid() . '.jpg';

        return [
            'original_name' => fake()->word() . '.jpg',
            'filename' => $filename,
            'path' => 'projects/test/' . $filename,
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(1000, 500000),
            'type' => 'image',
        ];
    }
}
