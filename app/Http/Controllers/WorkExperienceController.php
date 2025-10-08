<?php

namespace App\Http\Controllers;

use App\Filters\WorkExperienceFilter;
use App\Models\WorkExperience;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\WorkExperience\StoreWorkExperienceRequest;
use App\Http\Requests\WorkExperience\UpdateWorkExperienceRequest;

class WorkExperienceController extends Controller
{
    public function index(Request $request, WorkExperienceFilter $filter)
    {
        $perPage = $request->integer('per_page', 10);

        if ($perPage <= 0) {
            $perPage = 10;
        }

        $experiences = WorkExperience::filter($filter)
            ->orderByDesc('start_date')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('work-experiences/Index', [
            'experiences' => $experiences,
            'filters' => $request->only(['job_title', 'company_name', 'period_from', 'period_to', 'created_at', 'updated_at', 'sort', 'direction']),
        ]);
    }

    public function create()
    {
        return Inertia::render('work-experiences/Create');
    }

    public function store(StoreWorkExperienceRequest $request)
    {
        WorkExperience::create($request->validated());

        return redirect()
            ->route('work-experiences.index')
            ->with('message', 'Work experience created successfully!');
    }

    public function edit(WorkExperience $work_experience)
    {
        return Inertia::render('work-experiences/Edit', [
            'experience' => $work_experience,
        ]);
    }

    public function update(UpdateWorkExperienceRequest $request, WorkExperience $work_experience)
    {
        $work_experience->update($request->validated());

        return redirect()
            ->route('work-experiences.index')
            ->with('message', 'Work experience updated successfully!');
    }

    public function destroy(WorkExperience $work_experience)
    {
        $work_experience->delete();

        return redirect()
            ->route('work-experiences.index')
            ->with('message', 'Work experience deleted successfully!');
    }
}
