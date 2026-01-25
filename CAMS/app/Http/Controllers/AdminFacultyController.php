<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminFacultyController extends Controller
{
    /**
     * Display a listing of all faculty (advisors).
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'advisor')->with('department')->orderBy('name');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

         if ($request->has('department_id') && $request->department_id != '') {
             $query->where('department_id', $request->department_id);
        }

        $faculty = $query->paginate(20);
        $departments = Department::orderBy('name')->get();

        return view('admin.faculty.index', compact('faculty', 'departments'));
    }

    /**
     * Show the form for creating a new faculty.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.faculty.create', compact('departments'));
    }

    /**
     * Store a newly created faculty in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'department_id' => ['required', 'exists:departments,id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'department_id' => $request->department_id,
            'password' => Hash::make($request->password),
            'role' => 'advisor', // Automatically assign advisor role
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Faculty member created successfully. They can now log in.');
    }

    /**
     * Show the form for editing the specified faculty.
     */
    public function edit($id)
    {
        $faculty = User::where('role', 'advisor')->findOrFail($id);
        $departments = Department::orderBy('name')->get();

        return view('admin.faculty.edit', compact('faculty', 'departments'));
    }

    /**
     * Update the specified faculty in storage.
     */
    public function update(Request $request, $id)
    {
        $faculty = User::where('role', 'advisor')->findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'department_id' => ['required', 'exists:departments,id'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $faculty->name = $request->name;
        $faculty->email = $request->email;
        $faculty->department_id = $request->department_id;

        // Only update password if provided
        if ($request->filled('password')) {
            $faculty->password = Hash::make($request->password);
        }

        $faculty->save();

        return redirect()->route('admin.dashboard')
            ->with('success', 'Faculty member updated successfully.');
    }

    /**
     * Remove the specified faculty from storage.
     */
    public function destroy($id)
    {
        $faculty = User::where('role', 'advisor')->findOrFail($id);
        
        // Check if faculty has any appointments/slots before deleting
        if ($faculty->slots()->exists()) {
            return back()->with('error', 'Cannot delete faculty with existing appointment slots. Please delete their slots first.');
        }

        // Log the deletion
        \Illuminate\Support\Facades\Log::info('Admin deleted faculty', [
            'admin_id' => \Illuminate\Support\Facades\Auth::id(),
            'faculty_id' => $faculty->id,
            'faculty_email' => $faculty->email,
            'faculty_name' => $faculty->name
        ]);

        $faculty->delete();

        return redirect()->route('admin.dashboard')
            ->with('success', 'Faculty member deleted successfully.');
    }
}
