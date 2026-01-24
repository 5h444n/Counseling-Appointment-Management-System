<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">My Appointments</h1>
            <p class="text-gray-500 text-sm">Track your counseling requests and view digital tokens.</p>
        </div>

        {{-- Tabs Navigation --}}
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="{{ route('student.appointments.index', ['tab' => 'upcoming']) }}"
                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $tab === 'upcoming' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <svg class="inline-block w-5 h-5 mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Upcoming
                </a>
                <a href="{{ route('student.appointments.index', ['tab' => 'past']) }}"
                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $tab === 'past' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <svg class="inline-block w-5 h-5 mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Past / History
                </a>
            </nav>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Token</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Advisor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($appointments as $app)
                        <tr class="hover:bg-gray-50 transition-colors">

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(in_array($app->status, ['approved', 'pending', 'completed']))
                                    <span class="font-mono font-bold text-orange-600 bg-orange-50 px-2 py-1 rounded border border-orange-200">
                                        #{{ $app->token }}
                                    </span>
                                @elseif($app->status === 'declined')
                                    <span class="text-gray-400 line-through text-xs">Declined</span>
                                @elseif($app->status === 'cancelled')
                                    <span class="text-gray-400 line-through text-xs">Cancelled</span>
                                @elseif($app->status === 'no_show')
                                    <span class="text-gray-400 line-through text-xs">No Show</span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $app->slot->advisor->name }}</div>
                                <div class="text-xs text-gray-500">{{ $app->slot->advisor->department->code ?? 'Faculty' }}</div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $app->slot->start_time->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $app->slot->start_time->format('h:i A') }}</div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($app->status === 'approved')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Confirmed</span>
                                @elseif($app->status === 'pending')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                @elseif($app->status === 'declined')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Declined</span>
                                @elseif($app->status === 'cancelled')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Cancelled</span>
                                @elseif($app->status === 'completed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Completed</span>
                                @elseif($app->status === 'no_show')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-600">No Show</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($app->status === 'approved')
                                    <button onclick="window.print()" class="text-indigo-600 hover:text-indigo-900 text-xs mr-2">Print Slip</button>
                                @endif

                                @if($app->status === 'completed' && !$app->feedback)
                                    <div x-data="{ showRating: false }" class="inline-block">
                                        <button @click="showRating = true" class="text-orange-600 hover:text-orange-900 text-xs font-bold mr-2">Rate Session</button>

                                        {{-- Rating Modal --}}
                                        <div x-show="showRating" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-rating-{{ $app->id }}" role="dialog" aria-modal="true">
                                            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showRating = false"></div>
                                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-rating-{{ $app->id }}">Rate Your Session</h3>
                                                    <form action="{{ route('feedback.store') }}" method="POST" class="mt-4">
                                                        @csrf
                                                        <input type="hidden" name="appointment_id" value="{{ $app->id }}">
                                                        
                                                        <div class="mb-4">
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                                                            <div class="flex space-x-2">
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    <label class="cursor-pointer">
                                                                        <input type="radio" name="rating" value="{{ $i }}" class="sr-only peer" required>
                                                                        <svg class="w-8 h-8 text-gray-300 peer-checked:text-yellow-400 hover:text-yellow-300 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                                        </svg>
                                                                    </label>
                                                                @endfor
                                                            </div>
                                                            <p class="text-xs text-gray-400 mt-1">Select 1 to 5 stars taking into account your overall satisfaction.</p>
                                                        </div>

                                                        <div class="mb-4">
                                                            <label for="comment" class="block text-sm font-medium text-gray-700">Comment (Optional)</label>
                                                            <textarea name="comment" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                                        </div>

                                                        <div class="mb-4 flex items-center">
                                                            <input type="checkbox" name="is_anonymous" id="anon-{{ $app->id }}" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                            <label for="anon-{{ $app->id }}" class="ml-2 block text-sm text-gray-900">Submit anonymously</label>
                                                        </div>

                                                        <div class="flex justify-end">
                                                            <button type="button" @click="showRating = false" class="mr-3 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancel</button>
                                                            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-md hover:bg-orange-600 transition-colors">Submit Feedback</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($app->status === 'completed' && $app->feedback)
                                    <span class="text-xs text-green-600 font-semibold mr-2">✓ Rated</span>
                                @endif

                                {{-- Cancel Button for Upcoming appointments --}}
                                @if($tab === 'upcoming' && in_array($app->status, ['pending', 'approved']))
                                    <div x-data="{ showConfirm: false }" class="inline-block">
                                        <button @click="showConfirm = true" 
                                                class="text-red-600 hover:text-red-900 text-xs font-medium">
                                            Cancel
                                        </button>

                                        {{-- Confirmation Modal --}}
                                        <div x-show="showConfirm" 
                                             x-cloak
                                             class="fixed inset-0 z-50 overflow-y-auto" 
                                             aria-labelledby="modal-title-{{ $app->id }}" 
                                             role="dialog" 
                                             aria-modal="true">
                                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                                {{-- Background overlay --}}
                                                <div x-show="showConfirm" 
                                                     x-transition:enter="ease-out duration-300"
                                                     x-transition:enter-start="opacity-0"
                                                     x-transition:enter-end="opacity-100"
                                                     x-transition:leave="ease-in duration-200"
                                                     x-transition:leave-start="opacity-100"
                                                     x-transition:leave-end="opacity-0"
                                                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                                                     @click="showConfirm = false"></div>

                                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                                {{-- Modal panel --}}
                                                <div x-show="showConfirm"
                                                     x-transition:enter="ease-out duration-300"
                                                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                     x-transition:leave="ease-in duration-200"
                                                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                     class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                                                    <div class="sm:flex sm:items-start">
                                                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                            </svg>
                                                        </div>
                                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title-{{ $app->id }}">Cancel Appointment</h3>
                                                            <div class="mt-2">
                                                                <p class="text-sm text-gray-500">
                                                                    Are you sure you want to cancel this appointment with <strong>{{ $app->slot->advisor->name }}</strong> 
                                                                    on <strong>{{ $app->slot->start_time->format('M d, Y') }}</strong> at <strong>{{ $app->slot->start_time->format('h:i A') }}</strong>?
                                                                </p>
                                                                <p class="text-sm text-gray-500 mt-2">This action cannot be undone. The slot will become available for other students.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                                        <form action="{{ route('student.appointments.cancel', $app->id) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                                Yes, Cancel
                                                            </button>
                                                        </form>
                                                        <button type="button" 
                                                                @click="showConfirm = false"
                                                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                                            Keep Appointment
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                                @if($tab === 'upcoming')
                                    You don't have any upcoming appointments.
                                @else
                                    No past appointments found.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add x-cloak style to prevent flash of hidden elements --}}
    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-app-layout>
