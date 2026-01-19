<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use App\Models\Waitlist;
use App\Models\Department; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        ]);

        try {
            DB::transaction(function () use ($request) {
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

                // 7. Update Slot Status
                $slot->update(['status' => 'blocked']);

                // 8. Remove from waitlist if this student was on it
                Waitlist::where('slot_id', $slot->id)->where('student_id', Auth::id())->delete();

                Log::info('Appointment booked successfully', ['id' => $appointment->id]);
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

    public function myAppointments()
    {
        $appointments = Appointment::where('student_id', Auth::id())
            ->with(['slot.advisor.department'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.appointments.index', compact('appointments'));
    }
}
