<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard with analytics widgets.
     *
     * The returned view receives the following data:
     * - string $topAdvisorName     Name of the most booked advisor or 'N/A'.
     * - int    $topAdvisorCount    Number of appointments for the top advisor.
     * - float  $totalHours         Total counseling hours across completed appointments.
     * - int    $totalAppointments  Total number of appointments.
     * - int    $pendingRequests    Number of appointments with pending status.
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \Throwable If an error occurs while retrieving dashboard statistics.
     */
    public function index()
    {
        // Query 1: Most Booked Advisor
        $topAdvisor = Appointment::select('slots.advisor_id', DB::raw('count(*) as total'))
            ->join('appointment_slots as slots', 'appointments.slot_id', '=', 'slots.id')
            ->groupBy('slots.advisor_id')
            ->orderByDesc('total')
            ->first();

        // The aggregated query above only returns advisor_id and total count, not full Appointment models.
        // We therefore manually load the advisor user using advisor_id from the result.
        $topAdvisorName = 'N/A';
        $topAdvisorCount = 0;
        
        if ($topAdvisor) {
            $advisor = User::find($topAdvisor->advisor_id);
            $topAdvisorName = $advisor ? $advisor->name : 'Unknown';
            $topAdvisorCount = $topAdvisor->total;
        }

        // Query 2: Total Counseling Hours (Completed appointments)
        // We iterate and sum because diffInMinutes needs Carbon instances
        $completedAppointments = Appointment::where('status', 'completed')
            ->with('slot')
            ->get();
            
        $totalMinutes = $completedAppointments->sum(function($app) {
             if ($app->slot && $app->slot->start_time && $app->slot->end_time) {
                 return $app->slot->start_time->diffInMinutes($app->slot->end_time);
             }
             return 0;
        });
        
        $totalHours = round($totalMinutes / 60, 1);
        
        // Additional Stats
        $totalAppointments = Appointment::count();
        $pendingRequests = Appointment::where('status', 'pending')->count();
        
        // New Summaries
        $totalStudents = User::where('role', 'student')->count();
        $totalFaculty = User::where('role', 'advisor')->count();
        $totalNotices = \App\Models\Notice::count();

        return view('admin.dashboard', compact(
            'topAdvisorName', 
            'topAdvisorCount', 
            'totalHours', 
            'totalAppointments', 
            'pendingRequests',
            'totalStudents',
            'totalFaculty',
            'totalNotices'
        ));
    }

    /**
     * Export all appointments as CSV.
     */
    public function export()
    {
        $fileName = 'appointment_report_' . date('Y-m-d_H-i-s') . '.csv';
        
        return new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($handle, [
                'ID', 
                'Token', 
                'Student Name', 
                'Advisor Name', 
                'Department', 
                'Date', 
                'Time', 
                'Status', 
                'Purpose', 
                'Created At'
            ]);

            // Fetch Data in Chunks to save memory
            Appointment::with(['student', 'slot.advisor.department'])
                ->orderBy('created_at', 'desc')
                ->chunk(100, function ($appointments) use ($handle) {
                    foreach ($appointments as $app) {
                        $studentName = $app->student->name ?? 'N/A';
                        $advisorName = optional($app->slot)->advisor->name ?? 'N/A';
                        $deptCode = optional(optional($app->slot)->advisor)->department->code ?? 'N/A';
                        $slotDate = optional($app->slot)->start_time ? $app->slot->start_time->format('Y-m-d') : 'N/A';
                        $slotTime = optional($app->slot)->start_time && optional($app->slot)->end_time 
                            ? $app->slot->start_time->format('H:i') . ' - ' . $app->slot->end_time->format('H:i')
                            : 'N/A';
                        
                        fputcsv($handle, [
                            $app->id,
                            $app->token,
                            $studentName,
                            $advisorName,
                            $deptCode,
                            $slotDate,
                            $slotTime,
                            ucfirst($app->status),
                            $app->purpose,
                            $app->created_at->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
