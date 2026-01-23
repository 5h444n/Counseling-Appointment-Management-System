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
    public function index()
    {
        $resources = Resource::with('uploader')
            ->latest()
            ->paginate(12);

        $view = Auth::user()->role === 'student' ? 'student.resources.index' : 'advisor.resources.index';
        
        return view($view, compact('resources'));
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
