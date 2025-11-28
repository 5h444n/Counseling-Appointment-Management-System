<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Student Only Area
Route::middleware(['auth', 'student'])->group(function () {
    Route::get('/student-dashboard', function () {
        return "Welcome, Student! (You are safe here)";
    });
});

// Advisor Only Area
Route::middleware(['auth', 'advisor'])->group(function () {
    Route::get('/advisor-dashboard', function () {
        return "Welcome, Advisor! (Students cannot see this)";
    });
});

// Admin Only Area
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin-dashboard', function () {
        return "Welcome, Boss! (Admin Area)";
    });
});
