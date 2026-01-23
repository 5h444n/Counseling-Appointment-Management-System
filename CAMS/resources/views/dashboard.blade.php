<x-app-layout>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-sm flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0 text-green-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
                <button @click="show = false" class="text-green-500 hover:text-green-700">&times;</button>
            </div>
        @endif
    </div>

    <div class="mb-8 px-4 sm:px-6 lg:px-8 mt-6">
        <h1 class="text-3xl font-bold text-gray-800">
            Welcome back, <span class="text-orange-600">{{ Auth::user()->name }}</span>!
        </h1>
        <p class="text-gray-500 mt-1">Here is what’s happening with your schedule today.</p>

        @if(isset($notices) && $notices->isNotEmpty())
        <div class="mt-6 space-y-3">
            @foreach($notices as $notice)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">{{ $notice->title }}</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>{{ $notice->content }}</p>
                            </div>
                            <div class="mt-1 text-xs text-yellow-500">
                                Total {{ $notice->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-orange-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">University ID</p>
                    <p class="text-xl font-bold text-gray-800 mt-1">{{ Auth::user()->university_id ?? 'Not Set' }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ Auth::user()->department->name ?? 'General' }}</p>
                </div>
                <div class="p-2 bg-orange-50 rounded-lg text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .88.39 1.67 1 2.22V11a2 2 0 104 0V8.22c.61-.55 1-1.34 1-2.22m-9 10h.01"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-600">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Next Appointment</p>
                    @if(isset($nextAppointment) && $nextAppointment)
                        <p class="text-xl font-bold text-gray-800 mt-1">{{ $nextAppointment->slot->start_time->format('M d, h:i A') }}</p>
                        <p class="text-sm font-mono text-orange-600 mt-1">Token: {{ $nextAppointment->token }}</p>
                    @else
                        <p class="text-xl font-bold text-gray-800 mt-1">No Upcoming</p>
                        <p class="text-sm text-gray-500 mt-1">Check back later</p>
                    @endif
                </div>
                <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Account Status</p>
                    <p class="text-xl font-bold text-green-600 mt-1">Active</p>
                    <p class="text-sm text-gray-500 mt-1">No holds on account</p>
                </div>
                <div class="p-2 bg-green-50 rounded-lg text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
            <div class="p-6 border-b border-gray-100 bg-gray-50">
                <h3 class="font-bold text-gray-800">Quick Actions</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <a href="{{ route('profile.edit') }}" class="flex items-center p-4 border rounded-lg hover:bg-orange-50 hover:border-orange-200 transition-all group cursor-pointer">
                        <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 group-hover:bg-orange-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold text-gray-800">Update Profile</h4>
                            <p class="text-sm text-gray-500">Change password or details</p>
                        </div>
                    </a>

                    @if(Auth::user()->role === 'advisor')
                        <a href="{{ route('advisor.slots') }}" class="flex items-center p-4 border rounded-lg hover:bg-blue-50 hover:border-blue-200 transition-all group cursor-pointer">
                            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="font-semibold text-gray-800">Manage Slots</h4>
                                <p class="text-sm text-gray-500">Add or remove availability</p>
                            </div>
                        </a>
                    @endif

                    @if(Auth::user()->role === 'student')
                        <a href="{{ route('student.advisors.index') }}" class="flex items-center p-4 border rounded-lg hover:bg-blue-50 hover:border-blue-200 transition-all group cursor-pointer">
                            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="font-semibold text-gray-800">Book Appointment</h4>
                                <p class="text-sm text-gray-500">Find advisor and schedule</p>
                            </div>
                        </a>
                    @endif

                </div>
            </div>
        </div>
    </div>


    {{-- Calendar Integration --}}
    <div class="px-4 sm:px-6 lg:px-8 mt-8 mb-12">
        <x-calendar />
    </div>
</x-app-layout>
