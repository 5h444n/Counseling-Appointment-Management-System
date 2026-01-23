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

        {{-- Flash messages handled by global toast component --}}

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-800">Available Slots</h2>
                <div class="flex items-center space-x-4 text-xs">
                    <div class="flex items-center"><span class="w-3 h-3 bg-green-100 border border-green-300 rounded-full mr-1"></span> Open</div>
                    <div class="flex items-center"><span class="w-3 h-3 bg-red-50 border border-red-200 rounded-full mr-1"></span> Booked</div>
                    <div class="flex items-center"><span class="w-3 h-3 bg-blue-100 border border-blue-200 rounded-full mr-1"></span> Waitlisted</div>
                </div>
            </div>

            @if($slots->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($slots as $slot)
                        @if($slot->status === 'active')
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

                        @elseif($slot->status === 'blocked')
                            <div class="group border border-red-200 bg-red-50 rounded-lg p-4 text-center flex flex-col justify-between">
                                <div>
                                    <p class="font-bold text-red-800 text-lg opacity-75">
                                        {{ $slot->start_time->format('h:i') }} <span class="text-xs">AM/PM</span>
                                    </p>
                                    <p class="text-xs text-red-600 mt-1 font-medium">
                                        {{ $slot->start_time->format('M d') }}
                                    </p>
                                </div>
                                <div class="mt-3 flex flex-col items-center gap-2">
                                    <span class="text-[10px] font-bold text-red-500 uppercase tracking-wide">Booked</span>

                                    @if(in_array($slot->id, $waitlistedSlotIds))
                                        <div class="text-[10px] bg-blue-100 text-blue-700 border border-blue-200 px-3 py-1 rounded-full font-semibold uppercase tracking-wide cursor-default w-full">
                                            ✓ On Waitlist
                                        </div>
                                    @else
                                        <form action="{{ route('waitlist.join', $slot->id) }}" method="POST" class="w-full">
                                            @csrf
                                            <button type="submit" class="w-full text-[10px] bg-orange-100 text-orange-700 border border-orange-200 hover:bg-orange-200 hover:text-orange-800 px-3 py-1 rounded-full transition font-semibold uppercase tracking-wide shadow-sm">
                                                Join Waitlist
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="text-center py-16 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <p class="text-gray-500 font-medium">No slots found.</p>
                </div>
            @endif
        </div>
    </div>

    <div id="bookingModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-slate-900 px-4 py-3 sm:px-6">
                    <h3 class="text-base font-semibold leading-6 text-white">Confirm Appointment</h3>
                </div>
                <form action="{{ route('student.book.store') }}" 
                      method="POST" 
                      enctype="multipart/form-data" 
                      class="p-6"
                      x-data="{ 
                          submitting: false, 
                          fileName: '',
                          fileSize: 0,
                          fileError: '',
                          maxSize: 100 * 1024 * 1024,
                          handleFileSelect(e) {
                              const file = e.target.files[0];
                              if (file) {
                                  this.fileName = file.name;
                                  this.fileSize = file.size;
                                  if (file.size > this.maxSize) {
                                      this.fileError = 'File too large! Maximum size is 100MB.';
                                  } else {
                                      this.fileError = '';
                                  }
                              } else {
                                  this.fileName = '';
                                  this.fileSize = 0;
                                  this.fileError = '';
                              }
                          },
                          formatFileSize(bytes) {
                              if (bytes === 0) return '0 Bytes';
                              const k = 1024;
                              const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                              const i = Math.floor(Math.log(bytes) / Math.log(k));
                              return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                          }
                      }"
                      @submit="if(!fileError && fileSize <= maxSize) { submitting = true; } else { $event.preventDefault(); }">
                    @csrf
                    <input type="hidden" name="slot_id" id="modalSlotId">

                    <div class="mb-4">
                        <p class="text-sm text-gray-500">Booking Time:</p>
                        <p class="text-lg font-bold text-gray-900 mt-1">
                            <span id="modalDate"></span> at <span id="modalTime" class="text-orange-600"></span>
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Purpose <span class="text-red-500">*</span></label>
                        <textarea name="purpose" 
                                  required 
                                  rows="3" 
                                  minlength="10"
                                  placeholder="Please describe the purpose of your appointment (min 10 characters)..."
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                  :disabled="submitting"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Attachment (Optional)</label>
                        <input type="file" 
                               name="document" 
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.jpg,.jpeg,.png,.gif,.bmp,.svg" 
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100"
                               @change="handleFileSelect($event)"
                               :disabled="submitting">
                        
                        {{-- File info display --}}
                        <div x-show="fileName" class="mt-2 flex items-center text-sm">
                            <svg class="h-4 w-4 text-gray-400 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <span x-text="fileName" class="text-gray-600 truncate max-w-xs"></span>
                            <span class="text-gray-400 ml-2" x-text="'(' + formatFileSize(fileSize) + ')'"></span>
                        </div>
                        
                        {{-- Error message --}}
                        <p x-show="fileError" x-text="fileError" class="mt-1 text-sm text-red-600"></p>
                        
                        <p class="text-xs text-gray-500 mt-1">Supported: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT, JPG, PNG, GIF, BMP, SVG (Max: 100MB)</p>
                    </div>

                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                        <button type="submit" 
                                :disabled="submitting || fileError"
                                :class="{ 'opacity-50 cursor-not-allowed': submitting || fileError }"
                                class="inline-flex w-full justify-center items-center rounded-md bg-orange-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-500 sm:col-start-2 disabled:hover:bg-orange-600">
                            {{-- Loading spinner --}}
                            <svg x-show="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-show="!submitting">Confirm Booking</span>
                            <span x-show="submitting">Processing...</span>
                        </button>
                        <button type="button" 
                                onclick="closeBookingModal()" 
                                :disabled="submitting"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0 disabled:opacity-50">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openBookingModal(element) {
            document.getElementById('modalSlotId').value = element.getAttribute('data-slot-id');
            document.getElementById('modalDate').innerText = element.getAttribute('data-slot-date');
            document.getElementById('modalTime').innerText = element.getAttribute('data-slot-time');
            document.getElementById('bookingModal').classList.remove('hidden');
        }
        function closeBookingModal() {
            document.getElementById('bookingModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
