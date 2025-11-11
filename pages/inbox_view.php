<?php
/**
 * Inbox Page - User Inbox Messages View
 */

// Include inbox functions
require_once '../scripts/db/inbox.php';
require_once '../scripts/db/data.php';

// Check if user is logged in
if (!isset($userID) || !$userID) {
    echo '<div class="alert alert-warning text-center mt-5">';
    echo '<i class="fas fa-user-lock fa-3x mb-3"></i>';
    echo '<h4>Please Login</h4>';
    echo '<p>You need to be logged in to view your inbox.</p>';
    echo '<a href="login_view.php" class="btn btn-primary">Login</a>';
    echo '</div>';
    exit;
}

// Get filter from query parameter (default: all)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Validate filter
$validFilters = ['all', 'unread', 'read', 'archived'];
if (!in_array($filter, $validFilters)) {
    $filter = 'all';
}

// Get inbox messages
$messages = getInboxMessages($pdo, $userID, $filter);

// Get counts for each status
$allCount = getInboxCount($pdo, $userID, 'all');
$unreadCount = getInboxCount($pdo, $userID, 'unread');
$readCount = getInboxCount($pdo, $userID, 'read');
$archivedCount = getInboxCount($pdo, $userID, 'archived');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../bootstrap-5.3.6-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styling/main.css">
    <link rel="stylesheet" href="../styling/inbox.css">
    <title>Inbox - Newswire AI</title>
