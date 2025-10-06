<?php

namespace Feature\Project;

use App\Models\Project;
use App\Models\Technology;
use App\Services\FileService;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProjectService $projectService;
    private FileService  $fileService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->fileService = new FileService();
        $this->projectService = new ProjectService($this->fileService);
    }

    public function test_can_create_a_project_without_images()
    {
        $data = [
            'name' => 'Test Project',
            'short_description' => 'Short desc',
            'long_description' => 'Long description here',
            'link' => 'https://example.com',
            'github' => 'https://github.com/test/repo',
        ];

        $project = $this->projectService->createProject($data);

        $this->assertInstanceOf(Project::class, $project);
        $this->assertEquals('Test Project', $project->name);
        $this->assertDatabaseHas('projects', [
            'name' => 'Test Project',
            'short_description' => 'Short desc',
        ]);
    }

    public function test_can_create_a_project_with_images()
    {
        $data = [
            'name' => 'Test Project',
            'short_description' => 'Short desc',
            'long_description' => 'Long description',
            'link' => null,
            'github' => null,
        ];

        $images = [
            UploadedFile::fake()->image('test1.jpg'),
            UploadedFile::fake()->image('test2.png'),
        ];

        $project = $this->projectService->createProject($data, $images);

        $this->assertCount(2, $project->files);
        Storage::disk('public')->assertExists("projects/{$project->id}/" . $project->files[0]->filename);
    }

    public function test_can_create_a_project_with_technologies()
    {
        $tech1 = Technology::factory()->create(['name' => 'PHP']);
        $tech2 = Technology::factory()->create(['name' => 'Laravel']);

        $data = [
            'name' => 'Test Project',
            'short_description' => 'Short desc',
            'long_description' => 'Long description',
            'technology_ids' => [$tech1->id, $tech2->id],
        ];

        $project = $this->projectService->createProject($data);

        $this->assertCount(2, $project->technologies);
        $this->assertTrue($project->technologies->contains('id', $tech1->id));
        $this->assertTrue($project->technologies->contains('id', $tech2->id));
    }

    public function test_can_update_a_project()
    {
        $project = Project::factory()->create([
            'name' => 'Old Name',
            'short_description' => 'Old desc',
        ]);

        $data = [
            'name' => 'Updated Name',
            'short_description' => 'Updated desc',
            'long_description' => 'Updated long description',
            'link' => 'https://updated.com',
            'github' => null,
        ];

        $updatedProject = $this->projectService->updateProject($project, $data);

        $this->assertEquals('Updated Name', $updatedProject->name);
        $this->assertEquals('Updated desc', $updatedProject->short_description);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_syncs_technologies_with_correct_sort_order()
    {
        $project = Project::factory()->create();
        $tech1 = Technology::factory()->create();
        $tech2 = Technology::factory()->create();
        $tech3 = Technology::factory()->create();

        $data = [
            'name' => 'Test',
            'short_description' => 'Test',
            'long_description' => 'Test',
            'technology_ids' => [$tech2->id, $tech1->id, $tech3->id],
        ];

        $this->projectService->updateProject($project, $data);

        $project->refresh();
        $pivots = $project->technologies->pluck('pivot.sort_order', 'id');

        $this->assertEquals(2, $pivots[$tech1->id]);
        $this->assertEquals(1, $pivots[$tech2->id]);
        $this->assertEquals(3, $pivots[$tech3->id]);
    }

    public function test_can_detach_an_image()
    {
        Storage::fake('public');
        $project = Project::factory()->create();
        $image = UploadedFile::fake()->image('test.jpg');

        $this->fileService->handleImageUploads($project, [$image]);
        $file = $project->files->first();

        $this->projectService->detachImage($project, $file->id);

        $project->refresh();
        $this->assertCount(0, $project->files);
        Storage::disk('public')->assertMissing($file->path);
    }

    public function test_can_set_featured_image()
    {
        Storage::fake('public');
        $project = Project::factory()->create();
        $images = [
            UploadedFile::fake()->image('test1.jpg'),
            UploadedFile::fake()->image('test2.jpg'),
        ];

        $this->fileService->handleImageUploads($project, $images);
        $project->refresh();

        $firstFile = $project->files->first();
        $secondFile = $project->files->last();

        $this->projectService->setFeaturedImage($project, $secondFile->id);

        $project->refresh();
        $this->assertFalse((bool) $project->files->find($firstFile->id)->pivot->is_featured);
        $this->assertTrue((bool) $project->files->find($secondFile->id)->pivot->is_featured);
    }

    public function test_can_reorder_images()
    {
        Storage::fake('public');
        $project = Project::factory()->create();
        $images = [
            UploadedFile::fake()->image('test1.jpg'),
            UploadedFile::fake()->image('test2.jpg'),
            UploadedFile::fake()->image('test3.jpg'),
        ];

        $this->fileService->handleImageUploads($project, $images);
        $project->refresh();

        $fileIds = $project->files->pluck('id')->toArray();
        $reorderedIds = array_reverse($fileIds);

        $this->projectService->reorderImages($project, $reorderedIds);

        $project->refresh();
        $sortOrders = $project->files->pluck('pivot.sort_order', 'id');

        $this->assertEquals(1, $sortOrders[$reorderedIds[0]]);
        $this->assertEquals(2, $sortOrders[$reorderedIds[1]]);
        $this->assertEquals(3, $sortOrders[$reorderedIds[2]]);
    }
}
