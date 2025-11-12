// Home View - Popup Initialization and Refresh Logic

// Initialize popup system with newsletter data
if (parent.window && parent.window.popupSystem) {
    // Get newsletters data from PHP (this will be filled by PHP)
    const newsletters = window.newslettersData || {};
    
    parent.window.popupSystem.initData(newsletters, {}, {});
}

// Set initial page load time
const pageLoadTime = new Date();

// Update the "last updated" timestamp
function updateTimestamp() {
    const now = new Date();
    const diffSeconds = Math.floor((now - pageLoadTime) / 1000);
    const updateTimeSpan = document.getElementById('updateTime');
    
    if (updateTimeSpan) {
        if (diffSeconds < 60) {
            updateTimeSpan.textContent = 'just now';
        } else if (diffSeconds < 3600) {
            const minutes = Math.floor(diffSeconds / 60);
            updateTimeSpan.textContent = `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
        } else {
            const hours = Math.floor(diffSeconds / 3600);
            updateTimeSpan.textContent = `${hours} hour${hours > 1 ? 's' : ''} ago`;
        }
    }
}

// Update timestamp every 30 seconds
setInterval(updateTimestamp, 30000);

// Refresh button with animation
const refreshBtn = document.getElementById('refreshHomeBtn');
if (refreshBtn) {
    refreshBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const icon = this.querySelector('i');
        
        // Add spinning animation
        icon.classList.add('fa-spin');
        this.disabled = true;
        
        // Reload after short delay to show animation
        setTimeout(() => {
            location.reload();
        }, 300);
    });
}
