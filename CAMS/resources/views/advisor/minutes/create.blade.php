<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Session Notes (MOM)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Main Form Section (2/3 width) -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">

                            <div class="mb-6 border-b pb-4">
                                <h3 class="text-lg font-bold text-gray-700">Student: {{ $appointment->student->name }}</h3>
                                <p class="text-sm text-gray-500">
                                    Date: {{ $appointment->slot->start_time->format('M d, Y') }} •
                                    Purpose: {{ $appointment->purpose }}
                                </p>
                            </div>

                            <form action="{{ route('advisor.minutes.store', $appointment->id) }}" method="POST">
                                @csrf

                                <div class="mb-4">
                                    <label for="note" class="block text-gray-700 text-sm font-bold mb-2">
                                        Private Note (Visible only to you):
                                    </label>
                                    <textarea
                                        name="note"
                                        id="note"
                                        rows="10"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        placeholder="Record key discussion points, advice given, or follow-up actions..."
                                    >{{ optional($appointment->minute)->note }}</textarea>
                                    @error('note')
                                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex items-center justify-end">
                                    <a href="{{ route('advisor.schedule') }}" class="text-gray-500 hover:text-gray-700 text-sm mr-4">Cancel</a>
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition">
                                        Save Note & Complete Session
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

                <!-- History Section (1/3 width) -->
                <div class="lg:col-span-1">
                    <div class="bg-gray-50 overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">Past Session Notes</h3>
                            
                            @if(isset($history) && $history->isNotEmpty())
                                <div class="space-y-4">
                                    @foreach($history as $pastAppt)
                                        <div class="bg-white p-4 rounded shadow-sm border border-gray-100" x-data="{ expanded: false }">
                                            <div class="flex justify-between items-start mb-2">
                                                <div>
                                                    <p class="text-xs font-semibold text-indigo-600">
                                                        {{ $pastAppt->slot->start_time->format('M d, Y') }}
                                                    </p>
                                                    <p class="text-xs text-gray-500">
                                                        Advisor: {{ $pastAppt->slot->advisor->name ?? 'Unknown' }}
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <div class="text-sm text-gray-700">
                                                <!-- Short version -->
                                                <div x-show="!expanded">
                                                    <p class="line-clamp-3">
                                                        {{ Str::limit($pastAppt->minute->note ?? 'No content', 100) }}
                                                    </p>
                                                </div>
                                                <!-- Full version -->
                                                <div x-show="expanded" style="display: none;">
                                                    <p class="whitespace-pre-wrap">{{ $pastAppt->minute->note ?? '' }}</p>
                                                </div>
                                                
                                                @if(Str::length($pastAppt->minute->note ?? '') > 100)
                                                    <button @click="expanded = !expanded" class="text-xs text-indigo-500 hover:text-indigo-700 mt-2 focus:outline-none font-medium">
                                                        <span x-text="expanded ? 'Show Less' : 'Read More'"></span>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-sm italic">No previous session notes found for this student.</p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
        </div>
    </div>
</x-app-layout>
