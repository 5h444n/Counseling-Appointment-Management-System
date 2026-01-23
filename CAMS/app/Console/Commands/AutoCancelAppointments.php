<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ActivityLogger;

class AutoCancelAppointments extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'appointments:autocancel';

    /**
     * The console command description.
     */
    protected $description = 'Clean up stale pending requests and mark no-shows';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Auto-Cancellation Service...');

        // TASK 1: Auto-Cancel Pending Requests older than 24 hours
        $expiredTime = Carbon::now()->subHours(24);

        $staleAppointments = Appointment::with('slot.advisor', 'student')
            ->where('status', 'pending')
            ->where('created_at', '<', $expiredTime)
            ->get();

        foreach ($staleAppointments as $app) {
            DB::transaction(function () use ($app) {
                // Mark appointment as cancelled
                $app->update(['status' => 'cancelled']);

                // Free up the slot so others can book it
                if ($app->slot) {
                    $app->slot->update(['status' => 'active']);
                } else {
                    Log::warning("Appointment #{$app->id} has no associated slot.");
                }

                // Log the cancellation
                if ($app->student && $app->slot && $app->slot->advisor) {
                    ActivityLogger::logCancellation(
                        $app->student->name,
                        $app->slot->advisor->name,
                        $app->token
                    );
                }
            });

            $this->info("Cancelled Stale Request: ID {$app->id}");
            Log::info("Auto-Cancelled Appointment #{$app->id} due to 24h timeout.");
        }

        // TASK 2: Mark "No Show" (10 mins past start time)
        // Logic: If now > (Start Time + 10 mins) AND Status is still 'approved' (not completed/arrived)
        $tenMinsAgo = Carbon::now()->subMinutes(10);

        $noShowAppointments = Appointment::with('slot')
            ->where('status', 'approved') // Only check confirmed bookings
            ->whereHas('slot', function ($query) use ($tenMinsAgo) {
                $query->where('start_time', '<', $tenMinsAgo);
            })
            ->get();

        foreach ($noShowAppointments as $app) {
            DB::transaction(function () use ($app) {
                $app->update(['status' => 'no_show']);

                // Free up the slot so others can book it, similar to cancellation
                if ($app->slot) {
                    $app->slot->update(['status' => 'active']);
                }
            });
            $this->info("Marked No-Show: ID {$app->id}");
            Log::info("Marked Appointment #{$app->id} as No-Show.");
        }

        $this->info('Auto-Cancellation Service Complete.');
    }
}
