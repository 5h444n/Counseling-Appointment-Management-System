<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;

class AdminActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs.
     */
    public function index()
    {
        $logs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('admin.activity-logs.index', compact('logs'));
    }
}
