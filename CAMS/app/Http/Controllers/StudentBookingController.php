<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentBookingController extends Controller
{
    /**
     * 1. List all Advisors (With Search & Filter)
     */
    public function index(Request $request)
    {
        // Start the query
        $query = User::where('role', 'advisor')->with('department');

        // Filter by Name (if searched)
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by Department (if selected)
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $advisors = $query->get();

        // We also need the list of departments for the dropdown
        $departments = \App\Models\Department::all();

        return view('student.advisors.index', compact('advisors', 'departments'));
    }

    /**
     * 2. Show Calendar/Slots for a specific Advisor
     */
    public function show($advisorId)
    {
        $advisor = User::with('department')->findOrFail($advisorId);

        // Fetch only FUTURE and ACTIVE (Green) slots
        $slots = AppointmentSlot::where('advisor_id', $advisorId)
            ->where('status', 'active')
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->get();

        return view('student.advisors.show', compact('advisor', 'slots'));
    }

    /**
     * 3. Store the Booking (The Action)
     */
    public function store(Request $request)
    {
        $request->validate([
            'slot_id' => 'required|exists:appointment_slots,id',
            'purpose' => 'required|string|max:255',
            // 'file' => 'nullable|file|mimes:pdf,jpg,png|max:2048' // Task #8 preparation
        ]);

        $slot = AppointmentSlot::findOrFail($request->slot_id);

        // Security Check: Is slot still open?
        if ($slot->status !== 'active') {
            return back()->with('error', 'Sorry, this slot was just taken.');
        }

        // Generate Token: DEPT-RANDOM-ID (e.g., CSE-5928-X)
        $deptCode = Auth::user()->department?->code ?? 'GEN';
        $token = strtoupper($deptCode . '-' . Str::random(8));

        // Create Appointment within a transaction to prevent race conditions
        return DB::transaction(function () use ($slot, $request, $token) {
            // Re-check slot availability within transaction with lock
            $lockedSlot = AppointmentSlot::lockForUpdate()->findOrFail($slot->id);
            if ($lockedSlot->status !== 'active') {
                return back()->with('error', 'Sorry, this slot was just taken.');
            }

            // Check if student has already booked this slot (excluding terminal statuses)
            $existingBooking = Appointment::where('student_id', Auth::id())
                ->where('slot_id', $lockedSlot->id)
                ->whereNotIn('status', ['cancelled', 'declined', 'completed'])
                ->exists();

            if ($existingBooking) {
                return back()->with('error', 'You have already booked this slot.');
            }

            // Create Appointment
            Appointment::create([
                'student_id' => Auth::id(),
                'slot_id' => $lockedSlot->id,
                'purpose' => $request->purpose,
                'status' => 'pending', // Starts as Pending Approval
                'token' => $token,
            ]);

            // Lock the slot so no one else can book it
            $lockedSlot->update(['status' => 'blocked']);

            return redirect()->route('dashboard')->with('success', "Appointment Booked! Your Token: $token");
        });
    }
}
