<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Find an Advisor</h1>
            <p class="text-gray-500 text-sm">Search for faculty members by name or department.</p>
        </div>

        <div class="mb-8 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <form method="GET" action="{{ route('student.advisors.index') }}" class="flex flex-col md:flex-row gap-4">

                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="pl-10 block w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500 shadow-sm"
                           placeholder="Search advisor name...">
                </div>

                <div class="w-full md:w-64">
                    <select name="department_id" class="block w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="bg-slate-900 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-orange-600 transition-colors shadow-lg shadow-gray-200">
                    Filter
                </button>

                @if(request()->filled('search') || request()->filled('department_id'))
                    <a href="{{ route('student.advisors.index') }}" class="flex items-center justify-center px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($advisors as $advisor)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-orange-200 transition-all group">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-14 h-14 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xl group-hover:bg-orange-600 transition-colors">
                            {{ substr($advisor->name, 0, 1) }}
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">{{ $advisor->name }}</h2>
                            <p class="text-xs font-bold text-orange-600 uppercase tracking-wide bg-orange-50 inline-block px-2 py-1 rounded-md mt-1">
                                {{ $advisor->department->code ?? 'Faculty' }}
                            </p>
                        </div>
                    </div>

                    <div class="text-sm text-gray-500 mb-6 space-y-1">
                        <p class="flex items-center"><span class="font-medium text-gray-700 w-16">ID:</span> {{ $advisor->university_id ?? 'N/A' }}</p>
                        <p class="flex items-center"><span class="font-medium text-gray-700 w-16">Email:</span> {{ $advisor->email }}</p>
                    </div>

                    <a href="{{ route('student.advisors.show', $advisor->id) }}" class="block w-full text-center bg-slate-900 text-white font-medium py-2.5 rounded-lg hover:bg-orange-600 transition-colors shadow-lg shadow-gray-200">
                        View Schedule &rarr;
                    </a>
                </div>
            @empty
                <div class="col-span-3 text-center py-16 bg-white rounded-xl border border-dashed border-gray-300">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="mt-2 text-gray-500 font-medium">No advisors found matching your search.</p>
                    <a href="{{ route('student.advisors.index') }}" class="text-orange-600 hover:text-orange-800 text-sm mt-1 inline-block">Clear filters</a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
