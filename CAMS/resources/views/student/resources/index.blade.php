<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-800">Wellness Hub</h1>
            <p class="text-gray-500 mt-2">Explore our library of self-help guides, academic resources, and mental health support.</p>
            
            {{-- Search & Filter --}}
            <div class="mt-6 max-w-xl mx-auto">
                <form action="{{ route('student.resources.index') }}" method="GET" class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search query..." class="w-full rounded-full border-gray-300 pl-4 pr-12 py-3 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                    <button type="submit" class="absolute right-2 top-2 bg-orange-500 text-white p-1.5 rounded-full hover:bg-orange-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </form>
                {{-- Categories --}}
                <div class="flex flex-wrap justify-center gap-2 mt-4">
                    <a href="{{ route('student.resources.index') }}" class="px-3 py-1 bg-white border border-gray-200 rounded-full text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors {{ !request('category') ? 'bg-orange-50 text-orange-700 border-orange-200' : '' }}">All</a>
                    @foreach(['Academic', 'Mental Health', 'Wellness', 'Career', 'Other'] as $cat)
                        <a href="{{ route('student.resources.index', ['category' => $cat]) }}" 
                           class="px-3 py-1 bg-white border border-gray-200 rounded-full text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors {{ request('category') == $cat ? 'bg-orange-50 text-orange-700 border-orange-200' : '' }}">
                            {{ $cat }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        @if($resources->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($resources as $resource)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow flex flex-col h-full">
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-md text-xs font-medium">{{ $resource->category }}</span>
                                <span class="text-xs text-gray-400">{{ $resource->created_at->diffForHumans() }}</span>
                            </div>
                            
                            <h3 class="text-lg font-bold text-gray-800 mb-2">{{ $resource->title }}</h3>
                            <p class="text-sm text-gray-500 line-clamp-3 mb-4">{{ $resource->description }}</p>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-50 flex items-center justify-between">
                            <div class="flex items-center text-xs text-gray-400">
                                @if(Str::endsWith($resource->file_path, '.pdf'))
                                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg> PDF
                                @else
                                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> Document
                                @endif
                            </div>
                            <a href="{{ route('resources.download', $resource->id) }}" class="inline-flex items-center text-sm font-medium text-orange-600 hover:text-orange-800">
                                Download
                                <svg class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-8">
                {{ $resources->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-20 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <h3 class="text-lg font-medium text-gray-900">No resources found</h3>
                <p class="text-gray-500 text-sm mt-1">Try adjusting your search or category filters.</p>
                <a href="{{ route('student.resources.index') }}" class="inline-block mt-4 text-orange-600 hover:underline text-sm">Clear filters</a>
            </div>
        @endif
    </div>
</x-app-layout>
