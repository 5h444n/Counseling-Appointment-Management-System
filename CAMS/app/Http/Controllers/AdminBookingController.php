<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\User;
use Illuminate\Support\Str;

class AdminBookingController extends Controller
{
    /**
     * Show the form for creating a new booking.
     */
    public function create()
    {
        $students = User::where('role', 'student')->orderBy('name')->get();
        $advisors = User::where('role', 'advisor')->orderBy('name')->get();
        
        return view('admin.bookings.create', compact('students', 'advisors'));
    }

    public function getSlots(Request $request)
    {
        $request->validate(['advisor_id' => 'required|exists:users,id']);

        $slots = AppointmentSlot::where('advisor_id', $request->advisor_id)
            ->where('status', 'active') // Fixed: status is 'active', not 'open'
            ->where('start_time', '>', now())
            ->orderBy('start_time')
            ->get()
            ->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'start_time' => $slot->start_time->format('M d, Y h:i A'),
                    'end_time' => $slot->end_time->format('h:i A'),
                ];
            });

        return response()->json($slots);
    }

    /**
     * Store a newly created booking in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'slot_id' => 'required|exists:appointment_slots,id',
            'purpose' => 'required|string|max:255',
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                $slot = AppointmentSlot::where('id', $request->slot_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($slot->status !== 'active') {
                    throw new \Exception('This slot is no longer available.');
                }

                // Generate Standard Token
                $token = 'GEN-' . $request->student_id . '-' . strtoupper(Str::random(4));

                // Create Appointment
                Appointment::create([
                    'student_id' => $request->student_id,
                    'slot_id' => $slot->id,
                    'token' => $token,
                    'purpose' => $request->purpose,
                    'status' => 'approved', 
                ]);

                // Update Slot Status
                $slot->update(['status' => 'blocked']);

                // Log Activity
                \App\Services\ActivityLogger::logBooking(
                    'Admin', // Actor
                    $slot->advisor->name, // Advisor
                    $token
                );
            });

            return redirect()->route('admin.dashboard')
                ->with('success', 'Appointment booked successfully on behalf of student.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified booking from storage.
     */
    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $slot = $appointment->slot;

        // Log the deletion
        \Illuminate\Support\Facades\Log::info('Admin deleted appointment', [
            'admin_id' => \Illuminate\Support\Facades\Auth::id(),
            'appointment_id' => $appointment->id,
            'student_id' => $appointment->student_id,
            'token' => $appointment->token
        ]);

        // Delete appointment
        $appointment->delete();

        // Free up the slot
        if ($slot) {
            $slot->update(['status' => 'active']);
        }

        return back()->with('success', 'Appointment deleted and slot freed successfully.');
    }
}
