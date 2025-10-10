<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Models\Technology;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CertificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 10);

        if ($perPage <= 0) {
            $perPage = 10;
        }

        $certifications = Certification::query()
            ->orderByDesc('name')
            ->paginate($perPage)
            ->withQueryString();
        return Inertia::render('certifications/Index', [
            'certifications' => $certifications,
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
