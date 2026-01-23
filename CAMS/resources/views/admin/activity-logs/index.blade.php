<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Activity Logs</h1>
            <p class="text-gray-500 text-sm">Track system activity and user actions for security and debugging.</p>
        </div>

        {{-- Filters --}}
        <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
            <form action="{{ route('admin.activity-logs') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                
                {{-- Search --}}
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search User or Action..." 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>

                {{-- Action Type --}}
                <div>
                     <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Action Type</label>
                    <select name="action_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">All Actions</option>
                        <option value="login" {{ request('action_type') == 'login' ? 'selected' : '' }}>Login</option>
                        <option value="logout" {{ request('action_type') == 'logout' ? 'selected' : '' }}>Logout</option>
                        <option value="create_appointment" {{ request('action_type') == 'create_appointment' ? 'selected' : '' }}>Booked Slot</option>
                         <option value="cancel_appointment" {{ request('action_type') == 'cancel_appointment' ? 'selected' : '' }}>Cancelled Slot</option>
                        <option value="create_slot" {{ request('action_type') == 'create_slot' ? 'selected' : '' }}>Created Slot</option>
                        <option value="delete_slot" {{ request('action_type') == 'delete_slot' ? 'selected' : '' }}>Deleted Slot</option>
                    </select>
                </div>

                {{-- Role --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">User Role</label>
                    <select name="role" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">All Roles</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="advisor" {{ request('role') == 'advisor' ? 'selected' : '' }}>Advisor</option>
                        <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Student</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 text-sm">Filter</button>
                    @if(request()->anyFilled(['search', 'action_type', 'role']))
                        <a href="{{ route('admin.activity-logs') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">Clear</a>
                    @endif
                </div>

            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-slate-900 text-white flex justify-between items-center">
                <h2 class="font-semibold">Recent Activity</h2>
                <span class="bg-orange-600 text-xs font-bold px-2 py-1 rounded-full text-white">{{ $logs->total() }} Total</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="text-gray-900">{{ $log->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $log->created_at->format('h:i:s A') }}</div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->user)
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold text-xs">
                                            {{ substr($log->user->name, 0, 1) }}
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $log->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $log->user->role }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span
                                        class="text-gray-400 text-sm"
                                        title="This log entry has no associated user because the account was deleted or the action was performed by the system."
                                    >
                                        System / Deleted User
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $color = $log->action_color;
                                @endphp
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($color === 'blue') bg-blue-100 text-blue-800 
                                    @elseif($color === 'green') bg-green-100 text-green-800
                                    @elseif($color === 'red') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $log->action_label }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-600">
                                <p class="max-w-md truncate" title="{{ $log->description }}">
                                    {{ $log->description }}
                                </p>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                {{ $log->ip_address ?? 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                                No activity logs found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
