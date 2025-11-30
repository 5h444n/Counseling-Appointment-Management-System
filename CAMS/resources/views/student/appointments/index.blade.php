<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">My Appointments</h1>
            <p class="text-gray-500 text-sm">Track your counseling requests and view digital tokens.</p>
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
                                @if($app->status === 'approved' || $app->status === 'pending')
                                    <span class="font-mono font-bold text-orange-600 bg-orange-50 px-2 py-1 rounded border border-orange-200">
                                        #{{ $app->token }}
                                    </span>
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
                                @if($app->status === 'approved')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Confirmed</span>
                                @elseif($app->status === 'pending')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                @elseif($app->status === 'declined')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Declined</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($app->status === 'approved')
                                    <button onclick="window.print()" class="text-indigo-600 hover:text-indigo-900 text-xs">Print Slip</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                                You haven't booked any appointments yet.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
