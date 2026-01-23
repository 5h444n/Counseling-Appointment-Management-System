<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CalendarEvent;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function fetchEvents()
    {
        $user = Auth::user();
        $events = [];

        // 1. Fetch Personal Notes/Reminders
        $calendarEvents = CalendarEvent::where('user_id', $user->id)->get();
        foreach ($calendarEvents as $event) {
            $events[] = [
                'id' => 'note-' . $event->id, // Prefix to distinguish from appointments
                'title' => $event->title,
                'start' => $event->start_time->toIso8601String(),
                'end' => $event->end_time ? $event->end_time->toIso8601String() : null,
                'color' => $event->color ?? ($event->type == 'reminder' ? '#ef4444' : '#f59e0b'), // Red for reminder, Amber for note
                'extendedProps' => [
                    'type' => 'personal_note',
                    'description' => $event->description,
                    'db_id' => $event->id
                ]
            ];
        }

        // 2. Fetch Appointments (Logic differs by role)
        if ($user->role === 'student') {
            $appointments = Appointment::where('student_id', $user->id)
                ->with(['slot.advisor'])
                ->get();

            foreach ($appointments as $app) {
                $color = match($app->status) {
                    'approved' => '#3b82f6', // Blue
                    'completed' => '#22c55e', // Green
                    'cancelled' => '#9ca3af', // Gray
                    default => '#f97316' // Orange (pending)
                };

                $events[] = [
                    'id' => 'app-' . $app->id,
                    'title' => 'Meeting: ' . ($app->slot->advisor->name ?? 'Advisor'),
                    'start' => $app->slot->start_time->toIso8601String(),
                    'end' => $app->slot->end_time->toIso8601String(),
                    'color' => $color,
                    'url' => route('student.appointments.show', $app->id), // Link to details
                    'extendedProps' => [
                        'type' => 'appointment',
                        'status' => $app->status
                    ]
                ];
            }
        } elseif ($user->role === 'advisor') {
            // For Advisors, we need to join slots
            $appointments = Appointment::whereHas('slot', function($q) use ($user) {
                $q->where('advisor_id', $user->id);
            })->with(['student', 'slot'])->get();

             foreach ($appointments as $app) {
                $color = match($app->status) {
                    'approved' => '#3b82f6', // Blue
                    'completed' => '#22c55e', // Green
                    'cancelled' => '#9ca3af', // Gray
                    default => '#f97316' // Orange (pending)
                };

                $events[] = [
                    'id' => 'app-' . $app->id,
                    'title' => 'Appt: ' . ($app->student->name ?? 'Student'),
                    'start' => $app->slot->start_time->toIso8601String(),
                    'end' => $app->slot->end_time->toIso8601String(),
                    'color' => $color,
                    'extendedProps' => [
                        'type' => 'appointment',
                        'status' => $app->status
                    ]
                ];
            }
        }

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'required|date',
            'type' => 'required|in:note,reminder',
        ]);

        $event = CalendarEvent::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'type' => $request->type,
            'color' => $request->input('color'),
        ]);

        return response()->json(['success' => true, 'event' => $event]);
    }

    public function destroy($id)
    {
        $event = CalendarEvent::where('user_id', Auth::id())->findOrFail($id);
        $event->delete();
        return response()->json(['success' => true]);
    }
}
