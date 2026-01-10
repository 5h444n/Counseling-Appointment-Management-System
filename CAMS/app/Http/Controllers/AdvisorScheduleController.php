<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;

class AdvisorScheduleController extends Controller
{
    public function index()
    {
        $advisorId = Auth::id();

        // 1. Upcoming Appointments
        // Status is 'approved' AND start_time is in the future
        $upcoming = Appointment::whereHas('slot', function ($q) use ($advisorId) {
                $q->where('advisor_id', $advisorId);
            })
            ->where('status', 'approved')
            ->whereHas('slot', function ($q) {
                $q->where('start_time', '>=', now());
            })
            ->with(['student', 'slot']) // Eager load relationships
            ->get()
            ->sortBy(function($appointment) {
                return $appointment->slot->start_time;
            });

        // 2. History (Past) Appointments
        // Status is 'completed' OR ('approved' but time has passed)
        $history = Appointment::whereHas('slot', function ($q) use ($advisorId) {
                $q->where('advisor_id', $advisorId);
            })
            ->where(function($q) {
                $q->where('status', 'completed')
                  ->orWhere(function($subQ) {
                      $subQ->where('status', 'approved')
                           ->whereHas('slot', function($timeQ) {
                               $timeQ->where('start_time', '<', now());
                           });
                  });
            })
            ->with(['student', 'slot'])
            ->get()
            ->sortByDesc(function($appointment) {
                return $appointment->slot->start_time;
            });

        return view('advisor.schedule', compact('upcoming', 'history'));
    }
}
