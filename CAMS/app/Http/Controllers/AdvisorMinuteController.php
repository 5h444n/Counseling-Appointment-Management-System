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

        return view('advisor.minutes.create', compact('appointment'));
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
