<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Schedule & History') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-orange-500">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Upcoming Sessions
                    </h3>

                    @if($upcoming->isEmpty())
                        <p class="text-gray-500 italic">No upcoming appointments scheduled.</p>
                    @else
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            @foreach($upcoming as $appt)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition bg-gray-50">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded">
                                            {{ $appt->slot->start_time->format('M d') }} • {{ $appt->slot->start_time->format('h:i A') }}
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-lg text-gray-800">{{ $appt->student->name }}</h4>
                                    <p class="text-sm text-gray-500 mb-2">ID: {{ $appt->student->id }}</p> <div class="bg-white p-2 rounded border border-gray-100 text-sm text-gray-600 mb-3">
                                        <span class="font-semibold">Purpose:</span> {{ Str::limit($appt->purpose, 50) }}
                                    </div>

                                    <button class="w-full bg-orange-600 text-white text-sm font-bold py-2 rounded hover:bg-orange-700 transition">
                                        Start Session
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        History
                    </h3>

                    @if($history->isEmpty())
                        <p class="text-gray-500 italic">No past appointments found.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purpose</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($history as $appt)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ $appt->slot->start_time->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $appt->student->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $appt->student->email }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ Str::limit($appt->purpose, 40) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('advisor.minutes.create', $appt->id) }}"
   class="text-indigo-600 hover:text-indigo-900 font-bold border border-indigo-200 px-3 py-1 rounded hover:bg-indigo-50">
    @if($appt->minute)
        Edit Note
    @else
        Write Note (MOM)
    @endif
</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
