<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use App\Models\Waitlist;
use App\Models\Department; // <--- ADDED THIS IMPORT
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentBookingController extends Controller
{
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

    public function show($advisorId)
    {
        $advisor = User::where('role', 'advisor')->with('department')->findOrFail($advisorId);

        $slots = AppointmentSlot::where('advisor_id', $advisorId)
            ->where('status', 'active')
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->get();

        $waitlistedSlotIds = Waitlist::where('student_id', Auth::id())
            ->pluck('slot_id')
            ->toArray();

        return view('student.advisors.show', compact('advisor', 'slots', 'waitlistedSlotIds'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'slot_id' => 'required|exists:appointment_slots,id',
            'purpose' => 'required|string|min:10|max:500',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $slot = AppointmentSlot::where('id', $request->slot_id)
                    ->lockForUpdate()
                    ->first();

                if (!$slot || $slot->status !== 'active') {
                    throw new \Exception('Sorry, this slot was just taken by someone else.');
                }

                if ($slot->start_time <= now()) {
                    throw new \Exception('Cannot book a slot that has already started or passed.');
                }

                // Allow re-booking if previous status was 'declined'
                $existingAppointment = Appointment::where('student_id', Auth::id())
                    ->where('slot_id', $slot->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();

                if ($existingAppointment) {
                    throw new \Exception('You have already booked this slot (Check your Pending/Approved list).');
                }

                // Generate a Unique Token (e.g., CSE-8492-X)
                $deptCode = optional(Auth::user()->department)->code ?? 'GEN';
                $userId = Auth::id();
                
                // Generate a serial (Random letter A-Z) and ensure uniqueness
                $maxAttempts = 26; // Maximum 26 letters
                $attempts = 0;
                do {
                    if ($attempts >= $maxAttempts) {
                        throw new \Exception('Unable to generate a unique token. Please try again.');
                    }
                    
                    $serial = chr(random_int(65, 90));
                    $token = strtoupper("{$deptCode}-{$userId}-{$serial}");
                    $attempts++;
                } while (Appointment::where('token', $token)->exists());

                $appointment = Appointment::create([
                    'student_id' => Auth::id(),
                    'slot_id'    => $slot->id,
                    'purpose'    => $request->purpose,
                    'status'     => 'pending',
                    'token'      => $token,
                ]);

                $slot->update(['status' => 'blocked']);

                // Remove from waitlist if they successfully book it
                Waitlist::where('slot_id', $slot->id)->where('student_id', Auth::id())->delete();

                Log::info('Appointment booked successfully', ['id' => $appointment->id]);
            });

            return redirect()->route('dashboard')->with('success', 'Appointment booked successfully! Wait for approval.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function joinWaitlist(Request $request, $slotId)
    {
        $user = Auth::user();
        $slot = AppointmentSlot::findOrFail($slotId);

        if ($slot->status !== 'blocked') {
            return back()->with('error', 'This slot is currently available. You can book it directly.');
        }

        $exists = Waitlist::where('slot_id', $slotId)
            ->where('student_id', $user->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'You are already on the waitlist for this slot.');
        }

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
