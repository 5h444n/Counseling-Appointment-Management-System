<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">My Appointments</h1>
            <p class="text-gray-500 text-sm">Track your counseling requests and view digital tokens.</p>
        </div>

    @php
        $isUpcoming = ($activeTab ?? 'upcoming') === 'upcoming';
        
        $statusBadgeClasses = [
            'pending'   => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'approved'  => 'bg-green-100 text-green-800 border-green-200',
            'declined'  => 'bg-red-100 text-red-800 border-red-200',
            'completed' => 'bg-blue-100 text-blue-800 border-blue-200',
            'no_show'   => 'bg-orange-100 text-orange-800 border-orange-200',
            'cancelled' => 'bg-gray-100 text-gray-800 border-gray-200',
        ];

        $formatStatus = function (?string $status): string {
            if (!$status) return 'Unknown';
            return ucwords(str_replace('_', ' ', $status));
        };
    @endphp

    <div class="flex gap-2 mb-6">
        <a href="{{ url('/student/my-appointments') }}?tab=upcoming"
           class="px-4 py-2 rounded-md border {{ $isUpcoming ? 'bg-gray-900 text-white' : 'bg-white text-gray-700' }}">
            Upcoming
        </a>

        <a href="{{ url('/student/my-appointments') }}?tab=history"
           class="px-4 py-2 rounded-md border {{ !$isUpcoming ? 'bg-gray-900 text-white' : 'bg-white text-gray-700' }}">
            Past/History
        </a>
    </div>

    @if($isUpcoming)
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
                    @forelse($upcomingAppointments as $app)
                        <tr class="hover:bg-gray-50 transition-colors">

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($app->status === 'approved' || $app->status === 'pending')
                                    <span class="font-mono font-bold text-orange-600 bg-orange-50 px-2 py-1 rounded border border-orange-200">
                                        #{{ $app->token }}
                                    </span>
                                @elseif($app->status === 'declined')
                                    <span class="text-gray-400 line-through text-xs">Declined</span>
                                @else
                                    <span class="text-gray-400 line-through text-xs">Cancelled</span>
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
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $statusBadgeClasses[$app->status] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                                    {{ $formatStatus($app->status) }}
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @php
                                    $isCancelable = in_array($app->status, ['pending', 'approved'], true)
                                        && optional($app->slot)->start_time
                                        && \Carbon\Carbon::parse($app->slot)->start_time->isFuture();
                                @endphp

                                @if($isCancelable)
                                    <form method="POST" 
                                          action="{{ route('student.appointments.cancel', $app) }}" 
                                          onsubmit="return confirm('Cancel this appointment?')"
                                          class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1 text-xs font-semibold rounded-md border text-red-600 border-red-200 hover:bg-red-50">
                                            Cancel
                                        </button>
                                    </form>
                                @endif
                                
                                @if($app->status === 'approved')
                                    <button onclick="window.print()" class="text-indigo-600 hover:text-indigo-900 text-xs ml-2">Print Slip</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                                No upcoming appointments.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Token</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Advisor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($historyAppointments as $app)
                        <tr class="hover:bg-gray-50 transition-colors">

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($app->status === 'approved' || $app->status === 'pending')
                                    <span class="font-mono font-bold text-orange-600 bg-orange-50 px-2 py-1 rounded border border-orange-200">
                                        #{{ $app->token }}
                                    </span>
                                @elseif($app->status === 'declined')
                                    <span class="text-gray-400 line-through text-xs">Declined</span>
                                @else
                                    <span class="text-gray-400 line-through text-xs">Cancelled</span>
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
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $statusBadgeClasses[$app->status] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                                    {{ $formatStatus($app->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-400">
                                No past appointments.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    </div>
</x-app-layout>
