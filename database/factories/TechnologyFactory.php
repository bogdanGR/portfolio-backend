<?php

namespace Database\Factories;

use App\Enums\TechnologyCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TechnologyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->word();

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'category' => $this->faker->randomElement(TechnologyCategory::cases())->value,
        ];
    }

    public function backend(): static
    {
        return $this->state(fn () => ['category' => TechnologyCategory::BACKEND->value]);
    }

    public function frontend(): static
    {
        return $this->state(fn () => ['category' => TechnologyCategory::FRONTEND->value]);
    }

    public function tools(): static
    {
        return $this->state(fn () => ['category' => TechnologyCategory::TOOLS->value]);
    }
}
