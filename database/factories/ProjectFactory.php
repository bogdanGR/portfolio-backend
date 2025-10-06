<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'short_description' => fake()->sentence(10),
            'long_description' => fake()->paragraph(5),
            'link' => fake()->optional()->url(),
            'github' => fake()->optional()->url(),
        ];
    }

    public function withLink(): static
    {
        return $this->state(fn (array $attributes) => [
            'link' => fake()->url(),
        ]);
    }

    public function withGithub(): static
    {
        return $this->state(fn (array $attributes) => [
            'github' => 'https://github.com/' . fake()->userName() . '/' . fake()->word(),
        ]);
    }
}
