<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log an activity.
     *
     * @param string $action The action type (e.g., 'login', 'book_appointment')
     * @param string $description Human-readable description
     * @param int|null $userId Optional user ID (defaults to authenticated user)
     * @return ActivityLog|null
     * @return ActivityLog|null Returns ActivityLog on success, null on failure
     */
    public static function log(string $action, string $description, ?int $userId = null): ?ActivityLog
    {
        try {
            return ActivityLog::create([
                'user_id' => $userId ?? Auth::id(),
                'action' => $action,
                'description' => $description,
                'ip_address' => Request::ip(),
            ]);
        } catch (\Illuminate\Database\QueryException | \PDOException $e) {
            // Catch database-related exceptions specifically
            Log::error('Failed to create activity log', [
                'action' => $action,
                'description' => $description,
                'user_id' => $userId ?? Auth::id(),
                'ip_address' => Request::ip(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Return null so that audit logging failures don't break core functionality
            return null;
        }
    }

    /**
     * Log a user login event.
     */
    public static function logLogin(int $userId, string $userName): ?ActivityLog
    {
        return self::log('login', "{$userName} logged into the system", $userId);
    }

    /**
     * Log an appointment booking event.
     */
    public static function logBooking(string $studentName, string $advisorName, string $token): ?ActivityLog
    {
        return self::log('book_appointment', "{$studentName} booked an appointment with {$advisorName} (Token: {$token})");
    }

    /**
     * Log an appointment cancellation event.
     */
    public static function logCancellation(string $studentName, string $advisorName, string $token): ?ActivityLog
    {
        return self::log('cancel_appointment', "{$studentName} cancelled appointment with {$advisorName} (Token: {$token})");
    }
}
