<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="mb-8 flex items-center justify-between">
            <div>
                <a href="{{ route('student.advisors.index') }}" class="text-sm text-gray-500 hover:text-orange-600 mb-2 inline-flex items-center transition-colors">
                    &larr; Back to Advisors
                </a>
                <h1 class="text-2xl font-bold text-gray-800">
                    Book with <span class="text-orange-600">{{ $advisor->name }}</span>
                </h1>
                <p class="text-gray-500 text-sm mt-1">{{ $advisor->department->name ?? 'Faculty Member' }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-800">Available Slots</h2>
                <div class="flex items-center space-x-4 text-xs">
                    <div class="flex items-center"><span class="w-3 h-3 bg-green-100 border border-green-300 rounded-full mr-1"></span> Open</div>
                    <div class="flex items-center"><span class="w-3 h-3 bg-red-50 border border-red-200 rounded-full mr-1"></span> Booked</div>
                </div>
            </div>

            @if($slots->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($slots as $slot)
                        <div data-slot-id="{{ $slot->id }}" 
                             data-slot-date="{{ $slot->start_time->format('M d, Y') }}" 
                             data-slot-time="{{ $slot->start_time->format('h:i A') }}"
                             onclick="openBookingModal(this)"
                             class="group cursor-pointer border border-green-200 bg-green-50 hover:bg-green-600 hover:border-green-700 hover:text-white rounded-lg p-4 transition-all duration-200 text-center relative overflow-hidden">

                            <p class="font-bold text-green-800 group-hover:text-white text-lg">
                                {{ $slot->start_time->format('h:i') }} <span class="text-xs opacity-75">AM/PM</span>
                            </p>
                            <p class="text-xs text-green-600 group-hover:text-green-100 mt-1 font-medium">
                                {{ $slot->start_time->format('M d') }}
                            </p>

                            <div class="mt-2 text-[10px] uppercase tracking-wide font-bold text-green-700 group-hover:text-white bg-green-200 group-hover:bg-green-500/30 rounded py-0.5">
                                Book Now
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="mt-2 text-gray-500 font-medium">No open slots available.</p>
                </div>
            @endif
        </div>
    </div>

    <div id="bookingModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">

                <div class="bg-slate-900 px-4 py-3 sm:px-6">
                    <h3 class="text-base font-semibold leading-6 text-white" id="modal-title">Confirm Appointment</h3>
                </div>

                <form action="{{ route('student.book.store') }}" method="POST" class="p-6">
                    @csrf
                    <input type="hidden" name="slot_id" id="modalSlotId">

                    <div class="mb-4">
                        <p class="text-sm text-gray-500">You are booking a slot on:</p>
                        <p class="text-lg font-bold text-gray-900 mt-1">
                            <span id="modalDate"></span> at <span id="modalTime" class="text-orange-600"></span>
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Purpose of Meeting</label>
                        <textarea name="purpose" required rows="3"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                  placeholder="e.g., Discussing Project Proposal..."></textarea>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Attachment (Optional)</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-md p-4 text-center bg-gray-50">
                            <p class="text-xs text-gray-500">Upload PDF/Image features coming next...</p>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-orange-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-500 sm:col-start-2">
                            Confirm Booking
                        </button>
                        <button type="button" onclick="closeBookingModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openBookingModal(element) {
            const slotId = element.dataset.slotId;
            const date = element.dataset.slotDate;
            const time = element.dataset.slotTime;
            
            document.getElementById('modalSlotId').value = slotId;
            document.getElementById('modalDate').innerText = date;
            document.getElementById('modalTime').innerText = time;
            document.getElementById('bookingModal').classList.remove('hidden');

            // Prevent double submission
            const form = document.querySelector('#bookingModal form');
            form.onsubmit = function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Processing...';
                }
            };
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
