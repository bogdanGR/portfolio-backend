<?php

namespace App\Http\Controllers;

use App\Enums\TechnologyCategory;
use App\Models\Technology;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TechnologyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $technologies = Technology::all();

        return Inertia::render('technologies/Index', compact('technologies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = TechnologyCategory::all();

        return Inertia::render('technologies/Create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Technology $technology)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:technologies',
            'slug' => 'required|string|max:255|unique:technologies',
            'category' => ['required' , 'string', Rule::enum(TechnologyCategory::class)],
        ]);

        $technology->create($validated);

        return redirect()->route('technologies.index')
            ->with('success', 'Skill created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Technology $technology)
    {
        $categories = TechnologyCategory::all();

        return Inertia::render('technologies/Edit', [
            'technology' => $technology,
            'categories' => $categories
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Technology $technology)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'category' => ['required' , 'string', Rule::enum(TechnologyCategory::class)],
        ]);

        if ($validated) {
            $technology->update($validated);

            return redirect()->route('technologies.index')
                ->with('success', 'Skill updated successfully!');
        } else {
            return redirect()->back()-with('error', 'Skill not updated!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Technology $technology)
    {
        if ($technology->delete()) {
            return redirect()->route('technologies.index')
                ->with('success', 'Skill deleted successfully!');
        }
        return back()
            ->withErrors(['error' => 'Failed to delete the skill. Please try again.']);
    }
}
