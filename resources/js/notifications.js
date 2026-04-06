import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key:import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});

const notificationSelectors = {
    bellButton: '#notification-bell-button',
    panel: '#notification-panel',
    list: '#notification-list',
    badge: '#notification-badge',
    readAll: '#notification-read-all',
};

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

async function fetchUnreadNotifications() {
    const response = await fetch('/notifications', {
        headers: { 'Accept': 'application/json' },
    });

    if (!response.ok) {
        throw new Error('Failed to fetch notifications');
    }

    return response.json();
}

function renderNotificationList(items) {
    const list = document.querySelector(notificationSelectors.list);
    if (!list) {
        return;
    }

    if (!items.length) {
        list.innerHTML = '<div class="px-4 py-4 text-sm text-gray-500">No unread notifications</div>';
        return;
    }

    list.innerHTML = items.map((item) => {
        const message = item.data?.message ?? 'New notification';
        const orderNumber = item.data?.order_number ? `Order: ${item.data.order_number}` : '';
        const orderNumberRaw = item.data?.order_number ?? null;
        const orderId = item.data?.order_id ?? null;
        const time = item.created_at ? new Date(item.created_at).toLocaleString() : '';

        return `
            <div class="px-4 py-3 border-b border-gray-50 hover:bg-gray-50">
                <div class="text-sm text-gray-800">${escapeHtml(message)}</div>
                <div class="mt-1 text-xs text-gray-500 flex items-center justify-between">
                    <span>${escapeHtml(orderNumber)}</span>
                    <span>${escapeHtml(time)}</span>
                </div>
                <div class="mt-2 flex items-center gap-3">
                    <button type="button" class="text-xs text-primary hover:underline notification-read-btn" data-id="${item.id}">Mark as read</button>
                    ${(orderNumberRaw || orderId) ? `<button type="button" class="text-xs text-blue-600 hover:underline notification-open-btn" data-id="${item.id}" data-order-number="${escapeHtml(orderNumberRaw ?? '')}" data-order-id="${orderId ?? ''}">View details</button>` : ''}
                </div>
            </div>
        `;
    }).join('');
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

async function markNotificationAsRead(id) {
    const response = await fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
        },
        body: JSON.stringify({}),
    });

    if (!response.ok) {
        throw new Error('Failed to mark notification as read');
    }
}

async function markAllAsRead() {
    const response = await fetch('/notifications/read-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
        },
        body: JSON.stringify({}),
    });

    if (!response.ok) {
        throw new Error('Failed to mark all notifications as read');
    }
}

async function refreshNotificationsUI() {
    await Promise.all([
        updateNotificationBadge(),
        loadNotificationList(),
    ]);
}

async function loadNotificationList() {
    const list = document.querySelector(notificationSelectors.list);
    if (!list) {
        return;
    }

    try {
        list.innerHTML = '<div class="px-4 py-4 text-sm text-gray-500">Loading...</div>';
        const items = await fetchUnreadNotifications();
        renderNotificationList(items);
    } catch (error) {
        list.innerHTML = '<div class="px-4 py-4 text-sm text-red-500">Unable to load notifications</div>';
    }
}

function setupNotificationPanelEvents() {
    const bellButton = document.querySelector(notificationSelectors.bellButton);
    const panel = document.querySelector(notificationSelectors.panel);
    const list = document.querySelector(notificationSelectors.list);
    const readAllButton = document.querySelector(notificationSelectors.readAll);

    if (!bellButton || !panel || !list || !readAllButton) {
        return;
    }

    bellButton.addEventListener('click', async (event) => {
        event.stopPropagation();
        panel.classList.toggle('hidden');
        if (!panel.classList.contains('hidden')) {
            await refreshNotificationsUI();
        }
    });

    document.addEventListener('click', (event) => {
        if (!panel.contains(event.target) && !bellButton.contains(event.target)) {
            panel.classList.add('hidden');
        }
    });

    list.addEventListener('click', async (event) => {
        const button = event.target.closest('.notification-read-btn');
        if (button) {
            const id = button.getAttribute('data-id');
            if (!id) {
                return;
            }

            try {
                await markNotificationAsRead(id);
                await refreshNotificationsUI();
            } catch (error) {
                showToast('Failed to mark notification as read');
            }

            return;
        }

        const openButton = event.target.closest('.notification-open-btn');
        if (!openButton) {
            return;
        }

        const id = openButton.getAttribute('data-id');
        const orderNumber = openButton.getAttribute('data-order-number');
        const orderId = openButton.getAttribute('data-order-id');
        if (!orderNumber && !orderId) {
            showToast('Order details link is not available for this notification');
            return;
        }

        try {
            if (id) {
                await markNotificationAsRead(id);
            }
        } catch (error) {
            // Continue to details page even if mark-as-read fails.
        }

        const destination = orderNumber
            ? `/customer/order-details/${encodeURIComponent(orderNumber)}`
            : `/customer/order-details/${orderId}`;
        window.location.href = destination;
    });

    readAllButton.addEventListener('click', async () => {
        try {
            await markAllAsRead();
            await refreshNotificationsUI();
        } catch (error) {
            showToast('Failed to mark all notifications as read');
        }
    });
}

if (window.userId) {
    window.Echo.private(`App.Models.User.${window.userId}`).notification((notification)=> {
        showToast(notification.message);
        refreshNotificationsUI();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    setupNotificationPanelEvents();
    updateNotificationBadge();
});

function showToast(message){
    // Simple alert - replace with your toast library
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded shadow-lg z-50';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(()=> toast.remove(), 5000);
}
function updateNotificationBadge(){
    return fetch('/notifications/unread-count')
        .then(r => r.json())
        .then(data => {
            const badge = document.querySelector(notificationSelectors.badge);
            if (!badge) {
                return;
            }

            const count = Number(data.count ?? 0);
            badge.textContent = String(count);
            badge.classList.toggle('hidden', count <= 0);
        })
        .catch(() => {
            // Ignore badge update errors in UI so dashboard rendering is not blocked.
        });
}
