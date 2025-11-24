import './bootstrap';
import './touch-gestures';
import initializeKeyboardShortcuts from './keyboard';
import initializeNotifications from './notifications';

// Initialize features when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    initializeKeyboardShortcuts();
    initializeNotifications();
});
