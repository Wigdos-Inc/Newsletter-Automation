// Inbox Badge and Dropdown Auto-Update System
// This file is only loaded when user is logged in

function updateInboxBadge() {
  fetch('scripts/php/inbox_api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({
      action: 'get_unread_count'
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success && typeof data.count !== 'undefined') {
      const badge = document.querySelector('.inbox-badge');
      const dropdownBadge = document.querySelector('.inbox-header .badge');
      const inboxIcon = document.querySelector('.inbox-icon');
      
      if (data.count > 0) {
        // Show badge with count
        if (badge) {
          badge.textContent = data.count;
          badge.style.display = 'inline-block';
        } else if (inboxIcon) {
          // Create badge if it doesn't exist
          const newBadge = document.createElement('span');
          newBadge.className = 'inbox-badge';
          newBadge.textContent = data.count;
          inboxIcon.appendChild(newBadge);
        }
        
        if (dropdownBadge) {
          dropdownBadge.textContent = data.count + ' unread';
        }
      } else {
        // Hide badge if no unread messages
        if (badge) {
          badge.style.display = 'none';
        }
        
        if (dropdownBadge) {
          dropdownBadge.textContent = '0 unread';
        }
      }
      
      // Also refresh inbox dropdown
      updateInboxDropdown();
    }
  })
  .catch(error => {
    console.error('Error updating inbox badge:', error);
  });
}

function updateInboxDropdown() {
  fetch('scripts/php/inbox_api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({
      action: 'get_recent_messages',
      limit: 5
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success && data.messages) {
      const inboxItems = document.querySelector('.inbox-items');
      
      if (inboxItems) {
        if (data.messages.length > 0) {
          // Build inbox items HTML
          let html = '';
          data.messages.forEach(item => {
            const date = new Date(item.date);
            const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const snippet = item.snippet ? item.snippet.substring(0, 50) + '...' : '';
            
            html += `
              <a href="#" class="inbox-item" data-message-id="${item.message_ID}">
                <div class="inbox-item-content">
                  <strong>${item.subject || 'Notification'}</strong>
                  <p class="mb-0">${snippet}</p>
                  <small class="text-muted">${formattedDate}</small>
                </div>
              </a>
            `;
          });
          inboxItems.innerHTML = html;
        } else {
          // Show empty state
          inboxItems.innerHTML = `
            <div class="inbox-empty">
              <i class="fas fa-inbox fa-2x mb-2"></i>
              <p class="mb-0">No new messages</p>
            </div>
          `;
        }
      }
    }
  })
  .catch(error => {
    console.error('Error updating inbox dropdown:', error);
  });
}

// Update inbox badge every 30 seconds
setInterval(updateInboxBadge, 30000);

// Update immediately on page load
setTimeout(updateInboxBadge, 1000);
