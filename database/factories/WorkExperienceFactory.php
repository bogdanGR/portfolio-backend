<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WorkExperienceFactory extends Factory
{
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-10 years', '-1 year');
        $end   = $this->faker->dateTimeBetween($start, 'now');

        return [
            'job_title'       => $this->faker->jobTitle(),
            'company_name'    => $this->faker->company(),
            'company_website' => $this->faker->boolean(70) ? $this->faker->url() : null,
            'description'     => $this->faker->paragraphs(3, true),
            'start_date'      => $start->format('Y-m-d'),
            'end_date'        => $end->format('Y-m-d'),
        ];
    }
}
