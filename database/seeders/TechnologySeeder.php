<?php

namespace Database\Seeders;

use App\Enums\TechnologyCategory;
use App\Models\Technology;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TechnologySeeder extends Seeder
{
    public function run(): void
    {
        $technologies = [
            ['name' => 'Vue.js', 'category' => TechnologyCategory::FRONTEND],
            ['name' => 'Laravel', 'category' => TechnologyCategory::BACKEND],
            ['name' => 'Tailwind CSS', 'category' => TechnologyCategory::FRONTEND],
            ['name' => 'MySQL', 'category' => TechnologyCategory::BACKEND],
            ];

        foreach ($technologies as $t) {
            Technology::firstOrCreate(
                ['slug' => Str::slug($t['name'])],
                ['name' => $t['name'], 'category' => $t['category']]
            );
        }
    }
}
