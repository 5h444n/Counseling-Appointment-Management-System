<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * Store a newly created feedback in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $appointment = Appointment::findOrFail($request->appointment_id);

        // Security Check: Only the student of the appointment can rate it
        if ($appointment->student_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if already rated
        if (Feedback::where('appointment_id', $appointment->id)->exists()) {
            return back()->with('error', 'You have already rated this appointment.');
        }

        Feedback::create([
            'appointment_id' => $appointment->id,
            'student_id' => Auth::id(),
            'advisor_id' => $appointment->slot->advisor_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_anonymous' => $request->is_anonymous ?? false,
        ]);

        return back()->with('success', 'Thank you! Your feedback has been submitted.');
    }
}
