<?php

namespace App\Http\Controllers;

use App\Actions\DeleteProjectAction;
use App\Http\Requests\Projects\StoreProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Models\Project;
use App\Models\Technology;
use App\Repositories\ProjectRepository;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectsController extends Controller
{
    /**
     * Inject project service and project repository
     * @param ProjectService $projectService
     * @param ProjectRepository $projectRepository
     */
    public function __construct(
        private readonly ProjectService $projectService,
        private readonly ProjectRepository $projectRepository
    ){}

    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $projects = $this->projectRepository->paginateWithRelations($perPage);
        return Inertia::render('projects/Index', [
            'projects' => $projects,
            'filters'  => $request->only(['search','per_page']),
        ]);
    }

    public function create()
    {
        $technologies = Technology::all();
        return Inertia::render('projects/Create', compact('technologies'));
    }

    public function store(StoreProjectRequest $request)
    {
        try {
            $this->projectService->createProject(
                $request->validated(),
                $request->file('images')
            );

            return redirect()
                ->route('projects.index')
                ->with('message', 'Project created successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['message' => 'Failed to create project.'])
                ->withInput();
        }
    }

    public function edit(Project $project)
    {
        $data = $this->projectRepository->getEditData($project);
        return Inertia::render('projects/Edit', $data);
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        try {
            $this->projectService->updateProject(
                $project,
                $request->validated(),
                $request->file('images')
            );

            return redirect()
                ->route('projects.index')
                ->with('message', 'Project updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['message' => 'Failed to update project. Please try again.'])
                ->withInput();
        }
    }

    public function destroy(Project $project, DeleteProjectAction $action)
    {
        try {
            $action->execute($project);
            return redirect()
                ->route('projects.index')
                ->with('message', 'Project deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to delete project.']);
        }
    }

    public function detachImage(Project $project, $fileId)
    {
        try {
            $this->projectService->detachImage($project, $fileId);

            return back()->with('message', 'Image removed successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to remove image.']);
        }
    }

    public function setFeaturedImage(Project $project, $fileId)
    {
        try {
            $this->projectService->setFeaturedImage($project, $fileId);

            return back()->with('message', 'Featured image updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to update featured image.']);
        }
    }

    public function reorderImages(Request $request, Project $project)
    {
        $validated = $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'integer'
        ]);

        try {
            $this->projectService->reorderImages($project, $validated['file_ids']);

            return back()->with('message', 'Images reordered successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to reorder images.']);
        }
    }
}
