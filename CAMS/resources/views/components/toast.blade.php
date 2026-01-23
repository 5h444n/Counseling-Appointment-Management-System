{{-- Global Toast Notification Component --}}
{{-- This component displays session flash messages as toast notifications --}}

@php
    $hasSuccess = session('success');
    $hasError = session('error');
    $hasMessage = $hasSuccess || $hasError;
@endphp

@if($hasMessage)
<div x-data="{ 
        show: true, 
        type: '{{ $hasSuccess ? 'success' : 'error' }}',
        message: '{{ $hasSuccess ?? $hasError }}'
     }"
     x-init="setTimeout(() => show = false, 5000)"
     x-show="show"
     x-transition:enter="transform ease-out duration-300 transition"
     x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
     x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed top-4 right-4 z-50 max-w-sm w-full pointer-events-auto"
     x-cloak>
    
    <div :class="{
            'bg-green-50 border-green-500': type === 'success',
            'bg-red-50 border-red-500': type === 'error'
         }"
         class="border-l-4 rounded-lg shadow-lg p-4">
        <div class="flex items-start">
            {{-- Icon --}}
            <div class="flex-shrink-0">
                <template x-if="type === 'success'">
                    <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
                <template x-if="type === 'error'">
                    <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </div>
            
            {{-- Message --}}
            <div class="ml-3 flex-1">
                <p :class="{
                        'text-green-800': type === 'success',
                        'text-red-800': type === 'error'
                   }"
                   class="text-sm font-medium"
                   x-text="message"></p>
            </div>
            
            {{-- Close Button --}}
            <div class="ml-4 flex-shrink-0 flex">
                <button @click="show = false"
                        :class="{
                            'text-green-400 hover:text-green-600': type === 'success',
                            'text-red-400 hover:text-red-600': type === 'error'
                        }"
                        class="inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2">
                    <span class="sr-only">Close</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
        
        {{-- Progress bar for auto-dismiss --}}
        <div class="mt-2 w-full bg-gray-200 rounded-full h-1 overflow-hidden">
            <div :class="{
                    'bg-green-500': type === 'success',
                    'bg-red-500': type === 'error'
                 }"
                 class="h-1 rounded-full animate-shrink origin-left"
                 style="animation: shrink 5s linear forwards;"></div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    @keyframes shrink {
        from { width: 100%; }
        to { width: 0%; }
    }
    .animate-shrink {
        animation: shrink 5s linear forwards;
    }
</style>
@endif
