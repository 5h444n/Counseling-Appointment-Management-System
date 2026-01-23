<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AdminActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        // Search by user name or action type
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('action_type', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Date range filters
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        // Action type filter (e.g., login, booking, cancellation)
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->input('action_type'));
        }

        // User role filter
        if ($request->filled('role')) {
            $role = $request->input('role');

            $query->whereHas('user', function ($uq) use ($role) {
                $uq->where('role', $role);
            });
        }

        $logs = $query
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->appends($request->query());

        return view('admin.activity-logs.index', compact('logs'));
    }
}
