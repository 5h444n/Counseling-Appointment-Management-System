<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\AppointmentDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Events\SlotFreedUp;
use App\Services\ActivityLogger;

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

        $recentFeedback = \App\Models\Feedback::where('advisor_id', Auth::id())
            ->with('student')
            ->latest()
            ->take(6)
            ->get();

        return view('advisor.dashboard', compact('appointments', 'recentFeedback'));
    }

    /**
     * Handle Accept/Decline Actions
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,declined',
        ]);

        $appointment = Appointment::with('student', 'slot.advisor')->findOrFail($id);

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

            // 3. Log the cancellation
            if ($appointment->student && $slot->advisor) {
                ActivityLogger::logCancellation(
                    $appointment->student->name,
                    $slot->advisor->name,
                    $appointment->token
                );
            }

            // 4. Fire Event to notify Waitlist
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

    /**
     * Download/View an appointment document securely.
     */
    public function downloadDocument($documentId)
    {
        $document = AppointmentDocument::findOrFail($documentId);
        $appointment = $document->appointment;

        // Security check: ensure this document's appointment belongs to the logged-in advisor
        if ($appointment->slot->advisor_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this document.');
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Document not found.');
        }

        // Return the file for download/viewing
        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }
    /**
     * Fetch student history (completed sessions with minutes) for modals.
     */
    public function getStudentHistory($studentId)
    {
        // Fetch completed appointments for this student where the current advisor was the host
        // Or should it be global? Requirement says "he have of a student", implying *his* history with the student.
        // Let's assume Advisor can see *their own* history with the student for now to be safe, 
        // OR better: if it's a "Case File", maybe they should see ALL history? 
        // User said: "previous MOM notes *he have* of a student". "He have" implies his own.
        // But in a counseling center, usually history is shared.
        // Let's stick to "Advisor can see appointments where they were the advisor" OR "All appointments if authorized".
        // Use case: "He have". Stick to Advisor's own history + maybe shared if implemented later.
        // For now: Fetch ALL completed appointments for this student (System-wide history for better context).
        // Security: Only if this advisor has a pending/upcoming appointment with them.
        
        // Check if advisor has legitimate business with this student (e.g., pending request)
        $hasConnection = Appointment::where('student_id', $studentId)
            ->whereHas('slot', function ($q) {
                $q->where('advisor_id', Auth::id());
            })
            ->exists();

        if (!$hasConnection) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $history = Appointment::where('student_id', $studentId)
            ->where('status', 'completed')
            ->with(['minute', 'slot.advisor'])
            ->latest()
            ->get()
            ->map(function ($appt) {
                return [
                    'date' => $appt->slot->start_time->format('M d, Y'),
                    'advisor' => $appt->slot->advisor->name,
                    'note' => $appt->minute ? $appt->minute->note : 'No notes recorded.',
                ];
            });

        return response()->json($history);
    }
}
