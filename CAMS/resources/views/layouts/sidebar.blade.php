<aside class="w-64 bg-slate-900 text-white border-r border-gray-800 hidden md:flex flex-col h-screen fixed left-0 top-0 z-50 transition-all">
    <div class="h-16 flex items-center justify-center border-b border-gray-700 bg-slate-950">
        <div class="text-center">
            <h1 class="text-2xl font-bold tracking-wider text-white">
                CAMS <span class="text-orange-500">UIU</span>
            </h1>
            <p class="text-[10px] text-gray-400 uppercase tracking-widest">Counseling Portal</p>
        </div>
    </div>

    <nav class="flex-1 px-3 py-6 space-y-2 overflow-y-auto">
        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Main Menu</p>

        <a href="{{ route('dashboard') }}"
           class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200
           {{ request()->routeIs('dashboard') ? 'bg-orange-600 text-white shadow-lg shadow-orange-900/50' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            Dashboard
        </a>

        @if(Auth::user()->role === 'advisor')
            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-6 mb-2">Advisor Tools</p>

            <a href="{{ route('advisor.dashboard') }}"
               class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200
               {{ request()->routeIs('advisor.dashboard') ? 'bg-orange-600 text-white shadow-md' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                Appointment Requests
            </a>

            <a href="{{ route('advisor.slots') }}"
               class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200
               {{ request()->routeIs('advisor.slots') ? 'bg-orange-600 text-white shadow-md' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                My Availability
            </a>

            <a href="{{ route('advisor.schedule') }}"
               class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200
               {{ request()->routeIs('advisor.schedule') ? 'bg-orange-600 text-white shadow-md' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                My Schedule
            </a>
            @endif

        @if(Auth::user()->role === 'student')
            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-6 mb-2">Student Menu</p>

            <a href="{{ route('student.advisors.index') }}"
               class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200
               {{ request()->routeIs('student.advisors.*') ? 'bg-orange-600 text-white shadow-md' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Book Appointment
            </a>
            <a href="{{ route('student.appointments.index') }}"
               class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200
               {{ request()->routeIs('student.appointments.index') ? 'bg-orange-600 text-white shadow-md' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                My Appointments
            </a>
        @endif

        @if(Auth::user()->role === 'admin')
            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-6 mb-2">Admin Tools</p>

            <a href="{{ route('admin.activity-logs') }}"
               class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200
               {{ request()->routeIs('admin.activity-logs') ? 'bg-orange-600 text-white shadow-md' : 'text-gray-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Activity Logs
            </a>
        @endif
    </nav>

    <div class="p-4 border-t border-gray-800 bg-slate-950">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold text-lg shadow-md">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="overflow-hidden">
                <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-400 uppercase truncate">{{ Auth::user()->role }} • {{ Auth::user()->department->code ?? 'UIU' }}</p>
            </div>
        </div>
    </div>
</aside>
