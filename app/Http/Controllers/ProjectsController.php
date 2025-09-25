<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
class ProjectsController extends Controller
{
    public function index()
    {
        $projects = Project::with(['files' => function($query) {
            $query->orderBy('project_files.sort_order');
        }])->get();

        return Inertia::render('projects/Index', compact('projects'));
    }

    public function create()
    {
        return Inertia::render('projects/Create');
    }

    public function store(Request $request)
    {
        \Log::info('Files received:', [
            'images_count' => $request->hasFile('images') ? count($request->file('images')) : 0,
            'all_files' => $request->allFiles()
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'required|string|max:255',
            'long_description' => 'required|string',
            'link' => 'nullable|url',
            'github' => 'nullable|url',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

        DB::beginTransaction();
        try {
            // Create the project
            $project = Project::create([
                'name' => $validated['name'],
                'short_description' => $validated['short_description'],
                'long_description' => $validated['long_description'],
                'link' => $validated['link'],
                'github' => $validated['github']
            ]);

            // Handle file uploads
            if ($request->hasFile('images')) {
                $this->handleImageUploads($project, $request->file('images'));
            }

            DB::commit();

            return redirect()->route('projects.index')
                ->with('success', 'Project created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Failed to create project. Please try again.'])
                ->withInput();
        }

        // return redirect()->route('projects.index')->with('message', 'Project created successfully.');
    }

    public function edit(Project $project)
    {
        $project->load(['files' => function($query) {
            $query->orderBy('project_files.sort_order');
        }]);

        return Inertia::render('projects/Edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'required|string|max:255',
            'long_description' => 'required|string',
            'link' => 'nullable|url',
            'github' => 'nullable|url',
            'images' => 'nullable|array',
            //'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        //var_dump($validated);die();
       // dd('update');
        DB::beginTransaction();
        try {
            // Update project details
            $project->update([
                'name' => $validated['name'],
                'short_description' => $validated['short_description'],
                'long_description' => $validated['long_description'],
                'link' => $validated['link'],
                'github' => $validated['github']
            ]);

            // Handle new image uploads if any
            if ($request->hasFile('images')) {
                $this->handleImageUploads($project, $request->file('images'));
            }

            DB::commit();

            return redirect()->route('projects.index')
                ->with('success', 'Project updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Failed to update project. Please try again.'])
                ->withInput();
        }
    }

    public function destroy(Project $project)
    {
        DB::beginTransaction();
        try {
            // Get all files before deleting the project
            $files = $project->files;

            // Delete the project (this will cascade delete pivot records)
            $project->delete();

            // Clean up orphaned files
            foreach ($files as $file) {
                // Check if file is used by other projects or models
                if ($file->projects()->count() === 0) {
                    // Delete file from storage
                    Storage::delete($file->path);
                    // Delete file record
                    $file->delete();
                }
            }

            DB::commit();

            return redirect()->route('projects.index')
                ->with('success', 'Project deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Failed to delete project. Please try again.']);
        }
    }

    /**
     * Handle uploading multiple images for a project
     */
    private function handleImageUploads(Project $project, array $uploadedFiles)
    {
        $maxSortOrder = $project->files()->max('project_files.sort_order') ?? 0;
        $isFirstImage = $project->files()->count() === 0;

        foreach ($uploadedFiles as $index => $uploadedFile) {
            // Generate unique filename
            $filename = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
            $directory = 'projects/' . $project->id;

            // Store the file
            $path = $uploadedFile->storeAs($directory, $filename, 'public');

            // Create file record
            $file = File::create([
                'original_name' => $uploadedFile->getClientOriginalName(),
                'filename' => $filename,
                'path' => $path,
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
                'type' => 'image'
            ]);

            // Attach file to project
            $project->files()->attach($file->id, [
                'sort_order' => $maxSortOrder + $index + 1,
                'is_featured' => $isFirstImage && $index === 0, // First image of first batch is featured
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public function uploadImages(Request $request, Project $project)
    {
        dd('upload images...');
        $validator = Validator::make($request->all(), [
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        dd('edw');
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $uploadedFiles = $project->uploadFiles($request->file('images'));

        return response()->json([
            'files' => $uploadedFiles,
            'project' => $project->fresh(['files'])
        ]);
    }

    public function detachImage(Project $project, $fileId)
    {
        $project->removeFile($fileId);
        $project->detachFile($fileId);

        redirect()->back();
    }

    public function setFeaturedImage(Project $project, $fileId)
    {
        $project->setFeaturedImage($fileId);
        redirect()->back()->with('message', 'Featured image updated successfully!');
    }

    public function reorderImages(Request $request, Project $project)
    {
        $validator = Validator::make($request->all(), [
            'file_ids' => 'required|array',
            'file_ids.*' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $project->reorderFiles($request->file_ids);

        redirect()->back()->with('message', 'Images updated successfully!');
    }
}
