<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminStudentController extends Controller
{
    /**
     * Display a listing of students.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'student')->orderBy('name');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('university_id', 'like', "%{$search}%");
            });
        }
        
        // Filter by Department (Optional)
        if ($request->has('department_id') && $request->department_id != '') {
             $query->where('department_id', $request->department_id);
        }

        $students = $query->paginate(15);
        $departments = Department::orderBy('name')->get();

        return view('admin.students.index', compact('students', 'departments'));
    }

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.students.create', compact('departments'));
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'department_id' => ['required', 'exists:departments,id'],
            'university_id' => ['required', 'string', 'max:255', 'unique:users'], // Student ID
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'department_id' => $request->department_id,
            'university_id' => $request->university_id,
            'password' => Hash::make($request->password),
            'role' => 'student',
        ]);

        return redirect()->route('admin.students.index')
            ->with('success', 'Student profile created successfully.');
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit($id)
    {
        $student = User::where('role', 'student')->findOrFail($id);
        $departments = Department::orderBy('name')->get();

        return view('admin.students.edit', compact('student', 'departments'));
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, $id)
    {
        $student = User::where('role', 'student')->findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'university_id' => ['required', 'string', 'max:255', 'unique:users,university_id,' . $id],
            'department_id' => ['required', 'exists:departments,id'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $student->name = $request->name;
        $student->email = $request->email;
        $student->department_id = $request->department_id;
        $student->university_id = $request->university_id;

        if ($request->filled('password')) {
            $student->password = Hash::make($request->password);
        }

        $student->save();

        return redirect()->route('admin.students.index')
            ->with('success', 'Student profile updated successfully.');
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy($id)
    {
        $student = User::where('role', 'student')->findOrFail($id);
        
        // Optional: Check for existing appointments and delete them or prevent deletion
        // For now, we'll allow deletion which might cascade if set up, or throw error if restricted.
        // Assuming cascade or we just delete the user.
        
        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Student profile deleted successfully.');
    }
}
