<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Events\SlotFreedUp;

class AdvisorAppointmentController extends Controller
{
    /**
     * Display Pending Requests
     */
    public function index()
    {
        $appointments = Appointment::whereHas('slot', function ($q) {
                $q->where('advisor_id', Auth::id());
            })
            ->where('status', 'pending')
            ->with(['student', 'slot', 'documents'])
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

        // Security check: ensure this appointment belongs to the logged-in advisor
        if ($appointment->slot->advisor_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized action.');
        }

        // Prevent modifying already processed appointments
        if ($appointment->status !== 'pending') {
            return back()->with('error', 'This appointment has already been processed.');
        }

        // --- DECLINE FLOW ---
        if ($request->status === 'declined') {
            // 1. Update Appointment Status
            $appointment->update(['status' => 'declined']);

            // 2. Free up the slot (The Logic You Need)
            $slot = $appointment->slot;
            $slot->status = 'active';
            $slot->save();

            // 3. Fire Event to notify Waitlist
            try {
                event(new SlotFreedUp($slot));
                Log::info("Slot {$slot->id} freed. Waitlist event fired.");
            } catch (\Exception $e) {
                Log::error("Event Error: " . $e->getMessage());
            }

            return back()->with('success', 'Request Declined.');
        }

        // --- APPROVE FLOW ---
        $appointment->update(['status' => 'approved']);

        Log::info('Appointment approved', [
            'advisor_id' => Auth::id(),
            'appointment_id' => $appointment->id
        ]);

        return back()->with('success', 'Appointment Confirmed!');
    }
}
