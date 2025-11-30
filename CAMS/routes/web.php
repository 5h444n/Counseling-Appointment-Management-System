<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdvisorSlotController; // <--- Imported the new Controller
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentBookingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Root Route: Redirect straight to Login (More professional)
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. Dashboard: Accessible by any logged-in user (Student or Advisor)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// 3. Profile Routes: Standard Laravel profile management
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// STUDENT BOOKING ROUTES (Authenticated users only)
Route::middleware('auth')->group(function () {
    Route::get('/student/advisors', [StudentBookingController::class, 'index'])->name('student.advisors.index');
    Route::get('/student/advisors/{id}', [StudentBookingController::class, 'show'])->whereNumber('id')->name('student.advisors.show');
    Route::post('/student/book', [StudentBookingController::class, 'store'])->name('student.book.store');
});
/*
|--------------------------------------------------------------------------
| ROLE-BASED ROUTES (Week 1 & 2 Work)
|--------------------------------------------------------------------------
*/

// === ADVISOR AREA ===
// Only users with 'role: advisor' can access these.
Route::middleware(['auth', 'advisor'])->group(function () {

    // Manage Availability (The Phase 3 Work)
    Route::get('/advisor/slots', [AdvisorSlotController::class, 'index'])->name('advisor.slots');
    Route::post('/advisor/slots', [AdvisorSlotController::class, 'store'])->name('advisor.slots.store');
    Route::delete('/advisor/slots/{slot}', [AdvisorSlotController::class, 'destroy'])->name('advisor.slots.destroy');

});

// === STUDENT AREA (Future Placeholders) ===
// Only users with 'role: student' can access these.
Route::middleware(['auth', 'student'])->group(function () {

    Route::get('/student/book', function () {
        return "Student Booking Page (Coming Soon)";
    })->name('student.book');

});

// === ADMIN AREA ===
// Only users with 'role: admin' can access these.
Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/admin/dashboard', function () {
        return "Admin Dashboard (Coming Soon)";
    })->name('admin.dashboard');

});

require __DIR__.'/auth.php';
