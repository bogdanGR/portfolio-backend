<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
class ProjectsController extends Controller
{
    public function index()
    {
        $projects = Project::latest()->get();
        return Inertia::render('projects/Index', compact('projects'));
    }

    public function create()
    {
        return Inertia::render('projects/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'required|string|max:255',
            'long_description' => 'required|string',
            'link' => 'nullable|url',
            'github' => 'nullable|url',
            ]);

        Project::create($data);

        return redirect()->route('projects.index')->with('message', 'Project created successfully.');
    }

    public function edit(Project $project)
    {
        return Inertia::render('projects/Edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'required|string|max:255',
            'long_description' => 'required|string',
            'link' => 'nullable|url',
            'github' => 'nullable|url',
        ]);

        $project->update([
            'name' => $request->name,
            'short_description' => $request->short_description,
            'long_description' => $request->long_description,
            'link' => $request->link,
            'github' => $request->github
        ]);

        return redirect()->route('projects.index')->with('message', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')->with('message', 'Project deleted successfully.');
    }
}
