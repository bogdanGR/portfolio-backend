<?php

namespace Feature\Project;

use App\Models\Project;
use App\Models\Technology;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        Storage::fake('public');
    }
    public function test_displays_projects_index_page()
    {
        Project::factory()->count(3)->create();

        $response = $this->get(route('projects.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
        $page->component('projects/Index')
            ->has('projects.data', 3)
        );
    }

    public function test_displays_create_page()
    {
        $response = $this->get(route('projects.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
        $page->component('projects/Create')
            ->has('technologies')
        );
    }

    public function test_can_create_a_project()
    {
        $tech1 = Technology::factory()->create();
        $tech2 = Technology::factory()->create();

        $data = [
            'name' => 'New Project',
            'short_description' => 'Short description',
            'long_description' => 'Long description here',
            'link' => 'https://example.com',
            'github' => 'https://github.com/test/repo',
            'technology_ids' => [$tech1->id, $tech2->id],
            'images' => [
                UploadedFile::fake()->image('test.jpg'),
            ],
        ];

        $response = $this->post(route('projects.store'), $data);

        $response->assertRedirect(route('projects.index'));
        $response->assertSessionHas('message', 'Project created successfully!');

        $this->assertDatabaseHas('projects', [
            'name' => 'New Project',
            'short_description' => 'Short description',
        ]);

        $project = Project::where('name', 'New Project')->first();
        $this->assertCount(2, $project->technologies);
        $this->assertCount(1, $project->files);
    }

    public function test_validates_required_fields_on_create()
    {
        $response = $this->post(route('projects.store'), []);

        $response->assertSessionHasErrors(['name', 'short_description', 'long_description']);
    }

    public function test_validates_url_fields()
    {
        $response = $this->post(route('projects.store'), [
            'name' => 'Test',
            'short_description' => 'Test',
            'long_description' => 'Test',
            'link' => 'not-a-valid-url',
            'github' => 'also-not-valid',
        ]);

        $response->assertSessionHasErrors(['link', 'github']);
    }

    public function test_displays_edit_page()
    {
        $project = Project::factory()->create();

        $response = $this->get(route('projects.edit', $project));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
        $page->component('projects/Edit')
            ->has('project')
            ->has('technologiesAll')
            ->has('technologySelectedIds')
        );
    }

    public function test_can_update_a_project()
    {
        $project = Project::factory()->create([
            'name' => 'Old Name',
        ]);

        $data = [
            'name' => 'Updated Name',
            'short_description' => 'Updated description',
            'long_description' => 'Updated long description',
            'link' => 'https://updated.com',
            'github' => null,
        ];

        $response = $this->put(route('projects.update', $project), $data);

        $response->assertRedirect(route('projects.index'));
        $response->assertSessionHas('message', 'Project updated successfully!');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_a_project()
    {
        $project = Project::factory()->create();

        $response = $this->delete(route('projects.destroy', $project));

        $response->assertRedirect(route('projects.index'));
        $response->assertSessionHas('message', 'Project deleted successfully!');

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
        ]);
    }

    public function test_can_detach_an_image()
    {
        $project = Project::factory()->create();
        $image = UploadedFile::fake()->image('test.jpg');

        $fileService = app(FileService::class);
        $fileService->handleImageUploads($project, [$image]);

        $file = $project->files->first();

        $response = $this->delete(route('projects.detachImage', [
            'project' => $project,
            'fileId' => $file->id
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('message', 'Image removed successfully!');

        $this->assertDatabaseMissing('project_files', [
            'project_id' => $project->id,
            'file_id' => $file->id,
        ]);
    }

    public function test_can_set_featured_image()
    {
        $project = Project::factory()->create();
        $images = [
            UploadedFile::fake()->image('test1.jpg'),
            UploadedFile::fake()->image('test2.jpg'),
        ];

        $fileService = app(FileService::class);
        $fileService->handleImageUploads($project, $images);

        $file = $project->files->last();

        $response = $this->post(route('projects.setFeaturedImage', [
            'project' => $project,
            'fileId' => $file->id
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('message', 'Featured image updated successfully!');

        $this->assertDatabaseHas('project_files', [
            'project_id' => $project->id,
            'file_id' => $file->id,
            'is_featured' => 1,
        ]);
    }

    public function test_can_reorder_images()
    {
        $project = Project::factory()->create();
        $images = [
            UploadedFile::fake()->image('test1.jpg'),
            UploadedFile::fake()->image('test2.jpg'),
            UploadedFile::fake()->image('test3.jpg'),
        ];

        $fileService = app(FileService::class);
        $fileService->handleImageUploads($project, $images);

        $fileIds = $project->files->pluck('id')->reverse()->values()->toArray();

        $response = $this->post(route('projects.reorderImages', $project), [
            'file_ids' => $fileIds,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('message', 'Images reordered successfully!');
    }
}
