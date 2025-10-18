<?php

namespace App\Http\Controllers;

use App\Filters\CertificationFilter;
use App\Http\Requests\Certification\StoreCertificationRequest;
use App\Http\Requests\Certification\UpdateCertificationRequest;
use App\Models\Certification;
use App\Models\Technology;
use App\Repositories\CertificationRepository;
use App\Services\CertificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CertificationController extends Controller
{
    public function __construct(
        private readonly CertificationService $certificationService,
        private readonly CertificationRepository $certificationRepository
    ){}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, CertificationFilter $filter)
    {
        $perPage = $request->integer('per_page', 10);

        if ($perPage <= 0) {
            $perPage = 10;
        }

        $certifications = $this->certificationRepository->search($filter, $perPage);

        return Inertia::render('certifications/Index', [
            'certifications' => $certifications,
            'technologies' => Technology::getMappedTechnologies(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $technologies = Technology::all();

        return Inertia::render('certifications/Create', compact('technologies'));
    }

    /**
     * Create Certification entity
     * @param StoreCertificationRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreCertificationRequest $request)
    {
        try {
            $this->certificationService->create(
                $request->validated(),
                [$request->file('certificationImage')]
            );

            return redirect()
                ->route('certifications.index')
                ->with('message', 'Certification created successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['message' => 'Failed to create Certification.'])
                ->withInput();
        }
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
    public function edit(Certification $certification)
    {
        $data = $this->certificationRepository->getEditData($certification);
        return Inertia::render('certifications/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCertificationRequest $request, Certification $certification)
    {
        try {
            $this->certificationService->update(
                $certification,
                $request->validated(),
                $request->file('certificationImage')
            );

            return redirect()
                ->route('certifications.index')
                ->with('message', 'Certification updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['message' => 'Failed to update Certification. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
