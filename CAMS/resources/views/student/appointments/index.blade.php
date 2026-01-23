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

                                {{-- Cancel Button for Upcoming appointments --}}
                                @if($tab === 'upcoming' && in_array($app->status, ['pending', 'approved']) && $app->slot->start_time > now())
                                    <div x-data="{ showConfirm: false }" class="inline-block">
                                        <button @click="showConfirm = true" 
                                                class="text-red-600 hover:text-red-900 text-xs font-medium">
                                            Cancel
                                        </button>

                                        {{-- Confirmation Modal --}}
                                        <div x-show="showConfirm" 
                                             x-cloak
                                             class="fixed inset-0 z-50 overflow-y-auto" 
                                             aria-labelledby="modal-title" 
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
                                                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Cancel Appointment</h3>
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
