<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">My Profile</h1>
            <p class="text-gray-500 text-sm">Manage your account settings and preferences.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <div class="md:col-span-2 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow-sm sm:rounded-xl border border-gray-100">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow-sm sm:rounded-xl border border-gray-100">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="p-6 bg-slate-900 text-white rounded-xl shadow-lg">
                    <div class="flex items-center space-x-4">
                        <div class="h-16 w-16 rounded-full bg-orange-500 flex items-center justify-center text-2xl font-bold">
                            {{ substr($user->name, 0, 1) ?: '?' }}
                        </div>
                        <div>
                            <h2 class="text-lg font-bold">{{ $user->name }}</h2>
                            <p class="text-orange-300 text-sm font-medium">{{ $user->department->name ?? 'General' }}</p>
                        </div>
                    </div>
                    <div class="mt-6 border-t border-gray-700 pt-4">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-300">University ID</span>
                            <span class="font-mono font-bold">{{ $user->university_id ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Role</span>
                            <span class="capitalize">{{ $user->role }}</span>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow-sm sm:rounded-xl border border-red-100">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
