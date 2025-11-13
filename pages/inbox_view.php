<?php
/**
 * Inbox Page - User Inbox Messages View
 */

// Include inbox functions
require_once '../scripts/db/inbox.php';
require_once '../scripts/db/data.php';

// Check if user is logged in
if (!isset($userID) || !$userID) {
    header('location: login_view.php');
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
    <link rel="stylesheet" href="../styling/animations.css">
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

    <script src="../scripts/js/theme.js"></script>
    <script src="../scripts/js/inboxActions.js"></script>
</body>
</html>
