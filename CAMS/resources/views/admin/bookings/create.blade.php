<x-app-layout>
    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Create Booking</h1>
            <p class="text-gray-500 text-sm">Book an appointment on behalf of a student.</p>
        </div>

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <form action="{{ route('admin.bookings.store') }}" method="POST">
                @csrf
                
                {{-- 1. Student Selection --}}
                <div class="mb-4">
                    <label for="student_id" class="block text-sm font-medium text-gray-700">1. Select Student</label>
                    <select name="student_id" id="student_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">-- Choose Student --</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->name }} ({{ $student->university_id }})
                            </option>
                        @endforeach
                    </select>
                    @error('student_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- 2. Advisor Selection --}}
                <div class="mb-4">
                    <label for="advisor_id" class="block text-sm font-medium text-gray-700">2. Select Advisor</label>
                    <select name="advisor_id" id="advisor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">-- Choose Advisor --</option>
                        @foreach($advisors as $advisor)
                            <option value="{{ $advisor->id }}">{{ $advisor->name }} ({{ $advisor->department->code ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Slot Selection (Dynamic) --}}
                <div class="mb-4">
                    <label for="slot_id" class="block text-sm font-medium text-gray-700">3. Select Available Slot</label>
                    <select name="slot_id" id="slot_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50" required disabled>
                        <option value="">-- Select Advisor First --</option>
                    </select>
                    <p id="slot_loading" class="text-xs text-blue-500 mt-1 hidden">Loading available slots...</p>
                    <p id="slot_empty" class="text-xs text-orange-500 mt-1 hidden">No available slots for this advisor.</p>
                    @error('slot_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Purpose --}}
                <div class="mb-6">
                    <label for="purpose" class="block text-sm font-medium text-gray-700">Purpose</label>
                    <textarea name="purpose" id="purpose" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('purpose') }}</textarea>
                    @error('purpose') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('admin.dashboard') }}" class="mr-3 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const advisorSelect = document.getElementById('advisor_id');
            const slotSelect = document.getElementById('slot_id');
            const loadingMsg = document.getElementById('slot_loading');
            const emptyMsg = document.getElementById('slot_empty');

            advisorSelect.addEventListener('change', function() {
                const advisorId = this.value;
                
                // Reset Slots
                slotSelect.innerHTML = '<option value="">-- Select Available Slot --</option>';
                slotSelect.disabled = true;
                slotSelect.classList.add('bg-gray-50');
                emptyMsg.classList.add('hidden');

                if (advisorId) {
                    loadingMsg.classList.remove('hidden');

                    fetch(`{{ route('admin.bookings.slots') }}?advisor_id=${advisorId}`)
                        .then(response => response.json())
                        .then(data => {
                            loadingMsg.classList.add('hidden');
                            
                            if (data.length > 0) {
                                slotSelect.disabled = false;
                                slotSelect.classList.remove('bg-gray-50');
                                
                                data.forEach(slot => {
                                    const option = document.createElement('option');
                                    option.value = slot.id;
                                    option.text = `${slot.start_time} - ${slot.end_time}`;
                                    slotSelect.appendChild(option);
                                });
                            } else {
                                emptyMsg.classList.remove('hidden');
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching slots:', error);
                            loadingMsg.classList.add('hidden');
                        });
                }
            });
        });
    </script>
</x-app-layout>
