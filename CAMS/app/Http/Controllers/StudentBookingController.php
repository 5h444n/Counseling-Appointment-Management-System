<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use App\Models\AppointmentDocument;
use App\Models\Waitlist;
use App\Models\Department; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Events\SlotFreedUp;

class StudentBookingController extends Controller
{
    /**
     * Display a listing of advisors with filters.
     */
    public function index(Request $request)
    {
        // 1. Fetch Departments for the Filter Dropdown
        $departments = Department::all();

        // 2. Build the Advisor Query
        $query = User::where('role', 'advisor')->with('department');

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->get('department_id'));
        }

        $advisors = $query->paginate(12);

        // 3. Pass both 'advisors' AND 'departments' to the view
        return view('student.advisors.index', compact('advisors', 'departments'));
    }

    /**
     * Show the advisor's available slots.
     */
    public function show($advisorId)
    {
        $advisor = User::where('role', 'advisor')->with('department')->findOrFail($advisorId);

        // Fetch slots that are Active (Open) OR Blocked (to show Waitlist option)
        $slots = AppointmentSlot::where('advisor_id', $advisorId)
            ->where('status', 'active')
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->get();

        // Get list of slots this student is already waitlisted for (to show "On Waitlist" badge)
        $waitlistedSlotIds = Waitlist::where('student_id', Auth::id())
            ->pluck('slot_id')
            ->toArray();

        return view('student.advisors.show', compact('advisor', 'slots', 'waitlistedSlotIds'));
    }

    /**
     * Store a newly created booking (Appointment) in storage.
     */
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'slot_id' => 'required|exists:appointment_slots,id',
            'purpose' => 'required|string|min:10|max:500', // Note: Minimum 10 characters required!
            'document' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,jpg,jpeg,png,gif,bmp,svg|max:102400', // Max 100MB
        ]);

        try {
            $appointment = DB::transaction(function () use ($request) {
                // 2. Lock the slot to prevent double booking
                $slot = AppointmentSlot::where('id', $request->slot_id)
                    ->lockForUpdate()
                    ->first();

                // 3. Check availability
                if (!$slot || $slot->status !== 'active') {
                    throw new \Exception('Sorry, this slot was just taken by someone else.');
                }

                if ($slot->start_time <= now()) {
                    throw new \Exception('Cannot book a slot that has already started or passed.');
                }

                // 4. Check for existing booking (Allow re-booking if previous was declined)
                $existingAppointment = Appointment::where('student_id', Auth::id())
                    ->where('slot_id', $slot->id)
                    ->whereIn('status', ['pending', 'approved']) // Only block if Pending or Approved
                    ->exists();

                if ($existingAppointment) {
                    throw new \Exception('You have already booked this slot (Check your Pending/Approved list).');
                }

                // 5. Generate a Unique Token
                $deptCode = optional(Auth::user()->department)->code ?? 'GEN';
                $userId = Auth::id();
                
                $maxAttempts = 26; 
                $attempts = 0;
                do {
                    if ($attempts >= $maxAttempts) {
                        throw new \Exception('Unable to generate a unique token. Please try again.');
                    }
                    
                    $serial = chr(random_int(65, 90)); // Random A-Z
                    $token = strtoupper("{$deptCode}-{$userId}-{$serial}");
                    $attempts++;
                } while (Appointment::where('token', $token)->exists());

                // 6. Create Appointment
                $appointment = Appointment::create([
                    'student_id' => Auth::id(),
                    'slot_id'    => $slot->id,
                    'purpose'    => $request->purpose,
                    'status'     => 'pending',
                    'token'      => $token,
                ]);

                // 7. Handle file upload if present
                if ($request->hasFile('document')) {
                    $file = $request->file('document');
                    $originalName = $file->getClientOriginalName();
                    
                    // Store file in storage/app/public/appointment_documents
                    $filePath = $file->store('appointment_documents', 'public');
                    
                    // Save document record
                    AppointmentDocument::create([
                        'appointment_id' => $appointment->id,
                        'file_path' => $filePath,
                        'original_name' => $originalName,
                    ]);
                    
                    Log::info('Document uploaded', ['appointment_id' => $appointment->id, 'file' => $originalName]);
                }

                // 8. Update Slot Status
                $slot->update(['status' => 'blocked']);

                // 9. Remove from waitlist if this student was on it
                Waitlist::where('slot_id', $slot->id)->where('student_id', Auth::id())->delete();

                Log::info('Appointment booked successfully', ['id' => $appointment->id]);
                
                return $appointment;
            });

            return redirect()->route('dashboard')->with('success', 'Appointment booked successfully! Wait for approval.');

        } catch (\Exception $e) {
            // Log the specific error for debugging
            Log::error('Booking Failed: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Add student to waitlist for a blocked slot.
     */
    public function joinWaitlist(Request $request, $slotId)
    {
        $user = Auth::user();
        $slot = AppointmentSlot::findOrFail($slotId);

        // Only allow waitlist if slot is actually blocked (booked)
        if ($slot->status !== 'blocked') {
            return back()->with('error', 'This slot is currently available. You can book it directly.');
        }

        // Check if already on waitlist
        $exists = Waitlist::where('slot_id', $slotId)
            ->where('student_id', $user->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'You are already on the waitlist for this slot.');
        }

        // Add to waitlist
        Waitlist::create([
            'slot_id' => $slotId,
            'student_id' => $user->id,
        ]);

        return back()->with('success', 'You have joined the waitlist. We will notify you if it opens up.');
    }

    public function myAppointments(Request $request)
    {
        $now = now();
        $tab = $request->get('tab', 'upcoming');

        $query = Appointment::where('student_id', Auth::id())
            ->with(['slot.advisor.department']);

        if ($tab === 'upcoming') {
            // Upcoming: future appointments that are still pending or approved
            $appointments = $query->whereHas('slot', fn($q) => $q->where('start_time', '>', $now))
                ->whereIn('status', ['pending', 'approved'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Past: appointments where slot time has passed OR status is completed/declined/cancelled/no_show
            $appointments = Appointment::where('student_id', Auth::id())
                ->with(['slot.advisor.department'])
                ->where(function($q) use ($now) {
                    $q->whereHas('slot', fn($sq) => $sq->where('start_time', '<=', $now))
                      ->orWhereIn('status', ['completed', 'declined', 'cancelled', 'no_show']);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('student.appointments.index', compact('appointments', 'tab'));
    }

    /**
     * Cancel an upcoming appointment.
     */
    public function cancel(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($id) {
                // Lock the appointment row for update to prevent race conditions
                // Use lockForUpdate to prevent concurrent cancellations
                $appointment = Appointment::where('id', $id)
                    ->where('student_id', Auth::id())
                    ->lockForUpdate()
                    ->firstOrFail();

                // Re-check status inside transaction after acquiring lock
                if (!in_array($appointment->status, ['pending', 'approved'])) {
                    throw new \RuntimeException('This appointment cannot be cancelled.');
                }

                // Lock and load the slot to check time
                $slot = AppointmentSlot::where('id', $appointment->slot_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Prevent cancelling past appointments
                if ($slot->start_time <= now()) {
                    throw new \RuntimeException('Cannot cancel an appointment that has already started.');
                }

                // 1. Update appointment status to cancelled
                $appointment->update(['status' => 'cancelled']);

                // 2. Free up the slot
                $slot->update(['status' => 'active']);

                // 3. Fire event to notify waitlist (same pattern as AdvisorAppointmentController)
                event(new SlotFreedUp($slot));

                Log::info("Student cancelled appointment", [
                    'appointment_id' => $appointment->id,
                    'student_id' => Auth::id(),
                    'slot_id' => $slot->id
                ]);
            });

            return back()->with('success', 'Appointment cancelled successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Appointment not found or doesn't belong to this student - return 404
            abort(404);
        } catch (\RuntimeException $e) {
            // Business logic validation failures
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Cancel Failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to cancel appointment. Please try again.');
        }
    }
}
