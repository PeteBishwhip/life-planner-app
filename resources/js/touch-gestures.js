/**
 * Touch Gestures for Mobile Calendar Navigation
 *
 * Provides swipe left/right for navigation and pull-to-refresh functionality
 */

// Swipe Detection
export function initSwipeGestures() {
    let touchStartX = 0;
    let touchStartY = 0;
    let touchEndX = 0;
    let touchEndY = 0;

    const minSwipeDistance = 50;
    const maxVerticalDistance = 100;

    document.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        touchStartY = e.changedTouches[0].screenY;
    }, { passive: true });

    document.addEventListener('touchend', (e) => {
        // Don't handle swipes on input elements
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
            return;
        }

        touchEndX = e.changedTouches[0].screenX;
        touchEndY = e.changedTouches[0].screenY;

        handleSwipe();
    }, { passive: true });

    function handleSwipe() {
        const horizontalDistance = touchEndX - touchStartX;
        const verticalDistance = Math.abs(touchEndY - touchStartY);

        // Only trigger if horizontal swipe is significant and vertical movement is minimal
        if (Math.abs(horizontalDistance) > minSwipeDistance && verticalDistance < maxVerticalDistance) {
            if (horizontalDistance > 0) {
                // Swipe right - go to previous
                const prevButton = document.querySelector('[wire\\:click="previous"]');
                if (prevButton) {
                    prevButton.click();
                }
            } else {
                // Swipe left - go to next
                const nextButton = document.querySelector('[wire\\:click="next"]');
                if (nextButton) {
                    nextButton.click();
                }
            }
        }
    }
}

// Pull to Refresh
export function initPullToRefresh() {
    let touchStartY = 0;
    let touchMoveY = 0;
    let isPulling = false;

    const pullThreshold = 80;
    const mainContent = document.querySelector('main');

    if (!mainContent) return;

    document.addEventListener('touchstart', (e) => {
        if (window.scrollY === 0) {
            touchStartY = e.touches[0].clientY;
            isPulling = true;
        }
    }, { passive: true });

    document.addEventListener('touchmove', (e) => {
        if (!isPulling) return;

        touchMoveY = e.touches[0].clientY;
        const pullDistance = touchMoveY - touchStartY;

        if (pullDistance > 0 && window.scrollY === 0) {
            // Visual feedback - subtle transform
            const scale = Math.min(pullDistance / pullThreshold, 1);
            mainContent.style.transform = `translateY(${scale * 20}px)`;
            mainContent.style.transition = 'none';
        }
    }, { passive: true });

    document.addEventListener('touchend', () => {
        if (!isPulling) return;

        const pullDistance = touchMoveY - touchStartY;

        if (pullDistance > pullThreshold) {
            // Refresh the page
            window.location.reload();
        } else {
            // Reset transform
            mainContent.style.transform = '';
            mainContent.style.transition = 'transform 0.3s ease';
        }

        isPulling = false;
        touchStartY = 0;
        touchMoveY = 0;
    }, { passive: true });
}

// Long Press Detection (for future features)
export function initLongPress(element, callback) {
    let timer;
    const longPressDuration = 500; // ms

    element.addEventListener('touchstart', (e) => {
        timer = setTimeout(() => {
            callback(e);
        }, longPressDuration);
    });

    element.addEventListener('touchend', () => {
        clearTimeout(timer);
    });

    element.addEventListener('touchmove', () => {
        clearTimeout(timer);
    });
}

// Initialize all touch gestures when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initSwipeGestures();
        initPullToRefresh();
    });
} else {
    initSwipeGestures();
    initPullToRefresh();
}

// Re-initialize after Livewire navigation
document.addEventListener('livewire:navigated', () => {
    initSwipeGestures();
    initPullToRefresh();
});
