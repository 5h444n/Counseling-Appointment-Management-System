<div x-data="{
    notifications: [],
    unreadCount: 0,
    open: false,
    init() {
        this.fetchNotifications();
        // Poll every 30 seconds for new notifications
        setInterval(() => this.fetchNotifications(), 30000);
    },
    async fetchNotifications() {
        try {
            const response = await fetch('/notifications');
            const data = await response.json();
            this.notifications = data.notifications;
            this.unreadCount = data.unread_count;
        } catch (error) {
            console.error('Error fetching notifications:', error);
        }
    },
    async markAsRead(id, url) {
        try {
            await fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
            // Update UI locally
            this.unreadCount = Math.max(0, this.unreadCount - 1);
            this.notifications = this.notifications.map(n => 
                n.id === id ? { ...n, read_at: new Date().toISOString() } : n
            );
            
            // Redirect if URL exists
            if (url && url !== '#') {
                window.location.href = url;
            }
        } catch (error) {
            console.error('Error marking as read:', error);
        }
    },
    markAllRead() {
        fetch('/notifications/mark-all', {
            method: 'POST',
             headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => {
            this.unreadCount = 0;
            this.notifications.forEach(n => n.read_at = new Date());
        });
    }
}" class="relative ml-6">

    <!-- Bell Icon -->
    <button @click="open = !open" class="relative p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <span class="sr-only">View notifications</span>
        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        
        <!-- Red Dot for Unread -->
        <span x-show="unreadCount > 0" 
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="opacity-0 scale-0"
              x-transition:enter-end="opacity-100 scale-100"
              x-transition:leave="transition ease-in duration-100"
              x-transition:leave-start="opacity-100 scale-100"
              x-transition:leave-end="opacity-0 scale-0"
              class="absolute top-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-white bg-red-500 transform translate-x-1/4 -translate-y-1/4"></span>
    </button>

    <!-- Dropdown Panel -->
    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50 overflow-hidden" 
         style="display: none;">
         
        <div class="px-4 py-2 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Notifications</h3>
            <button @click="markAllRead" class="text-xs text-indigo-600 hover:text-indigo-800">Mark all read</button>
        </div>

        <div class="max-h-96 overflow-y-auto">
            <template x-for="notification in notifications" :key="notification.id">
                <div @click="markAsRead(notification.id, notification.data.url)" 
                     class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 transition-colors"
                     :class="{ 'bg-blue-50': !notification.read_at }">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 pt-0.5">
                            <!-- Icons based on type -->
                            <template x-if="notification.data.type === 'status_change'">
                                <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </template>
                            <template x-if="notification.data.type === 'new_request'">
                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                </div>
                            </template>
                            <template x-if="notification.data.type === 'system_notice'">
                                <div class="h-8 w-8 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                                </div>
                            </template>
                        </div>
                        <div class="ml-3 w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900" x-text="notification.data.title"></p>
                            <p class="text-xs text-gray-500 mt-1" x-text="notification.data.message"></p>
                            <p class="text-xs text-gray-400 mt-1" x-text="new Date(notification.created_at).toLocaleDateString() + ' ' + new Date(notification.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})"></p>
                        </div>
                    </div>
                </div>
            </template>
            
            <template x-if="notifications.length === 0">
                <div class="px-4 py-6 text-center text-sm text-gray-500">
                    No new notifications.
                </div>
            </template>
        </div>
    </div>
</div>
