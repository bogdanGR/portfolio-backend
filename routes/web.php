<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TechnologyController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::delete('projects/{project}/images/{fileId}', [ProjectController::class, 'detachImage'])->name('projects.detachImage');
    Route::post('projects/{project}/images/{fileId}/featured', [ProjectController::class, 'setFeaturedImage'])->name('projects.setFeaturedImage');
    Route::post('projects/{project}/images/reorder', [ProjectController::class, 'reorderImages'])->name('projects.reorderImages');
});

Route::resource('technologies', TechnologyController::class)->middleware(['auth', 'verified']);
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
