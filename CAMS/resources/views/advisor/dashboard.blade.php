<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Appointment Requests</h1>
            <p class="text-gray-500 text-sm">Manage incoming counseling requests from students.</p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 text-green-700 p-3 rounded-lg mb-4 text-sm border-l-4 border-green-500">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 text-red-700 p-3 rounded-lg mb-4 text-sm border-l-4 border-red-500">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-slate-900 text-white flex justify-between items-center">
                <h2 class="font-semibold">Pending Approvals</h2>
                <span class="bg-orange-600 text-xs font-bold px-2 py-1 rounded-full text-white">{{ $appointments->count() }} Pending</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slot Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purpose</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Attachment</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($appointments as $app)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-bold">
                                        {{ substr($app->student->name, 0, 1) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $app->student->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $app->student->university_id }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-semibold">{{ $app->slot->start_time->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $app->slot->start_time->format('h:i A') }} - {{ $app->slot->end_time->format('h:i A') }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-600 truncate w-48" title="{{ $app->purpose }}">
                                    {{ $app->purpose }}
                                </p>
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if($app->documents->count() > 0)
                                    <a href="{{ route('advisor.documents.download', $app->documents->first()->id) }}" class="inline-flex items-center text-orange-600 hover:text-orange-900 border border-orange-200 bg-orange-50 px-2 py-1 rounded-md transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        View File
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">No file</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <form action="{{ route('advisor.appointments.update', $app->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" aria-label="Accept appointment from {{ $app->student->name }}" class="text-white bg-green-600 hover:bg-green-700 px-3 py-1 rounded-md text-xs transition-colors shadow-sm">
                                            Accept
                                        </button>
                                    </form>

                                    <form action="{{ route('advisor.appointments.update', $app->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="declined">
                                        <button type="submit" class="text-white bg-red-600 hover:bg-red-700 px-3 py-1 rounded-md text-xs transition-colors shadow-sm" onclick="return confirm('Are you sure you want to decline this request?')">
                                            Decline
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                                No pending requests at this time.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>


        {{-- Calendar Integration --}}
        <div class="mt-8 mb-12">
            <x-calendar />
        </div>

        {{-- Recent Feedback --}}
        <div class="mt-8">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Recent Feedback</h2>
            @if(isset($recentFeedback) && $recentFeedback->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($recentFeedback as $fb)
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex text-yellow-400">
                                    @for($i=1; $i<=5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $fb->rating ? 'fill-current' : 'text-gray-300' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </div>
                                <span class="text-xs text-gray-400">{{ $fb->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-gray-600 text-sm mb-2">"{{ Str::limit($fb->comment, 80) }}"</p>
                            <p class="text-xs text-gray-500 font-medium">
                                - {{ $fb->is_anonymous ? 'Anonymous Student' : $fb->student->name }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm italic">No feedback received yet.</p>
            @endif
        </div>
    </div>
</x-app-layout>
