<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Availability Management</h1>
                <p class="text-sm text-gray-500">Add time slots for students to book appointments.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden sticky top-24">
                    <div class="bg-slate-900 px-6 py-4 border-b border-gray-800">
                        <h2 class="text-white font-semibold">Add New Availability</h2>
                    </div>

                    <div class="p-6">
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

                        <form action="{{ route('advisor.slots.store') }}" method="POST" class="space-y-5">
                            @csrf

                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                <input type="date" id="date" name="date" required min="{{ date('Y-m-d') }}"
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                <input type="date" name="date" required min="{{ date('Y-m-d') }}" value="{{ old('date') }}"
                                       class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500 shadow-sm @error('date') border-red-500 @enderror">
                                <x-input-error :messages="$errors->get('date')" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                                    <input type="time" name="start_time" required value="{{ old('start_time') }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500 shadow-sm @error('start_time') border-red-500 @enderror">
                                    <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                                    <input type="time" name="end_time" required value="{{ old('end_time') }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500 shadow-sm @error('end_time') border-red-500 @enderror">
                                    <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Slot Duration</label>
                                <select name="duration" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500 shadow-sm @error('duration') border-red-500 @enderror">
                                    <option value="20" {{ old('duration') == '20' ? 'selected' : '' }}>20 Minutes</option>
                                    <option value="30" {{ old('duration', '30') == '30' ? 'selected' : '' }}>30 Minutes (Standard)</option>
                                    <option value="45" {{ old('duration') == '45' ? 'selected' : '' }}>45 Minutes</option>
                                    <option value="60" {{ old('duration') == '60' ? 'selected' : '' }}>1 Hour</option>
                                </select>
                                <x-input-error :messages="$errors->get('duration')" class="mt-2" />
                            </div>

                            <button type="submit" class="w-full bg-slate-900 hover:bg-orange-600 text-white font-medium py-2.5 rounded-lg transition-colors shadow-lg shadow-orange-900/20">
                                Generate Slots
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <h2 class="font-semibold text-gray-800">Your Upcoming Slots</h2>
                        <span class="bg-orange-100 text-orange-700 text-xs font-bold px-2 py-1 rounded-full">{{ $slots->count() }} Slots</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($slots as $slot)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $slot->start_time->format('M d, Y') }}</div>
                                        <div class="text-sm text-gray-500">{{ $slot->start_time->format('h:i A') }} - {{ $slot->end_time->format('h:i A') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($slot->status === 'active')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Open
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Booked
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($slot->status === 'active')
                                            <form action="{{ route('advisor.slots.destroy', $slot->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        aria-label="Delete slot for {{ $slot->start_time->format('M d, Y h:i A') }}"
                                                        class="text-red-500 hover:text-red-700 font-medium"
                                                        onclick="return confirm('Delete this slot?')">Delete</button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 cursor-not-allowed">Locked</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-10 text-center text-gray-400">
                                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <p class="mt-2 text-sm">No slots created yet. Use the form to add availability.</p>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Set min date based on user's local timezone to avoid timezone mismatch issues
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('date-input');
            if (dateInput) {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');
                dateInput.min = `${year}-${month}-${day}`;
            }
        });
    </script>
</x-app-layout>
