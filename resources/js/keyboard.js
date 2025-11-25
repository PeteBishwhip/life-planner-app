// Keyboard shortcuts for Life Planner
export function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', (event) => {
        // Ignore if user is typing in an input, textarea, or select
        if (
            event.target.tagName === 'INPUT' ||
            event.target.tagName === 'TEXTAREA' ||
            event.target.tagName === 'SELECT' ||
            event.target.isContentEditable
        ) {
            return;
        }

        const key = event.key.toLowerCase();

        // Prevent default for handled shortcuts
        const handledKeys = ['t', 'n', 'p', 'j', 'k', 'c', 'q', 's', '/', 'm', 'w', 'd', 'l', '?'];
        if (handledKeys.includes(key)) {
            event.preventDefault();
        }

        // Only execute if on calendar page
        const isOnCalendar = window.location.pathname === '/calendar';

        // Navigation shortcuts
        if (key === 't' && isOnCalendar) {
            // Jump to Today
            Livewire.dispatch('keyboard-shortcut', { action: 'today' });
        } else if ((key === 'n' || key === 'j') && isOnCalendar) {
            // Next period
            Livewire.dispatch('keyboard-shortcut', { action: 'next' });
        } else if ((key === 'p' || key === 'k') && isOnCalendar) {
            // Previous period
            Livewire.dispatch('keyboard-shortcut', { action: 'previous' });
        }

        // View switching shortcuts (only on calendar page)
        if (isOnCalendar) {
            if (key === 'm') {
                Livewire.dispatch('keyboard-shortcut', { action: 'view-month' });
            } else if (key === 'w') {
                Livewire.dispatch('keyboard-shortcut', { action: 'view-week' });
            } else if (key === 'd') {
                Livewire.dispatch('keyboard-shortcut', { action: 'view-day' });
            } else if (key === 'l') {
                Livewire.dispatch('keyboard-shortcut', { action: 'view-list' });
            }
        }

        // Global shortcuts
        if (key === 'c') {
            // Create appointment
            window.location.href = '/appointments/create';
        } else if (key === 'q' && isOnCalendar) {
            // Quick add (will be implemented)
            Livewire.dispatch('keyboard-shortcut', { action: 'quick-add' });
        } else if (key === 's' || key === '/') {
            // Focus search
            window.location.href = '/search';
        } else if (key === '?') {
            // Show help
            Livewire.dispatch('open-shortcuts');
        }

        // Escape to close modals
        if (key === 'escape') {
            Livewire.dispatch('close-modal');
        }
    });

    console.log('Keyboard shortcuts initialized');
}

// Export for use in app.js
export default initializeKeyboardShortcuts;
