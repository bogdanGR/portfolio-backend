<?php

namespace Tests\Feature\Project;

use App\Models\Project;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_can_filter_projects_by_name()
    {
        Project::factory()->create(['name' => 'Laravel Project']);
        Project::factory()->create(['name' => 'Vue Project']);
        Project::factory()->create(['name' => 'React Project']);

        $response = $this->get(route('projects.index', ['name' => 'Laravel']));

        $response->assertInertia(fn ($page) =>
        $page->has('projects.data', 1)
            ->where('projects.data.0.name', 'Laravel Project')
        );
    }

    public function test_can_filter_projects_by_technology()
    {
        $php = Technology::factory()->create(['name' => 'PHP']);
        $js = Technology::factory()->create(['name' => 'JavaScript']);

        $project1 = Project::factory()->create();
        $project1->technologies()->attach($php);

        $project2 = Project::factory()->create();
        $project2->technologies()->attach($js);

        $response = $this->get(route('projects.index', ['technologies' => [$php->id]]));

        $response->assertInertia(fn ($page) =>
        $page->has('projects.data', 1)
        );
    }

    public function test_can_sort_projects()
    {
        Project::factory()->create(['name' => 'C Project']);
        Project::factory()->create(['name' => 'A Project']);
        Project::factory()->create(['name' => 'B Project']);

        $response = $this->get(route('projects.index', [
            'sort' => 'name',
            'direction' => 'asc'
        ]));

        $response->assertInertia(fn ($page) =>
        $page->where('projects.data.0.name', 'A Project')
            ->where('projects.data.1.name', 'B Project')
            ->where('projects.data.2.name', 'C Project')
        );
    }
}
