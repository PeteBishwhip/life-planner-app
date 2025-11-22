/**
 * Keyboard Shortcuts Manager
 * Handles global keyboard shortcuts for the Life Planner application
 */

export default function keyboardShortcuts() {
    return {
        showHelpModal: false,
        shortcuts: {
            // Navigation
            't': { action: 'goToToday', description: 'Go to Today' },
            'n': { action: 'nextPeriod', description: 'Next period' },
            'p': { action: 'previousPeriod', description: 'Previous period' },
            'd': { action: 'dayView', description: 'Day view' },
            'w': { action: 'weekView', description: 'Week view' },
            'm': { action: 'monthView', description: 'Month view' },

            // Actions
            'c': { action: 'createAppointment', description: 'Create appointment' },
            'q': { action: 'quickAdd', description: 'Quick add' },
            's': { action: 'search', description: 'Search' },
            'r': { action: 'refresh', description: 'Refresh' },

            // Special
            '?': { action: 'showHelp', description: 'Show shortcuts help' },
            'Escape': { action: 'closeModal', description: 'Close modal' }
        },

        init() {
            // Listen for keydown events
            document.addEventListener('keydown', (e) => this.handleKeyPress(e));
        },

        handleKeyPress(event) {
            // Don't trigger shortcuts when user is typing in an input
            const activeElement = document.activeElement;
            const isInputField = ['INPUT', 'TEXTAREA', 'SELECT'].includes(activeElement.tagName);
            const isContentEditable = activeElement.isContentEditable;

            if (isInputField || isContentEditable) {
                // Exception: Allow Escape to work in input fields
                if (event.key !== 'Escape') {
                    return;
                }
            }

            const key = event.key.toLowerCase();

            // Handle Shift + ? to show help
            if (event.shiftKey && key === '/') {
                event.preventDefault();
                this.executeAction('showHelp');
                return;
            }

            // Check if the key is a registered shortcut
            if (this.shortcuts[key]) {
                event.preventDefault();
                this.executeAction(this.shortcuts[key].action);
            } else if (this.shortcuts[event.key]) {
                // For keys like Escape that aren't lowercase
                event.preventDefault();
                this.executeAction(this.shortcuts[event.key].action);
            }
        },

        executeAction(action) {
            console.log('Executing action:', action);

            switch (action) {
                case 'goToToday':
                    this.dispatchEvent('calendar:go-to-today');
                    break;

                case 'nextPeriod':
                    this.dispatchEvent('calendar:next-period');
                    break;

                case 'previousPeriod':
                    this.dispatchEvent('calendar:previous-period');
                    break;

                case 'dayView':
                    this.dispatchEvent('calendar:change-view', { view: 'day' });
                    break;

                case 'weekView':
                    this.dispatchEvent('calendar:change-view', { view: 'week' });
                    break;

                case 'monthView':
                    this.dispatchEvent('calendar:change-view', { view: 'month' });
                    break;

                case 'createAppointment':
                    this.dispatchEvent('appointment:create');
                    break;

                case 'quickAdd':
                    this.dispatchEvent('appointment:quick-add');
                    this.focusQuickAdd();
                    break;

                case 'search':
                    this.dispatchEvent('appointment:search');
                    this.focusSearch();
                    break;

                case 'refresh':
                    this.dispatchEvent('calendar:refresh');
                    window.location.reload();
                    break;

                case 'showHelp':
                    this.showHelpModal = true;
                    break;

                case 'closeModal':
                    this.showHelpModal = false;
                    this.dispatchEvent('modal:close');
                    break;

                default:
                    console.warn('Unknown action:', action);
            }
        },

        dispatchEvent(eventName, detail = {}) {
            window.dispatchEvent(new CustomEvent(eventName, { detail }));
        },

        focusQuickAdd() {
            const quickAddInput = document.querySelector('[data-quick-add-input]');
            if (quickAddInput) {
                quickAddInput.focus();
            }
        },

        focusSearch() {
            const searchInput = document.querySelector('[data-search-input]');
            if (searchInput) {
                searchInput.focus();
            }
        },

        getShortcutsByCategory() {
            return {
                'Navigation': [
                    { key: 'T', description: 'Go to Today' },
                    { key: 'N', description: 'Next period (day/week/month)' },
                    { key: 'P', description: 'Previous period (day/week/month)' },
                    { key: 'D', description: 'Switch to Day view' },
                    { key: 'W', description: 'Switch to Week view' },
                    { key: 'M', description: 'Switch to Month view' }
                ],
                'Actions': [
                    { key: 'C', description: 'Create new appointment' },
                    { key: 'Q', description: 'Quick add (natural language)' },
                    { key: 'S', description: 'Search appointments' },
                    { key: 'R', description: 'Refresh calendar' }
                ],
                'Help': [
                    { key: '?', description: 'Show this help dialog' },
                    { key: 'Esc', description: 'Close modal/dialog' }
                ]
            };
        },

        closeHelpModal() {
            this.showHelpModal = false;
        }
    };
}

// Make it available globally
if (typeof window !== 'undefined') {
    window.keyboardShortcuts = keyboardShortcuts;
}
