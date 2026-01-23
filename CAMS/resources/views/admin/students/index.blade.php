<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Manage Students</h1>
            <a href="{{ route('admin.students.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add New Student</a>
        </div>

        {{-- Search & Filter --}}
        <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-100 flex flex-col md:flex-row gap-4">
             <form action="{{ route('admin.students.index') }}" method="GET" class="flex-1 flex gap-4">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, ID or email..." 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="w-48">
                    <select name="department_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">Filter</button>
                @if(request()->has('search') || request()->has('department_id'))
                    <a href="{{ route('admin.students.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Clear</a>
                @endif
             </form>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($students as $student)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $student->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $student->university_id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $student->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $student->department->code ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.students.edit', $student->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</a>
                            <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4">
                {{ $students->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
