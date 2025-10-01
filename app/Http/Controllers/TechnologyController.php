<?php

namespace App\Http\Controllers;

use App\Enums\TechnologyCategory;
use App\Http\Requests\Technologies\StoreTechnologyRequest;
use App\Http\Requests\Technologies\UpdateTechnologyRequest;
use App\Models\Technology;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TechnologyController extends Controller
{
    /**
     * Show list of the technologies (skills)
     * @return \Inertia\Response
     */
    public function index(): Response
    {
        $technologies = Technology::all();

        return Inertia::render('technologies/Index', compact('technologies'));
    }

    /**
     * Show the form for creating a new resource.
     * @return \Inertia\Response
     */
    public function create(): Response
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
    public function store(StoreTechnologyRequest $request): RedirectResponse
    {
        Technology::create($request->validated());

        return redirect()->route('technologies.index')
            ->with('message', 'Skill created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     * @param Technology $technology
     * @return \Inertia\Response
     */
    public function edit(Technology $technology): Response
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
    public function update(UpdateTechnologyRequest $request, Technology $technology): RedirectResponse
    {
        if ($request->validated()) {
            $technology->update($request->validated());

            return redirect()->route('technologies.index')
                ->with('message', 'Skill updated successfully!');
        } else {
            return redirect()->back()->with('message', 'Skill not updated!');
        }
    }

    /**
     * Delete technology from the database
     * @param Technology $technology
     * @return RedirectResponse
     */
    public function destroy(Technology $technology): RedirectResponse
    {
        $technology->delete();

        return redirect()->route('technologies.index')
            ->with('message', 'Skill deleted successfully!');
    }
}
