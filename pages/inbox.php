<?php require_once '../scripts/db/data.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../bootstrap-5.3.6-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../styling/main.css">
  <style>
    .inbox-container {
      max-width: 1200px;
      margin: 0 auto;
    }

    .inbox-header-section {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 2rem;
      border-radius: 15px;
      margin-bottom: 2rem;
      color: white;
    }

    .inbox-tabs {
      display: flex;
      gap: 1rem;
      margin-bottom: 2rem;
      border-bottom: 2px solid #e0e0e0;
      padding-bottom: 0;
    }

    .inbox-tab {
      padding: 0.75rem 1.5rem;
      background: none;
      border: none;
      color: #666;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s;
      border-bottom: 3px solid transparent;
      margin-bottom: -2px;
    }

    .inbox-tab.active {
      color: #667eea;
      border-bottom-color: #667eea;
    }

    .inbox-tab:hover {
      color: #667eea;
    }

    .message-card {
      background: white;
      border: 1px solid #e0e0e0;
      border-radius: 10px;
      padding: 1.5rem;
      margin-bottom: 1rem;
      transition: all 0.3s;
      position: relative;
    }

    .message-card:hover {
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      transform: translateY(-2px);
    }

    .message-card.unread {
      background: linear-gradient(to right, #f8f9ff, #ffffff);
      border-left: 4px solid #667eea;
    }

    .message-card.read {
      opacity: 0.8;
    }

    .message-header {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: 0.75rem;
    }

    .message-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: #2c3e50;
      margin: 0;
      flex: 1;
    }

    .message-status {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      margin-left: 1rem;
    }

    .message-status.unread {
      background: #667eea;
      color: white;
    }

    .message-status.read {
      background: #e0e0e0;
      color: #666;
    }

    .message-content {
      color: #555;
      margin-bottom: 0.75rem;
      line-height: 1.6;
    }

    .message-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.85rem;
      color: #999;
      padding-top: 0.75rem;
      border-top: 1px solid #f0f0f0;
    }

    .message-actions {
      display: flex;
      gap: 0.5rem;
    }

    .action-btn {
      padding: 0.4rem 1rem;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 0.85rem;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 0.3rem;
    }

    .action-btn.mark-read {
      background: #667eea;
      color: white;
    }

    .action-btn.mark-read:hover {
      background: #5568d3;
    }

    .action-btn.delete {
      background: #e74c3c;
      color: white;
    }

    .action-btn.delete:hover {
      background: #c0392b;
    }

    .empty-inbox {
      text-align: center;
      padding: 4rem 2rem;
      color: #999;
    }

    .empty-inbox i {
      font-size: 4rem;
      color: #ddd;
      margin-bottom: 1rem;
    }

    .bulk-actions {
      display: flex;
      gap: 1rem;
      margin-bottom: 1.5rem;
      padding: 1rem;
      background: #f8f9fa;
      border-radius: 8px;
      align-items: center;
    }

    .bulk-actions button {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 0.9rem;
      transition: all 0.3s;
    }

    .mark-all-read-btn {
      background: #667eea;
      color: white;
    }

    .mark-all-read-btn:hover {
      background: #5568d3;
    }

    .delete-all-read-btn {
      background: #e74c3c;
      color: white;
    }

    .delete-all-read-btn:hover {
      background: #c0392b;
    }

    .login-prompt {
      text-align: center;
      padding: 4rem 2rem;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 15px;
      color: white;
    }

    .login-prompt i {
      font-size: 4rem;
      margin-bottom: 1rem;
      opacity: 0.8;
    }

    .login-prompt a {
      display: inline-block;
      margin-top: 1rem;
      padding: 0.75rem 2rem;
      background: white;
      color: #667eea;
      text-decoration: none;
      border-radius: 25px;
      font-weight: 600;
      transition: all 0.3s;
    }

    .login-prompt a:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
  </style>
