<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdvisorSlotController;
use App\Http\Controllers\StudentBookingController;
use App\Http\Controllers\AdvisorAppointmentController; // <--- Added for Task #9
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

// =========================================================================
// 1. PUBLIC & GLOBAL ROUTES
// =========================================================================

// Root Route: Redirects guests to Login immediately for a private portal feel
Route::get('/', function () {
    return redirect()->route('login');
});

// Main Dashboard: The landing page after login (Accessible by all roles)
Route::get('/dashboard', function () {
    $nextAppointment = null;
    if (Auth::check()) {
        $nextAppointment = \App\Models\Appointment::where('student_id', Auth::id())
            ->where('status', 'approved')
            ->latest()
            ->first();
    }
    return view('dashboard', compact('nextAppointment'));
})->middleware(['auth', 'verified'])->name('dashboard');

// User Profile: Settings for Password/Name updates
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| ROLE-BASED ROUTES (PROTECTED)
|--------------------------------------------------------------------------
*/

// =========================================================================
// 2. STUDENT AREA
// Protected by 'auth' and 'student' middleware
// =========================================================================
Route::middleware(['auth', 'student', 'throttle:60,1'])->group(function () {

    // Booking Interface: Search Advisors & View Slots
    Route::get('/student/advisors', [StudentBookingController::class, 'index'])->name('student.advisors.index');
    Route::get('/student/advisors/{id}', [StudentBookingController::class, 'show'])->whereNumber('id')->name('student.advisors.show');

    // Appointment Submission: Process the booking request (stricter rate limit)
    Route::post('/student/book', [StudentBookingController::class, 'store'])->middleware('throttle:10,1')->name('student.book.store');

    // Appointment History
    Route::get('/student/my-appointments', [StudentBookingController::class, 'myAppointments'])->name('student.appointments.index');

});

// routes/web.php

Route::middleware(['auth', 'student', 'verified'])->group(function () {
    // ... existing routes
    Route::post('/waitlist/{slot_id}', [App\Http\Controllers\StudentBookingController::class, 'joinWaitlist'])
        ->name('waitlist.join');
});


// =========================================================================
// 3. ADVISOR AREA
// Protected by 'auth' and 'advisor' middleware
// =========================================================================
Route::middleware(['auth', 'advisor', 'throttle:60,1'])->group(function () {

    // --- Task #6: Availability Management ---
    // Manage Slots (Create/Delete)
    Route::get('/advisor/slots', [AdvisorSlotController::class, 'index'])->name('advisor.slots');
    Route::post('/advisor/slots', [AdvisorSlotController::class, 'store'])->middleware('throttle:20,1')->name('advisor.slots.store');
    Route::delete('/advisor/slots/{slot}', [AdvisorSlotController::class, 'destroy'])->name('advisor.slots.destroy');

    // --- Task #9: Request Handling (NEW) ---
    // Advisor Dashboard (View Pending Requests)
    Route::get('/advisor/dashboard', [AdvisorAppointmentController::class, 'index'])->name('advisor.dashboard');

    // Action Buttons (Approve/Decline Requests)
    Route::patch('/advisor/appointments/{id}', [AdvisorAppointmentController::class, 'updateStatus'])->name('advisor.appointments.update');

});


// =========================================================================
// 4. ADMIN AREA
// Protected by 'auth' and 'admin' middleware
// =========================================================================
Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/admin/dashboard', function () {
        return "Admin Dashboard (Coming Soon)";
    })->name('admin.dashboard');

});

require __DIR__.'/auth.php';
