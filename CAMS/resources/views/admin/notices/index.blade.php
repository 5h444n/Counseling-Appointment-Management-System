<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">System Notices</h1>
            <a href="{{ route('admin.notices.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Send New Notice</a>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <ul class="divide-y divide-gray-200">
                @foreach($notices as $notice)
                <li class="p-6 hover:bg-gray-50">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-lg font-semibold text-gray-800">{{ $notice->title }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            {{ $notice->user_role === 'all' ? 'bg-purple-100 text-purple-800' : 
                               ($notice->user_role === 'student' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800') }}">
                            To: {{ ucfirst($notice->user_role) }}s
                        </span>
                    </div>
                    <p class="text-gray-600 text-sm mb-2">{{ Str::limit($notice->content, 150) }}</p>
                    <div class="text-xs text-gray-400">
                        Sent on {{ $notice->created_at->format('M d, Y h:i A') }}
                    </div>
                </li>
                @endforeach
                @if($notices->isEmpty())
                <li class="p-6 text-center text-gray-500">
                    No notices sent yet.
                </li>
                @endif
            </ul>
        </div>
        <div class="mt-4 px-4">
             {{ $notices->links() }}
        </div>
    </div>
</x-app-layout>
