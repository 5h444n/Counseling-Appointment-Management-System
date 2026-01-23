<x-app-layout>
    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Send a Notice</h1>
            <p class="text-gray-500 text-sm">Send a message to Students, Advisors, or Everyone.</p>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <form action="{{ route('admin.notices.store') }}" method="POST">
                @csrf
                
                {{-- Title --}}
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700">Subject / Title</label>
                    <input type="text" name="title" id="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('title') }}" required>
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Target Audience --}}
                <div class="mb-4">
                    <label for="user_role" class="block text-sm font-medium text-gray-700">Send To</label>
                    <select name="user_role" id="user_role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="all">Everyone (Students & Advisors)</option>
                        <option value="student">Students Only</option>
                        <option value="advisor">Advisors Only</option>
                        <option value="specific">Specific User</option>
                    </select>
                    @error('user_role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Specific User Logic --}}
                <div class="mb-4 hidden" id="specific_user_div">
                    <label for="user_id" class="block text-sm font-medium text-gray-700">Select Recipient</label>
                    <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Choose User --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }} ({{ ucfirst($user->role) }}) - {{ $user->university_id ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                     @error('user_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <script>
                    document.getElementById('user_role').addEventListener('change', function() {
                        const specificDiv = document.getElementById('specific_user_div');
                        if (this.value === 'specific') {
                            specificDiv.classList.remove('hidden');
                        } else {
                            specificDiv.classList.add('hidden');
                        }
                    });
                </script>

                {{-- Content --}}
                <div class="mb-6">
                    <label for="content" class="block text-sm font-medium text-gray-700">Message Content</label>
                    <textarea name="content" id="content" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('content') }}</textarea>
                    @error('content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('admin.notices.index') }}" class="mr-3 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Send Notice</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
