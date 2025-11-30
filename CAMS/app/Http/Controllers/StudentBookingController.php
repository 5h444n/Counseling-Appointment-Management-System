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
use Illuminate\Support\Str;

class StudentBookingController extends Controller
{
    /**
     * 1. List all Advisors (Matches index.blade.php)
     */
    public function index(Request $request)
    {
        // Start query for Advisors only, eager load department for performance
        $query = User::where('role', 'advisor')->with('department');

        // Handle Search (Name)
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
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
        $advisor = User::with('department')->findOrFail($advisorId);

        // Fetch slots: Must be active, belongs to advisor, and in the future
        $slots = AppointmentSlot::where('advisor_id', $advisorId)
            ->where('status', 'active')
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
            'purpose' => 'required|string|max:500',
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

                // Generate a Unique Token (e.g., CSE-8492-X)
                $deptCode = Auth::user()->department->code ?? 'GEN';
                $token = strtoupper($deptCode . '-' . rand(1000, 9999) . '-' . Str::random(1));

                // Create the Appointment
                Appointment::create([
                    'student_id' => Auth::id(),
                    'slot_id'    => $slot->id,
                    'purpose'    => $request->purpose,
                    'status'     => 'pending',
                    'token'      => $token,
                ]);

                // Mark slot as blocked
                $slot->update(['status' => 'blocked']);
            });

            // 3. Success Redirect
            return redirect()->route('dashboard')->with('success', 'Appointment booked successfully! Wait for approval.');

        } catch (\Exception $e) {
            // 4. Error Redirect
            return back()->with('error', $e->getMessage());
        }
    }
}
