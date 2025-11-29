<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
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
        $advisor = User::findOrFail($advisorId);

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
        $deptCode = Auth::user()->department->code ?? 'GEN';
        $token = strtoupper($deptCode . '-' . rand(1000, 9999) . '-' . Str::random(1));

        // Create Appointment
        Appointment::create([
            'student_id' => Auth::id(),
            'slot_id' => $slot->id,
            'purpose' => $request->purpose,
            'status' => 'pending', // Starts as Pending Approval
            'token' => $token,
        ]);

        // Lock the slot so no one else can book it
        $slot->update(['status' => 'blocked']);

        return redirect()->route('dashboard')->with('success', "Appointment Booked! Your Token: $token");
    }
}