</head>
<body>
  <div class="inbox-container py-4">
    <?php if (isset($userID) && $userID): ?>
      <!-- Inbox Header -->
      <div class="inbox-header-section">
        <h1 class="mb-2"><i class="fas fa-inbox me-2"></i>Inbox</h1>
        <p class="mb-0">Manage your messages and notifications</p>
      </div>

      <!-- Tabs -->
      <div class="inbox-tabs">
        <button class="inbox-tab active" data-tab="all">
          All Messages
        </button>
        <button class="inbox-tab" data-tab="unread">
          Unread <span class="badge bg-primary ms-1"><?php echo isset($inboxAmount) ? $inboxAmount : 0; ?></span>
        </button>
        <button class="inbox-tab" data-tab="read">
          Read
        </button>
      </div>

      <!-- Bulk Actions -->
      <div class="bulk-actions">
        <span class="text-muted">Bulk Actions:</span>
        <button class="mark-all-read-btn" onclick="markAllAsRead()">
          <i class="fas fa-check-double"></i> Mark All as Read
        </button>
        <button class="delete-all-read-btn" onclick="deleteAllRead()">
          <i class="fas fa-trash"></i> Delete All Read
        </button>
      </div>

      <!-- Messages -->
      <div class="messages-container">
        <?php
        // Fetch all messages for the user
        $stmt = $pdo->prepare("SELECT * FROM tab_inbox WHERE user_ID = ? ORDER BY created_at DESC");
        $stmt->execute([$userID]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($messages) > 0):
          foreach ($messages as $message):
            $isUnread = $message['status'] === 'unread';
            $statusClass = $isUnread ? 'unread' : 'read';
        ?>
          <div class="message-card <?php echo $statusClass; ?>" data-status="<?php echo $message['status']; ?>" data-message-id="<?php echo $message['inbox_ID']; ?>">
            <div class="message-header">
              <h3 class="message-title"><?php echo htmlspecialchars($message['title'] ?? 'No Title'); ?></h3>
              <span class="message-status <?php echo $statusClass; ?>">
                <?php echo $isUnread ? 'Unread' : 'Read'; ?>
              </span>
            </div>
            <div class="message-content">
              <?php echo nl2br(htmlspecialchars($message['message'] ?? 'No content')); ?>
            </div>
            <div class="message-meta">
              <span>
                <i class="far fa-clock me-1"></i>
                <?php echo date('F j, Y \a\t g:i A', strtotime($message['created_at'])); ?>
              </span>
              <div class="message-actions">
                <?php if ($isUnread): ?>
                  <button class="action-btn mark-read" onclick="markAsRead(<?php echo $message['inbox_ID']; ?>)">
                    <i class="fas fa-check"></i> Mark as Read
                  </button>
                <?php endif; ?>
                <button class="action-btn delete" onclick="deleteMessage(<?php echo $message['inbox_ID']; ?>)">
                  <i class="fas fa-trash"></i> Delete
                </button>
              </div>
            </div>
          </div>
        <?php
          endforeach;
        else:
        ?>
          <div class="empty-inbox">
            <i class="fas fa-inbox"></i>
            <h3>Your inbox is empty</h3>
            <p>You don't have any messages yet.</p>
          </div>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <!-- Login Prompt -->
      <div class="login-prompt">
        <i class="fas fa-user-lock"></i>
        <h2>Please Log In</h2>
        <p>You need to be logged in to view your inbox.</p>
        <a href="#" data-page="login_view.php">
          <i class="fas fa-sign-in-alt me-2"></i>Log In
        </a>
      </div>
    <?php endif; ?>
  </div>

  <script src="../bootstrap-5.3.6-dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Tab functionality
    const tabs = document.querySelectorAll('.inbox-tab');
    const messages = document.querySelectorAll('.message-card');

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        // Remove active class from all tabs
        tabs.forEach(t => t.classList.remove('active'));
        // Add active class to clicked tab
        tab.classList.add('active');

        const filter = tab.dataset.tab;

        // Filter messages
        messages.forEach(message => {
          const status = message.dataset.status;
          
          if (filter === 'all') {
            message.style.display = 'block';
          } else if (filter === 'unread' && status === 'unread') {
            message.style.display = 'block';
          } else if (filter === 'read' && status === 'read') {
            message.style.display = 'block';
          } else {
            message.style.display = 'none';
          }
        });

        // Check if any messages are visible
        const visibleMessages = Array.from(messages).filter(m => m.style.display !== 'none');
        const messagesContainer = document.querySelector('.messages-container');
        
        if (visibleMessages.length === 0 && filter !== 'all') {
          const existingEmpty = messagesContainer.querySelector('.empty-inbox');
          if (!existingEmpty) {
            const emptyDiv = document.createElement('div');
            emptyDiv.className = 'empty-inbox';
            emptyDiv.innerHTML = `
              <i class="fas fa-inbox"></i>
              <h3>No ${filter} messages</h3>
              <p>You don't have any ${filter} messages.</p>
            `;
            messagesContainer.appendChild(emptyDiv);
          }
        } else {
          const existingEmpty = messagesContainer.querySelector('.empty-inbox');
          if (existingEmpty && messages.length > 0) {
            existingEmpty.remove();
          }
        }
      });
    });

    // Mark as read
    function markAsRead(messageId) {
      fetch('../scripts/php/inbox_actions.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=mark_read&message_id=${messageId}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert('Error: ' + (data.message || 'Could not mark message as read'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      });
    }

    // Delete message
    function deleteMessage(messageId) {
      if (confirm('Are you sure you want to delete this message?')) {
        fetch('../scripts/php/inbox_actions.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `action=delete&message_id=${messageId}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert('Error: ' + (data.message || 'Could not delete message'));
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred. Please try again.');
        });
      }
    }

    // Mark all as read
    function markAllAsRead() {
      if (confirm('Mark all messages as read?')) {
        fetch('../scripts/php/inbox_actions.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'action=mark_all_read'
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert('Error: ' + (data.message || 'Could not mark all messages as read'));
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred. Please try again.');
        });
      }
    }

    // Delete all read
    function deleteAllRead() {
      if (confirm('Are you sure you want to delete all read messages? This cannot be undone.')) {
        fetch('../scripts/php/inbox_actions.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'action=delete_all_read'
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert('Error: ' + (data.message || 'Could not delete messages'));
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred. Please try again.');
        });
      }
    }
  </script>
</body>
</html>
