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

window.Echo.private(`App.Models.User.${window.userId}`).notification((notification)=> {
    // This fires INSTANTLY when the job broadcasts the event
    // 1. Show a toast
    showToast(notification.message);

    //2. Update the bell icon unread count

    updateNotificationBadge();

})

function showToast(message){
    // Simple alert - replace with your toast library
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded shadow-lg z-50';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(()=> toast.remove(), 5000);
}
function updateNotificationBadge(){
    fetch('/notifications/unread-count')
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notification-badge');
            if (badge) badge.textContent = data.count;
        });
}
