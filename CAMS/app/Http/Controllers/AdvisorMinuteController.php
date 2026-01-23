<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Minute;
use Illuminate\Support\Facades\Auth;

class AdvisorMinuteController extends Controller
{
    // Show the "Write Note" form
    public function create($appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);

        // Security: Ensure the logged-in advisor owns this appointment slot
        if ($appointment->slot->advisor_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this session note.');
        }

        // Fetch past confirmed history (Session Notes) for this student
        // Requirement: "previous MOM notes he have of a student when... during the session"
        $history = Appointment::where('student_id', $appointment->student_id)
            ->where('id', '!=', $appointmentId) // Exclude current
            ->where('status', 'completed')     // Only completed ones usually have MOM
            ->whereHas('minute')             // Only those with notes
            ->with(['minute', 'slot.advisor'])
            ->latest()
            ->take(5) // Limit to last 5 for relevance
            ->get();

        return view('advisor.minutes.create', compact('appointment', 'history'));
    }

    // Save the Note
    public function store(Request $request, $appointmentId)
    {
        $request->validate([
            'note' => 'required|string|min:5|max:5000',
        ]);

        $appointment = Appointment::findOrFail($appointmentId);

        // Security Check
        if ($appointment->slot->advisor_id !== Auth::id()) {
            abort(403);
        }

        // 1. Create or Update the Minute (MOM)
        Minute::updateOrCreate(
            ['appointment_id' => $appointment->id],
            ['note' => $request->note]
        );

        // 2. Mark Appointment as "Completed" (Requirement)
        $appointment->update(['status' => 'completed']);

        return redirect()->route('advisor.schedule')->with('success', 'Session note saved and appointment marked as Completed.');
    }
}