</head>
<body>
    <div class="inbox-container">
        <!-- Header -->
        <div class="inbox-header">
            <h2>
                <i class="fas fa-inbox"></i>
                Inbox
            </h2>
            <div class="inbox-actions">
                <button class="action-btn" id="refreshBtn" onclick="location.reload()" title="Refresh inbox">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <button class="action-btn" id="selectAllBtn" onclick="toggleSelectAll()">
                    <i class="fas fa-check-square"></i> Select All
                </button>
                <button class="action-btn" id="bulkMarkReadBtn" onclick="bulkMarkRead()" disabled>
                    <i class="fas fa-envelope-open"></i> Mark Read
                </button>
                <button class="action-btn" id="bulkArchiveBtn" onclick="bulkArchive()" disabled>
                    <i class="fas fa-archive"></i> Archive
                </button>
                <button class="action-btn" id="bulkDeleteBtn" onclick="bulkDelete()" disabled>
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="inbox-filters">
            <a href="?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                <i class="fas fa-inbox"></i>
                All
                <span class="filter-badge"><?php echo $allCount; ?></span>
            </a>
            <a href="?filter=unread" class="filter-btn <?php echo $filter === 'unread' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i>
                Unread
                <span class="filter-badge"><?php echo $unreadCount; ?></span>
            </a>
            <a href="?filter=read" class="filter-btn <?php echo $filter === 'read' ? 'active' : ''; ?>">
                <i class="fas fa-envelope-open"></i>
                Read
                <span class="filter-badge"><?php echo $readCount; ?></span>
            </a>
            <a href="?filter=archived" class="filter-btn <?php echo $filter === 'archived' ? 'active' : ''; ?>">
                <i class="fas fa-archive"></i>
                Archived
                <span class="filter-badge"><?php echo $archivedCount; ?></span>
            </a>
        </div>

        <!-- Messages -->
        <div class="messages-list">
            <?php if (empty($messages)): ?>
                <div class="empty-inbox">
                    <i class="fas fa-inbox"></i>
                    <h3>No Messages</h3>
                    <p>Your <?php echo $filter === 'all' ? 'inbox' : $filter; ?> is empty.</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message-card <?php echo htmlspecialchars($message['status']); ?>" data-message-id="<?php echo $message['message_ID']; ?>">
                        <input type="checkbox" class="message-checkbox" onchange="updateBulkActions()">
                        
                        <div class="message-content">
                            <div class="message-header">
                                <h3 class="message-subject">
                                    <?php echo htmlspecialchars($message['subject']); ?>
                                </h3>
                                <span class="message-status <?php echo htmlspecialchars($message['status']); ?>">
                                    <?php echo htmlspecialchars($message['status']); ?>
                                </span>
                            </div>

                            <div class="message-snippet">
                                <?php echo nl2br(htmlspecialchars($message['snippet'])); ?>
                            </div>

                            <div class="message-meta">
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('F j, Y \a\t g:i A', strtotime($message['date'])); ?>
                                </div>
                                
                                <?php if ($message['newsletter_title']): ?>
                                    <div class="meta-item">
                                        <i class="fas fa-newspaper"></i>
                                        <?php echo htmlspecialchars($message['newsletter_title']); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($message['creator_username']): ?>
                                    <div class="meta-item">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($message['creator_username']); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="message-actions">
                                    <?php if ($message['status'] === 'unread'): ?>
                                        <button class="msg-action-btn" onclick="markAsRead(<?php echo $message['message_ID']; ?>)">
                                            <i class="fas fa-envelope-open"></i> Mark Read
                                        </button>
                                    <?php else: ?>
                                        <button class="msg-action-btn" onclick="markAsUnread(<?php echo $message['message_ID']; ?>)">
                                            <i class="fas fa-envelope"></i> Mark Unread
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($message['status'] !== 'archived'): ?>
                                        <button class="msg-action-btn" onclick="archiveMessage(<?php echo $message['message_ID']; ?>)">
                                            <i class="fas fa-archive"></i> Archive
                                        </button>
                                    <?php endif; ?>

                                    <button class="msg-action-btn danger" onclick="deleteMessage(<?php echo $message['message_ID']; ?>)">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Listen for theme changes from parent window
        window.addEventListener('message', function(event) {
            if (event.data && event.data.type === 'themeChange') {
                const theme = event.data.theme;
                if (theme === 'light') {
                    document.body.classList.add('light-mode');
                } else {
                    document.body.classList.remove('light-mode');
                }
            }
        });

        // Apply saved theme on load
        const savedTheme = localStorage.getItem('theme') || 'dark';
        if (savedTheme === 'light') {
            document.body.classList.add('light-mode');
        }

        // Track selected messages
        let selectedMessages = new Set();

        // Toggle select all
        function toggleSelectAll() {
            const checkboxes = document.querySelectorAll('.message-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = !allChecked;
            });
            
            updateBulkActions();
        }

        // Update bulk action buttons state
        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.message-checkbox:checked');
            const count = checkboxes.length;
            
            document.getElementById('bulkMarkReadBtn').disabled = count === 0;
            document.getElementById('bulkArchiveBtn').disabled = count === 0;
            document.getElementById('bulkDeleteBtn').disabled = count === 0;
            
            // Update select all button text
            const selectAllBtn = document.getElementById('selectAllBtn');
            if (count > 0) {
                selectAllBtn.innerHTML = '<i class="fas fa-times-circle"></i> Deselect All';
            } else {
                selectAllBtn.innerHTML = '<i class="fas fa-check-square"></i> Select All';
            }
        }

        // Get selected message IDs
        function getSelectedMessageIDs() {
            const checkboxes = document.querySelectorAll('.message-checkbox:checked');
            return Array.from(checkboxes).map(cb => {
                return parseInt(cb.closest('.message-card').dataset.messageId);
            });
        }

        // Mark single message as read
        function markAsRead(messageID) {
            sendAction('markRead', messageID, () => {
                location.reload();
            });
        }

        // Mark single message as unread
        function markAsUnread(messageID) {
            sendAction('markUnread', messageID, () => {
                location.reload();
            });
        }

        // Archive single message
        function archiveMessage(messageID) {
            if (confirm('Archive this message?')) {
                sendAction('archive', messageID, () => {
                    location.reload();
                });
            }
        }

        // Delete single message
        function deleteMessage(messageID) {
            if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
                sendAction('delete', messageID, () => {
                    location.reload();
                });
            }
        }

        // Bulk mark as read
        function bulkMarkRead() {
            const messageIDs = getSelectedMessageIDs();
            if (messageIDs.length === 0) return;
            
            sendBulkAction('bulkUpdate', messageIDs, 'read', () => {
                location.reload();
            });
        }

        // Bulk archive
        function bulkArchive() {
            const messageIDs = getSelectedMessageIDs();
            if (messageIDs.length === 0) return;
            
            if (confirm(`Archive ${messageIDs.length} message(s)?`)) {
                sendBulkAction('bulkUpdate', messageIDs, 'archived', () => {
                    location.reload();
                });
            }
        }

        // Bulk delete
        function bulkDelete() {
            const messageIDs = getSelectedMessageIDs();
            if (messageIDs.length === 0) return;
            
            if (confirm(`Are you sure you want to delete ${messageIDs.length} message(s)? This action cannot be undone.`)) {
                sendBulkAction('bulkDelete', messageIDs, null, () => {
                    location.reload();
                });
            }
        }

        // Send single action to server
        function sendAction(action, messageID, callback) {
            fetch('../scripts/db/inbox.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=${action}&messageID=${messageID}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    callback();
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }

        // Send bulk action to server
        function sendBulkAction(action, messageIDs, status, callback) {
            const formData = new URLSearchParams();
            formData.append('action', action);
            formData.append('messageIDs', JSON.stringify(messageIDs));
            if (status) {
                formData.append('status', status);
            }
            
            fetch('../scripts/db/inbox.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    callback();
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }

        // Auto-refresh inbox every 30 seconds to check for new messages
        let autoRefreshInterval;
        
        function startAutoRefresh() {
            // Clear any existing interval
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
            
            // Refresh every 30 seconds
            autoRefreshInterval = setInterval(() => {
                checkForNewMessages();
            }, 30000);
        }
        
        function checkForNewMessages() {
            const currentFilter = new URLSearchParams(window.location.search).get('filter') || 'all';
            
            fetch('../scripts/php/inbox_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'get_all_messages',
                    status: currentFilter,
                    limit: 50,
                    offset: 0
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update counts in filter badges
                    updateFilterBadges(data.counts);
                    
                    // Update the parent window's inbox badge
                    updateParentInboxBadge(data.counts.unread);
                    
                    // If there are new messages, show a notification
                    const currentMessageCount = document.querySelectorAll('.message-card').length;
                    if (data.messages.length > currentMessageCount) {
                        showNewMessageNotification();
                    }
                }
            })
            .catch(error => {
                console.error('Error checking for new messages:', error);
            });
        }
        
        function updateFilterBadges(counts) {
            const filterBadges = {
                'all': counts.all,
                'unread': counts.unread,
                'read': counts.read,
                'archived': counts.archived
            };
            
            Object.keys(filterBadges).forEach(filter => {
                const badge = document.querySelector(`a[href="?filter=${filter}"] .filter-badge`);
                if (badge) {
                    badge.textContent = filterBadges[filter];
                }
            });
        }
        
        function updateParentInboxBadge(unreadCount) {
            try {
                const parentWindow = window.parent && window.parent !== window ? window.parent : null;
                
                if (parentWindow) {
                    const badge = parentWindow.document.querySelector('.inbox-badge');
                    const dropdownBadge = parentWindow.document.querySelector('.inbox-header .badge');
                    
                    if (unreadCount > 0) {
                        if (badge) {
                            badge.textContent = unreadCount;
                            badge.style.display = 'inline-block';
                        }
                        if (dropdownBadge) {
                            dropdownBadge.textContent = `${unreadCount} unread`;
                        }
                    } else {
                        if (badge) {
                            badge.style.display = 'none';
                        }
                        if (dropdownBadge) {
                            dropdownBadge.textContent = '0 unread';
                        }
                    }
                }
            } catch (error) {
                // Ignore cross-origin errors
                console.log('Cannot update parent inbox badge (cross-origin)');
            }
        }
        
        function showNewMessageNotification() {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #3498db;
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                z-index: 10000;
                animation: slideIn 0.3s ease;
            `;
            notification.innerHTML = `
                <i class="fas fa-envelope"></i>
                <strong>New Message!</strong>
                <button onclick="location.reload()" style="margin-left: 10px; background: white; color: #3498db; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                    Refresh
                </button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }
        
        // Start auto-refresh when page loads
        startAutoRefresh();
        
        // Stop auto-refresh when page is hidden/closed
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                }
            } else {
                startAutoRefresh();
                checkForNewMessages(); // Check immediately when page becomes visible
            }
        });
    </script>
    
    <style>
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
    </style>
</body>
</html>
