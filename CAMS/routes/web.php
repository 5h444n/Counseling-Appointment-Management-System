<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdvisorSlotController;
use App\Http\Controllers\StudentBookingController;
use App\Http\Controllers\AdvisorAppointmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdvisorScheduleController;
use App\Http\Controllers\AdvisorMinuteController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminActivityLogController;
use App\Http\Controllers\AdminFacultyController;

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
// Main Dashboard: The landing page after login (Accessible by all roles)
Route::get('/dashboard', function () {
    if (Auth::user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    
    $nextAppointment = null;
    if (Auth::check()) {
        $nextAppointment = \App\Models\Appointment::where('student_id', Auth::id())
            ->where('status', 'approved')
            ->latest()
            ->first();
            
        $notices = \App\Models\Notice::where('user_role', 'all')
            ->orWhere('user_role', Auth::user()->role)
            ->latest()
            ->take(3)
            ->get();
    } else {
        $notices = collect();
    }
    return view('dashboard', compact('nextAppointment', 'notices'));
})->middleware(['auth', 'verified'])->name('dashboard');

// User Profile & Calendar
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Calendar
    Route::controller(\App\Http\Controllers\CalendarController::class)->group(function () {
        Route::get('/calendar/events', 'fetchEvents')->name('calendar.events');
        Route::post('/calendar/events', 'store')->name('calendar.store');
        Route::delete('/calendar/events/{id}', 'destroy')->name('calendar.destroy');
    });

    // Feedback
    Route::post('/feedback', [\App\Http\Controllers\FeedbackController::class, 'store'])->name('feedback.store');

    // Resources (Download)
    Route::get('/resources/{resource}/download', [\App\Http\Controllers\ResourceController::class, 'download'])->name('resources.download');
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

    Route::post('/waitlist/{slot_id}', [StudentBookingController::class, 'joinWaitlist'])->name('waitlist.join');

    // Cancel an upcoming appointment
    Route::post('/student/appointments/{id}/cancel', [StudentBookingController::class, 'cancel'])->name('student.appointments.cancel');

    // Resources (Student View)
    Route::get('/student/resources', [\App\Http\Controllers\ResourceController::class, 'index'])->name('student.resources.index');
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
    Route::delete('/advisor/slots/bulk', [AdvisorSlotController::class, 'bulkDestroy'])->name('advisor.slots.bulk_destroy');
    Route::delete('/advisor/slots/{slot}', [AdvisorSlotController::class, 'destroy'])->name('advisor.slots.destroy');

    // --- Task #9: Request Handling (NEW) ---
    // Advisor Dashboard (View Pending Requests)
    Route::get('/advisor/dashboard', [AdvisorAppointmentController::class, 'index'])->name('advisor.dashboard');

    // Action Buttons (Approve/Decline Requests)
    Route::patch('/advisor/appointments/{id}', [AdvisorAppointmentController::class, 'updateStatus'])->name('advisor.appointments.update');
    Route::get('/advisor/students/{id}/history', [AdvisorAppointmentController::class, 'getStudentHistory'])->name('advisor.students.history');
    Route::get('/advisor/schedule', [AdvisorScheduleController::class, 'index'])->name('advisor.schedule');

    // --- Task #16: MOM Notes ---
    Route::get('/advisor/appointments/{id}/note', [AdvisorMinuteController::class, 'create'])->name('advisor.minutes.create');
    Route::post('/advisor/appointments/{id}/note', [AdvisorMinuteController::class, 'store'])->name('advisor.minutes.store');

    // --- Document Download (Secure Access) ---
    Route::get('/advisor/documents/{documentId}/download', [AdvisorAppointmentController::class, 'downloadDocument'])->name('advisor.documents.download');

    // --- Resources (Manage) ---
    Route::get('/advisor/resources', [\App\Http\Controllers\ResourceController::class, 'index'])->name('advisor.resources.index');
    Route::post('/advisor/resources', [\App\Http\Controllers\ResourceController::class, 'store'])->name('advisor.resources.store');
    Route::delete('/advisor/resources/{resource}', [\App\Http\Controllers\ResourceController::class, 'destroy'])->name('advisor.resources.destroy');

});


// =========================================================================
// 4. ADMIN AREA
// Protected by 'auth' and 'admin' middleware
// =========================================================================
Route::middleware(['auth', 'admin', 'throttle:60,1'])->group(function () {

    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/export', [AdminDashboardController::class, 'export'])->name('admin.export');


    // Faculty CRUD
    Route::get('/admin/faculty', [AdminFacultyController::class, 'index'])->name('admin.faculty.index');
    Route::get('/admin/faculty/create', [AdminFacultyController::class, 'create'])->name('admin.faculty.create');
    Route::post('/admin/faculty', [AdminFacultyController::class, 'store'])->name('admin.faculty.store');
    Route::get('/admin/faculty/{id}/edit', [AdminFacultyController::class, 'edit'])->name('admin.faculty.edit');
    Route::put('/admin/faculty/{id}', [AdminFacultyController::class, 'update'])->name('admin.faculty.update');
    Route::delete('/admin/faculty/{id}', [AdminFacultyController::class, 'destroy'])->name('admin.faculty.destroy');

    // Activity Logs
    Route::get('/admin/activity-logs', [AdminActivityLogController::class, 'index'])->name('admin.activity-logs');

    // Student CRUD
    Route::resource('admin/students', \App\Http\Controllers\AdminStudentController::class, ['as' => 'admin']);

    // Booking Management
    Route::get('admin/bookings/create', [\App\Http\Controllers\AdminBookingController::class, 'create'])->name('admin.bookings.create');
    Route::post('admin/bookings', [\App\Http\Controllers\AdminBookingController::class, 'store'])->name('admin.bookings.store');
    Route::get('admin/bookings/slots', [\App\Http\Controllers\AdminBookingController::class, 'getSlots'])->name('admin.bookings.slots');
    Route::delete('admin/bookings/{id}', [\App\Http\Controllers\AdminBookingController::class, 'destroy'])->name('admin.bookings.destroy');

    // Notice Management
    Route::resource('admin/notices', \App\Http\Controllers\AdminNoticeController::class, ['as' => 'admin']);

    // Resource Library Management
    Route::get('/admin/resources', [\App\Http\Controllers\ResourceController::class, 'index'])->name('admin.resources.index');
    Route::post('/admin/resources', [\App\Http\Controllers\ResourceController::class, 'store'])->name('admin.resources.store');
    Route::delete('/admin/resources/{resource}', [\App\Http\Controllers\ResourceController::class, 'destroy'])->name('admin.resources.destroy');
});

require __DIR__.'/auth.php';
