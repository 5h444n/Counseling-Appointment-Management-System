<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Session Notes (MOM)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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
    </div>
</x-app-layout>
