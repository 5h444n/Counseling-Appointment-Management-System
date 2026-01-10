<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StudentBookingController extends Controller
{
    /**
     * 1. List all Advisors (Matches index.blade.php)
     */
    public function index(Request $request)
    {
        // Validate query parameters
        $request->validate([
            'search' => 'nullable|string|max:100',
            'department_id' => 'nullable|integer|exists:departments,id',
        ]);

        // Start query for Advisors only, eager load department for performance
        $query = User::where('role', 'advisor')->with('department');

        // Handle Search (Name) - escape LIKE wildcards for security
        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Handle Filter (Department)
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $advisors = $query->get();

        // Get departments for the dropdown filter
        $departments = Department::all();

        return view('student.advisors.index', compact('advisors', 'departments'));
    }

    /**
     * 2. Show Slots for specific Advisor (Matches show.blade.php)
     */
    public function show($advisorId)
    {
        // Ensure we only show advisor profiles
        $advisor = User::where('role', 'advisor')->with('department')->findOrFail($advisorId);

        // Fetch slots: Must be active, belongs to advisor, and in the future
        $slots = AppointmentSlot::where('advisor_id', $advisorId)
            ->whereIn('status', ['active', 'blocked']) // <--- CHANGED THIS LINE
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->get();

        return view('student.advisors.show', compact('advisor', 'slots'));
    }

    /**
     * 3. Handle Booking Submission (Matches the form in show.blade.php)
     */
    public function store(Request $request)
    {
        // 1. Validate Form Input
        $request->validate([
            'slot_id' => 'required|exists:appointment_slots,id',
            'purpose' => 'required|string|min:10|max:500',
        ]);

        try {
            // 2. Database Transaction to prevent Double Booking
            DB::transaction(function () use ($request) {

                // Lock the slot row so no one else can read/write it simultaneously
                $slot = AppointmentSlot::where('id', $request->slot_id)
                    ->lockForUpdate()
                    ->first();

                // Double check status inside the lock
                if (!$slot || $slot->status !== 'active') {
                    throw new \Exception('Sorry, this slot was just taken by someone else.');
                }

                // Ensure the slot is in the future
                if ($slot->start_time <= now()) {
                    throw new \Exception('Cannot book a slot that has already started or passed.');
                }

                // Prevent duplicate bookings by same student for same slot
                $existingAppointment = Appointment::where('student_id', Auth::id())
                    ->where('slot_id', $slot->id)
                    ->exists();

                if ($existingAppointment) {
                    throw new \Exception('You have already booked this slot.');
                }

                // Generate a Unique Token (e.g., CSE-8492-X)
                // 1. Get Department Code (e.g., CSE)
                $deptCode = Auth::user()->department->code ?? 'GEN';

                // 2. Get User ID (e.g., 123)
                $userId = Auth::id();

                // 3. Generate a Serial (Random letter A-Z) and ensure uniqueness
                $maxAttempts = 26; // Maximum 26 letters
                $attempts = 0;
                do {
                    $serial = chr(rand(65, 90));
                    $token = strtoupper("{$deptCode}-{$userId}-{$serial}");
                    $attempts++;

                    if ($attempts >= $maxAttempts) {
                        throw new \Exception('Unable to generate a unique token. Please try again.');
                    }
                } while (Appointment::where('token', $token)->exists());

                // Create the Appointment
                $appointment = Appointment::create([
                    'student_id' => Auth::id(),
                    'slot_id'    => $slot->id,
                    'purpose'    => $request->purpose,
                    'status'     => 'pending',
                    'token'      => $token,
                ]);

                // Mark slot as blocked
                $slot->update(['status' => 'blocked']);

                // Log successful booking
                Log::info('Appointment booked successfully', [
                    'student_id' => Auth::id(),
                    'appointment_id' => $appointment->id,
                    'token' => $token,
                    'slot_id' => $slot->id,
                ]);
            });

            // 3. Success Redirect
            return redirect()->route('dashboard')->with('success', 'Appointment booked successfully! Wait for approval.');

        } catch (\Exception $e) {
            // 4. Error Redirect
            Log::warning('Appointment booking failed', [
                'student_id' => Auth::id(),
                'slot_id' => $request->slot_id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * 4. List Student's Appointment History
     */
    public function myAppointments()
    {
        $appointments = Appointment::with(['slot.advisor', 'documents'])
            ->where('student_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.appointments.index', compact('appointments'));
    }

public function joinWaitlist(Request $request, $slotId)
{
    $user = Auth::user();
    $slot = \App\Models\AppointmentSlot::findOrFail($slotId);

    // 1. Validation: Can only join if status is 'blocked'
    if ($slot->status !== 'blocked') {
        return back()->with('error', 'This slot is currently available. You can book it directly.');
    }

    // 2. Validation: Prevent duplicates
    $exists = \App\Models\Waitlist::where('slot_id', $slotId)
        ->where('student_id', $user->id)
        ->exists();

    if ($exists) {
        return back()->with('error', 'You are already on the waitlist for this slot.');
    }

    // 3. Create Entry
    \App\Models\Waitlist::create([
        'slot_id' => $slotId,
        'student_id' => $user->id,
    ]);

    return back()->with('success', 'You have joined the waitlist. We will notify you if it opens up.');
}


}
