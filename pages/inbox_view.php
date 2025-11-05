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
    <title>Inbox - Newswire AI</title>
    <style>
        .inbox-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .inbox-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
        }

        .inbox-header h2 {
            color: var(--text-primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .inbox-filters {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 20px;
            border: 1px solid var(--border-color);
            background: var(--card-bg);
            color: var(--text-primary);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-btn:hover {
            background: var(--hover-bg);
            color: var(--text-primary);
            transform: translateY(-2px);
        }

        .filter-btn.active {
            background: var(--accent-blue);
            color: white;
            border-color: var(--accent-blue);
        }

        .filter-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: bold;
        }

        .inbox-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .action-btn {
            padding: 8px 16px;
            border: 1px solid var(--border-color);
            background: var(--card-bg);
            color: var(--text-primary);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9em;
        }

        .action-btn:hover {
            background: var(--hover-bg);
        }

        .action-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .message-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            position: relative;
        }

        .message-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px var(--shadow);
        }

        .message-card.unread {
            border-left: 4px solid var(--accent-blue);
            background: rgba(52, 152, 219, 0.05);
        }

        .message-card.archived {
            opacity: 0.7;
        }

        .message-checkbox {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .message-content {
            margin-left: 40px;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 10px;
        }

        .message-subject {
            font-size: 1.1em;
            font-weight: bold;
            color: var(--text-primary);
            margin: 0;
        }

        .message-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
        }

        .message-status.unread {
            background: var(--accent-blue);
            color: white;
        }

        .message-status.read {
            background: var(--text-muted);
            color: white;
        }

        .message-status.archived {
            background: var(--accent-green);
            color: white;
        }

        .message-snippet {
            color: var(--text-secondary);
            margin: 10px 0;
            line-height: 1.6;
        }

        .message-meta {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--text-muted);
            font-size: 0.9em;
        }

        .meta-item i {
            color: var(--accent-blue);
        }

        .message-actions {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }

        .msg-action-btn {
            padding: 6px 12px;
            border: 1px solid var(--border-color);
            background: var(--bg-secondary);
            color: var(--text-primary);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.85em;
        }

        .msg-action-btn:hover {
            background: var(--hover-bg);
        }

        .msg-action-btn.danger:hover {
            background: var(--accent-red);
            color: white;
            border-color: var(--accent-red);
        }

        .empty-inbox {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-inbox i {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-inbox h3 {
            color: var(--text-secondary);
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .inbox-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .message-content {
                margin-left: 0;
                margin-top: 30px;
            }

            .message-checkbox {
                left: auto;
                right: 20px;
            }

            .message-meta {
                flex-direction: column;
                align-items: flex-start;
            }

            .message-actions {
                margin-left: 0;
                width: 100%;
            }

            .msg-action-btn {
                flex: 1;
            }
        }
    </style>
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
    </script>
</body>
</html>
