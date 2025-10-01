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
     * Show list of the technologies (skills)
     * @return \Inertia\Response
     */
    public function index()
    {
        $technologies = Technology::all();

        return Inertia::render('technologies/Index', compact('technologies'));
    }

    /**
     * Show the form for creating a new resource.
     * @return \Inertia\Response
     */
    public function create()
    {
        $categories = TechnologyCategory::all();

        return Inertia::render('technologies/Create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @param Technology $technology
     * @return \Illuminate\Http\RedirectResponse
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
            ->with('message', 'Skill created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     * @param Technology $technology
     * @return \Inertia\Response
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
     * Update the specified technology in storage.
     * @param Request $request
     * @param Technology $technology
     * @return \Illuminate\Http\RedirectResponse|string
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
                ->with('message', 'Skill updated successfully!');
        } else {
            return redirect()->back()-with('message', 'Skill not updated!');
        }
    }

    /**
     * Delete technology from the database
     * @param Technology $technology
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Technology $technology)
    {
        if ($technology->delete()) {
            return redirect()->route('technologies.index')
                ->with('message', 'Skill deleted successfully!');
        }
        return back()
            ->withErrors(['message' => 'Failed to delete the skill. Please try again.']);
    }
}
