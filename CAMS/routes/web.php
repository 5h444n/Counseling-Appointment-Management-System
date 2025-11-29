<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentBookingController;

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

// STUDENT BOOKING ROUTES
Route::get('/student/advisors', [StudentBookingController::class, 'index'])->name('student.advisors.index');
Route::get('/student/advisors/{id}', [StudentBookingController::class, 'show'])->name('student.advisors.show');
Route::post('/student/book', [StudentBookingController::class, 'store'])->name('student.book.store');

require __DIR__.'/auth.php';
