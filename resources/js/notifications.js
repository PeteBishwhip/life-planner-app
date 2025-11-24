// Browser Notification Permissions and Management
export function initializeNotifications() {
    // Check if browser supports notifications
    if (!('Notification' in window)) {
        console.log('Browser does not support notifications');
        return;
    }

    // Expose global function for requesting permission
    window.requestNotificationPermission = async function() {
        try {
            const permission = await Notification.requestPermission();

            if (permission === 'granted') {
                // Show test notification
                new Notification('Life Planner', {
                    body: 'Browser notifications are now enabled!',
                    icon: '/favicon.ico',
                    badge: '/favicon.ico'
                });

                return true;
            } else if (permission === 'denied') {
                alert('Notification permission denied. You can enable it in your browser settings.');
                return false;
            }

            return false;
        } catch (error) {
            console.error('Error requesting notification permission:', error);
            return false;
        }
    };

    // Expose function to check current permission status
    window.getNotificationPermission = function() {
        return Notification.permission;
    };

    // Expose function to show a notification
    window.showNotification = function(title, options = {}) {
        if (Notification.permission === 'granted') {
            return new Notification(title, {
                icon: '/favicon.ico',
                badge: '/favicon.ico',
                ...options
            });
        } else {
            console.log('Notification permission not granted');
            return null;
        }
    };

    console.log('Notification support initialized');
}

export default initializeNotifications;
