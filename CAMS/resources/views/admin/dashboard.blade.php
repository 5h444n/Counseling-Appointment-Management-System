<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header & Actions --}}
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    Welcome back, <span class="text-orange-600">{{ Auth::user()->name }}</span>!
                </h1>
                <p class="text-gray-500 mt-1">Here is what’s happening with the system today.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.export') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download Report
                </a>
            </div>
        </div>
        
        {{-- Flash Messages --}}
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="show = false">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                </span>
            </div>
        @endif

        {{-- Quick Actions --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <a href="{{ route('admin.students.index') }}" class="flex items-center p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div class="ml-4">
                    <h3 class="font-semibold text-gray-800">Manage Students</h3>
                    <p class="text-xs text-gray-500">Add, Edit, Delete</p>
                </div>
            </a>

            <a href="{{ route('admin.faculty.index') }}" class="flex items-center p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition">
                <div class="p-3 rounded-full bg-teal-100 text-teal-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div class="ml-4">
                    <h3 class="font-semibold text-gray-800">Manage Faculty</h3>
                    <p class="text-xs text-gray-500">Advisors & Staff</p>
                </div>
            </a>

            <a href="{{ route('admin.bookings.create') }}" class="flex items-center p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition">
                <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div class="ml-4">
                    <h3 class="font-semibold text-gray-800">Book Appointment</h3>
                    <p class="text-xs text-gray-500">On behalf of Student</p>
                </div>
            </a>

            <a href="{{ route('admin.notices.index') }}" class="flex items-center p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                   <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                </div>
                <div class="ml-4">
                    <h3 class="font-semibold text-gray-800">System Notices</h3>
                    <p class="text-xs text-gray-500">Manage Announcements</p>
                </div>
            </a>
            
             <a href="{{ route('admin.activity-logs') }}" class="flex items-center p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition">
                <div class="p-3 rounded-full bg-gray-100 text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <div class="ml-4">
                    <h3 class="font-semibold text-gray-800">Activity Logs</h3>
                    <p class="text-xs text-gray-500">View Audit Trail</p>
                </div>
            </a>
        </div>

        {{-- Consolidated Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            
            {{-- Widget 1: Total Bookings --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Bookings</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalAppointments }}</p>
                    </div>
                </div>
            </div>

            {{-- Widget 2: Pending Requests --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Pending Requests</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $pendingRequests }}</p>
                    </div>
                </div>
            </div>

            {{-- Widget 3: Counseling Hours --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Counseling Hours</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalHours }}h</p>
                    </div>
                </div>
            </div>

            {{-- Widget 4: Top Advisor --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Top Advisor</p>
                        <p class="text-lg font-bold text-gray-900 truncate max-w-[140px]" title="{{ $topAdvisorName }}">
                            {{ Str::limit($topAdvisorName, 12) }}
                        </p>
                        <p class="text-xs text-gray-500">{{ $topAdvisorCount }} bookings</p>
                    </div>
                </div>
            </div>

            {{-- Widget 5: Students Registered --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Students Registered</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalStudents }}</p>
                    </div>
                </div>
            </div>

            {{-- Widget 6: Faculty Members --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-teal-100 text-teal-600">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2-2v16m14 0h2m-2 0h-5m-9 0H3m2 0h-5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Faculty Members</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalFaculty }}</p>
                    </div>
                </div>
            </div>

            {{-- Widget 7: Notices Sent --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Notices Sent</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalNotices }}</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- Recent Activity logs preview could go here in future --}}
        
    </div>
</x-app-layout>
