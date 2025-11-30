<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;

class AdvisorAppointmentController extends Controller
{
    /**
     * Display Pending Requests
     */
    public function index()
    {
        // Fetch appointments where the SLOT belongs to the logged-in advisor
        // And status is 'pending'
        $appointments = Appointment::with(['student', 'slot', 'documents'])
            ->whereHas('slot', function ($query) {
                $query->where('advisor_id', Auth::id());
            })
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('advisor.dashboard', compact('appointments'));
    }

    /**
     * Handle Accept/Decline Actions
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,declined',
        ]);

        $appointment = Appointment::findOrFail($id);

        // Security: Ensure this appointment belongs to this advisor
        if ($appointment->slot->advisor_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized action.');
        }

        $appointment->update(['status' => $request->status]);

        // If declined, we might want to free up the slot (Optional logic)
        // For MVP, we keep the slot blocked or manage it manually.
        // If Approved, the status simply changes to 'approved' (Booked).

        $message = $request->status === 'approved' ? 'Appointment Confirmed!' : 'Request Declined.';

        return back()->with('success', $message);
    }
}
