<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    /**
     * Display a listing of resources.
     * Shared for Students (view only) and Advisors/Admins (manage).
     */
    public function index(Request $request)
    {
        $query = Resource::with('uploader');

        // Filter by Category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Filter by Advisor
        if ($request->has('advisor_id') && $request->advisor_id) {
            $query->where('uploaded_by', $request->advisor_id);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $resources = $query->latest()->paginate(12);
        
        // Get list of advisors who have uploaded resources for the filter
        $advisors = \App\Models\User::where('role', 'advisor')
            ->whereHas('resources') // Only advisors with uploads
            ->get();

        $view = 'student.resources.index';
        if (Auth::user()->role === 'advisor') {
            $view = 'advisor.resources.index';
        } elseif (Auth::user()->role === 'admin') {
            $view = 'advisor.resources.index'; // Share advisor view for Admin
        }
        
        return view($view, compact('resources', 'advisors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:Academic,Mental Health,Wellness,Career,Other',
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,jpg,jpeg,png|max:51200', // 50MB
            'description' => 'nullable|string',
        ]);

        $path = $request->file('file')->store('resources', 'public');

        Resource::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $path,
            'category' => $request->category,
            'uploaded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Resource uploaded successfully!');
    }

    /**
     * Download the specified resource.
     */
    public function download(Resource $resource)
    {
        if (!Storage::disk('public')->exists($resource->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download($resource->file_path, $resource->title . '.' . pathinfo($resource->file_path, PATHINFO_EXTENSION));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Resource $resource)
    {
        if (Auth::user()->role !== 'admin' && Auth::id() !== $resource->uploaded_by) {
            abort(403, 'Unauthorized.');
        }

        // Delete file
        if (Storage::disk('public')->exists($resource->file_path)) {
            Storage::disk('public')->delete($resource->file_path);
        }

        $resource->delete();

        return back()->with('success', 'Resource deleted successfully.');
    }
}
