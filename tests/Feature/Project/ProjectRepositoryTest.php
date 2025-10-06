<?php

namespace Feature\Project;

use App\Models\Project;
use App\Models\Technology;
use App\Repositories\ProjectRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProjectRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProjectRepository();
    }

    public function test_can_get_all_projects_with_relations()
    {
        Project::factory()->count(3)->create();

        $projects = $this->repository->getAllWithRelations();

        $this->assertCount(3, $projects);
        $this->assertTrue($projects->first()->relationLoaded('files'));
        $this->assertTrue($projects->first()->relationLoaded('technologies'));
    }

    public function test_can_get_all_technologies()
    {
        Technology::factory()->count(5)->create();

        $technologies = $this->repository->getAllTechnologies();

        $this->assertCount(5, $technologies);
    }

    public function test_can_get_edit_data()
    {
        $project = Project::factory()->create();
        $technologies = Technology::factory()->count(3)->create();
        $project->technologies()->attach($technologies->pluck('id'));

        $data = $this->repository->getEditData($project);

        $this->assertArrayHasKey('project', $data);
        $this->assertArrayHasKey('technologiesAll', $data);
        $this->assertArrayHasKey('technologySelectedIds', $data);
        $this->assertCount(3, $data['technologySelectedIds']);
    }

    public function test_can_find_project_with_relations()
    {
        $project = Project::factory()->create();
        $technologies = Technology::factory()->count(2)->create();
        $project->technologies()->attach($technologies->pluck('id'));

        $found = $this->repository->findWithRelations($project->id);

        $this->assertEquals($project->id, $found->id);
        $this->assertTrue($found->relationLoaded('technologies'));
        $this->assertTrue($found->relationLoaded('files'));
    }
}
