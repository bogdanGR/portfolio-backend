<?php

use App\Http\Controllers\ProjectsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/projects', [ProjectsController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectsController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectsController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}/edit', [ProjectsController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectsController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectsController::class, 'destroy'])->name('projects.destroy');

    Route::post('projects/{project}/images', [ProjectsController::class, 'uploadImages']);
    Route::delete('projects/{project}/images/{fileId}', [ProjectsController::class, 'detachImage']);
    Route::post('projects/{project}/images/{fileId}/featured', [ProjectsController::class, 'setFeaturedImage']);
    Route::post('projects/{project}/images/reorder', [ProjectsController::class, 'reorderImages']);
});
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
