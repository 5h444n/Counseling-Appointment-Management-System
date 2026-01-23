<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notice;
use Illuminate\Support\Facades\Auth;

class AdminNoticeController extends Controller
{
    /**
     * Display a listing of notices.
     */
    public function index()
    {
        $notices = Notice::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.notices.index', compact('notices'));
    }

    /**
     * Show the form for creating a new notice.
     */
    public function create()
    {
        // Get all potential recipients for selection
        $users = \App\Models\User::whereIn('role', ['student', 'advisor'])
            ->orderBy('name')
            ->select('id', 'name', 'role', 'university_id')
            ->get();
            
        return view('admin.notices.create', compact('users'));
    }

    /**
     * Store a newly created notice in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'user_role' => 'required|in:student,advisor,all,specific',
            'user_id' => 'nullable|required_if:user_role,specific|exists:users,id',
        ]);

        Notice::create([
            'title' => $request->title,
            'content' => $request->content,
            'user_role' => $request->user_role === 'specific' ? 'specific' : $request->user_role,
            'user_id' => $request->user_role === 'specific' ? $request->user_id : null,
        ]);

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice sent successfully.');
    }
}
