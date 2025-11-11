<?php
/**
 * Verification Script for Favorites System
 * This script tests that the favorites and notification systems are working correctly
 */

session_start();
require_once 'scripts/db/config.php';
require_once 'scripts/db/inbox.php';

header('Content-Type: text/html; charset=utf-8');

// Check if user is logged in
$userID = isset($_COOKIE['userID']) ? intval($_COOKIE['userID']) : null;

if (!$userID) {
    die('<h2>Error: Please log in first</h2><p>You need to be logged in to test the favorites system.</p>');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorites System Verification</title>
    <link rel="stylesheet" href="bootstrap-5.3.6-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styling/main.css">
    <link rel="stylesheet" href="styling/favorites.css">
    <style>
        .test-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .test-result {
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .test-result.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .test-result.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .item-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">
            <i class="fas fa-heart"></i>
            Favorites System Verification
        </h1>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Logged in as User ID:</strong> <?php echo $userID; ?>
        </div>

        <!-- Test 1: Database Connection -->
        <div class="test-section">
            <h3><i class="fas fa-database"></i> Database Connection</h3>
            <?php
            try {
                $stmt = $pdo->query("SELECT 1");
                echo '<div class="test-result success"><i class="fas fa-check-circle"></i> Database connection successful!</div>';
            } catch (PDOException $e) {
                echo '<div class="test-result error"><i class="fas fa-times-circle"></i> Database connection failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>

        <!-- Test 2: Available Newsletters -->
        <div class="test-section">
            <h3><i class="fas fa-newspaper"></i> Available Newsletters</h3>
            <?php
            try {
                $stmt = $pdo->query("
                    SELECT n.newsletter_ID, n.title, n.status, u.username as creator_username
                    FROM tab_newsletters n
                    LEFT JOIN tab_users u ON n.user_ID = u.user_ID
                    WHERE n.status = 'published'
                    LIMIT 3
                ");
                $newsletters = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($newsletters) > 0) {
                    echo '<div class="test-result success"><i class="fas fa-check-circle"></i> Found ' . count($newsletters) . ' newsletters</div>';
                    foreach ($newsletters as $newsletter) {
                        echo '<div class="item-card">';
                        echo '<h5>' . htmlspecialchars($newsletter['title']) . '</h5>';
                        echo '<p class="text-muted mb-2">By: ' . htmlspecialchars($newsletter['creator_username']) . '</p>';
                        echo '<button class="btn btn-sm favorite-btn" data-favorite-btn data-favorite-type="newsletter" data-favorite-id="' . $newsletter['newsletter_ID'] . '">';
                        echo '<i class="far fa-heart"></i> <span>Add to Favorites</span>';
                        echo '</button>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="test-result error"><i class="fas fa-times-circle"></i> No published newsletters found</div>';
                }
            } catch (PDOException $e) {
                echo '<div class="test-result error"><i class="fas fa-times-circle"></i> Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>

        <!-- Test 3: Available Categories -->
        <div class="test-section">
            <h3><i class="fas fa-tags"></i> Available Categories</h3>
            <?php
            try {
                $stmt = $pdo->query("SELECT category_ID, name, description FROM tab_categories LIMIT 3");
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($categories) > 0) {
                    echo '<div class="test-result success"><i class="fas fa-check-circle"></i> Found ' . count($categories) . ' categories</div>';
                    foreach ($categories as $category) {
                        echo '<div class="item-card">';
                        echo '<h5>' . htmlspecialchars($category['name']) . '</h5>';
                        if ($category['description']) {
                            echo '<p class="text-muted mb-2">' . htmlspecialchars($category['description']) . '</p>';
                        }
                        echo '<button class="btn btn-sm favorite-btn" data-favorite-btn data-favorite-type="category" data-favorite-id="' . $category['category_ID'] . '">';
                        echo '<i class="far fa-heart"></i> <span>Follow Category</span>';
                        echo '</button>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="test-result error"><i class="fas fa-times-circle"></i> No categories found</div>';
                }
            } catch (PDOException $e) {
                echo '<div class="test-result error"><i class="fas fa-times-circle"></i> Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>

        <!-- Test 4: Current Favorites -->
        <div class="test-section">
            <h3><i class="fas fa-heart"></i> Your Current Favorites</h3>
            <?php
            try {
                // Newsletter favorites
                $stmt = $pdo->prepare("
                    SELECT n.newsletter_ID, n.title
                    FROM con_favorites_newsletters f
                    INNER JOIN tab_newsletters n ON f.newsletter_ID = n.newsletter_ID
                    WHERE f.user_ID = ?
                ");
                $stmt->execute([$userID]);
                $favNewsletters = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Category favorites
                $stmt = $pdo->prepare("
                    SELECT c.category_ID, c.name
                    FROM con_favorites_categories f
                    INNER JOIN tab_categories c ON f.category_ID = c.category_ID
                    WHERE f.user_ID = ?
                ");
                $stmt->execute([$userID]);
                $favCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo '<div class="test-result success">';
                echo '<i class="fas fa-check-circle"></i> ';
                echo 'You have ' . count($favNewsletters) . ' favorite newsletter(s) and ' . count($favCategories) . ' favorite category(ies)';
                echo '</div>';
                
                if (count($favNewsletters) > 0) {
                    echo '<h5 class="mt-3">Favorite Newsletters:</h5>';
                    foreach ($favNewsletters as $fav) {
                        echo '<div class="item-card">' . htmlspecialchars($fav['title']) . '</div>';
                    }
                }
                
                if (count($favCategories) > 0) {
                    echo '<h5 class="mt-3">Favorite Categories:</h5>';
                    foreach ($favCategories as $fav) {
                        echo '<div class="item-card">' . htmlspecialchars($fav['name']) . '</div>';
                    }
                }
                
            } catch (PDOException $e) {
                echo '<div class="test-result error"><i class="fas fa-times-circle"></i> Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>

        <!-- Test 5: Inbox Messages -->
        <div class="test-section">
            <h3><i class="fas fa-inbox"></i> Recent Inbox Messages</h3>
            <?php
            try {
                $messages = getInboxMessages($pdo, $userID, 'all', 5, 0);
                
                if (count($messages) > 0) {
                    echo '<div class="test-result success"><i class="fas fa-check-circle"></i> You have ' . count($messages) . ' message(s)</div>';
                    foreach ($messages as $msg) {
                        echo '<div class="item-card">';
                        echo '<h6>' . htmlspecialchars($msg['subject']) . '</h6>';
                        echo '<p class="mb-1">' . htmlspecialchars(substr($msg['snippet'], 0, 100)) . '...</p>';
                        echo '<small class="text-muted">' . date('M j, Y g:i A', strtotime($msg['date'])) . '</small>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="test-result error"><i class="fas fa-info-circle"></i> No inbox messages yet. Try favoriting something above!</div>';
                }
            } catch (PDOException $e) {
                echo '<div class="test-result error"><i class="fas fa-times-circle"></i> Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>

        <div class="alert alert-success mt-4">
            <h4><i class="fas fa-check-circle"></i> How to Test Live Updates:</h4>
            <ol>
                <li>Click the "Add to Favorites" button on any newsletter or category above</li>
                <li>You should see a success toast notification appear</li>
                <li>The heart icon should become solid (filled) immediately</li>
                <li><strong>Without refreshing</strong>, the "Recent Inbox Messages" section should update automatically with a new notification (wait 2-3 seconds)</li>
                <li>The inbox badge in the parent window should update live (if viewing in iframe)</li>
                <li>The inbox dropdown should show the new message without refresh</li>
            </ol>
            <p class="mb-0"><strong>Note:</strong> The inbox also auto-refreshes every 30 seconds to check for new messages.</p>
        </div>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <button onclick="location.reload()" class="btn btn-secondary">
                <i class="fas fa-sync"></i> Refresh Tests
            </button>
        </div>
    </div>

    <script src="bootstrap-5.3.6-dist/js/bootstrap.bundle.min.js"></script>
    <script src="scripts/favorites.js"></script>
    
    <script>
        // Auto-refresh inbox messages section every 5 seconds to show new notifications
        let refreshCount = 0;
        const maxRefreshes = 12; // Stop after 1 minute (12 * 5 seconds)
        
        function refreshInboxSection() {
            if (refreshCount >= maxRefreshes) {
                return; // Stop auto-refresh after 1 minute
            }
            
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
                    updateInboxDisplay(data.messages);
                    refreshCount++;
                }
            })
            .catch(error => {
                console.error('Error refreshing inbox:', error);
            });
        }
        
        function updateInboxDisplay(messages) {
            const inboxSection = document.querySelector('.test-section:nth-of-type(5)');
            if (!inboxSection) return;
            
            // Find the result div
            let resultDiv = inboxSection.querySelector('.test-result');
            
            // Update or create result message
            if (messages.length > 0) {
                if (resultDiv && !resultDiv.classList.contains('success')) {
                    resultDiv.className = 'test-result success';
                    resultDiv.innerHTML = '<i class="fas fa-check-circle"></i> You have ' + messages.length + ' message(s)';
                } else if (!resultDiv) {
                    resultDiv = document.createElement('div');
                    resultDiv.className = 'test-result success';
                    resultDiv.innerHTML = '<i class="fas fa-check-circle"></i> You have ' + messages.length + ' message(s)';
                    inboxSection.querySelector('h3').after(resultDiv);
                } else {
                    resultDiv.innerHTML = '<i class="fas fa-check-circle"></i> You have ' + messages.length + ' message(s)';
                }
                
                // Remove old message cards
                const oldCards = inboxSection.querySelectorAll('.item-card');
                oldCards.forEach(card => card.remove());
                
                // Add new message cards
                messages.forEach(msg => {
                    const card = document.createElement('div');
                    card.className = 'item-card';
                    
                    const date = new Date(msg.date);
                    const formattedDate = date.toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric', 
                        year: 'numeric',
                        hour: 'numeric',
                        minute: '2-digit'
                    });
                    
                    card.innerHTML = `
                        <h6>${msg.subject || 'Notification'}</h6>
                        <p class="mb-1">${msg.snippet ? msg.snippet.substring(0, 100) + '...' : ''}</p>
                        <small class="text-muted">${formattedDate}</small>
                    `;
                    
                    inboxSection.appendChild(card);
                });
            }
        }
        
        // Start auto-refresh after page loads
        setTimeout(() => {
            setInterval(refreshInboxSection, 5000); // Check every 5 seconds
        }, 2000); // Wait 2 seconds before starting
        
        // Listen for favorite events to trigger immediate refresh
        document.addEventListener('DOMContentLoaded', () => {
            const favoriteButtons = document.querySelectorAll('[data-favorite-btn]');
            favoriteButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Reset refresh count when user interacts
                    refreshCount = 0;
                    // Refresh after 2 seconds to allow server to process
                    setTimeout(refreshInboxSection, 2000);
                });
            });
        });
    </script>
</body>
</html>
