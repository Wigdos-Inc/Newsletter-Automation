<?php
session_start();
require_once '../scripts/db/config.php';

// Get UserID from cookie
$userID = isset($_COOKIE['userID']) ? intval($_COOKIE['userID']) : null;

// Fetch categories
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM tab_categories ORDER BY name ASC LIMIT 3");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Silent fail for now
}

// Fetch newsletters for "Popular" section (by favorite count or date)
$popularNewsletters = [];
try {
    $stmt = $pdo->query("
        SELECT n.newsletter_ID, n.title, n.content, n.creation_date, 
               COUNT(f.user_ID) as fav_count 
        FROM tab_newsletters n 
        LEFT JOIN con_favorites_newsletters f ON n.newsletter_ID = f.newsletter_ID 
        WHERE n.status = 'published'
        GROUP BY n.newsletter_ID, n.title, n.content, n.creation_date 
        ORDER BY fav_count DESC, n.creation_date DESC 
        LIMIT 6
    ");
    $popularNewsletters = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Silent fail
}

// Fetch user's most recent newsletter (for logged-in users)
$userDigest = null;
if ($userID) {
    try {
        $stmt = $pdo->prepare("
            SELECT n.* 
            FROM tab_newsletters n
            INNER JOIN con_favorites_newsletters f ON n.newsletter_ID = f.newsletter_ID
            WHERE f.user_ID = ?
            ORDER BY n.creation_date DESC
            LIMIT 1
        ");
        $stmt->execute([$userID]);
        $userDigest = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Silent fail
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <base href="../">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap-5.3.6-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styling/main.css">
    <link rel="stylesheet" href="styling/home.css">
    <link rel="stylesheet" href="styling/favorites.css">
    <style>
        /* Refresh button styling */
        #refreshHomeBtn {
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        #refreshHomeBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        #refreshHomeBtn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        #refreshHomeBtn i {
            transition: transform 0.3s ease;
        }
        
        #refreshHomeBtn:hover i {
            transform: rotate(90deg);
        }
        
        /* Page header styling */
        .page-header-section {
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.1) 0%, rgba(46, 204, 113, 0.1) 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .page-header-section h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
        }
        
        /* Light mode overrides */
        body.light-mode .page-header-section {
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.05) 0%, rgba(46, 204, 113, 0.05) 100%);
        }
    </style>
    <title>Home - Newswire AI</title>
</head>
<body>
    <div class="container py-3">
        
        <!-- Page Header with Refresh Button -->
        <div class="page-header-section">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1>
                        <i class="fas fa-home me-2"></i>
                        Home
                    </h1>
                    <small class="text-muted" id="lastUpdated">
                        <i class="far fa-clock me-1"></i>
                        Last updated: <span id="updateTime">just now</span>
                    </small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button class="btn btn-outline-primary btn-sm" id="refreshHomeBtn" title="Refresh to see updated favorites">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
        
        <?php if ($userID && $userDigest): ?>
        <!-- User Digest Section (Only for logged-in users) -->
        <section class="user-digest-section mb-3">
            <div class="section-header mb-4">
                <h2 class="section-title">
                    <i class="fas fa-user-circle me-2"></i>
                    Your Personalized Digest
                </h2>
                <p class="section-subtitle">Latest newsletter from your favorites</p>
            </div>
            
            <div class="digest-card">
                <div class="digest-content">
                    <div class="digest-label">
                        <i class="fas fa-star"></i>
                        <span>Featured</span>
                    </div>
                    <h3 class="digest-title"><?php echo htmlspecialchars($userDigest['title']); ?></h3>
                    <p class="digest-description">
                        <?php echo htmlspecialchars(substr(strip_tags($userDigest['content']) ?? 'Your personalized newsletter content will appear here.', 0, 150)); ?>...
                    </p>
                    <div class="digest-meta">
                        <span class="digest-date">
                            <i class="far fa-calendar-alt me-1"></i>
                            <?php echo date('M j, Y', strtotime($userDigest['creation_date'])); ?>
                        </span>
                        <button class="btn-read-digest" onclick="if(parent.window.popupSystem) parent.window.popupSystem.newsPopup(<?php echo $userDigest['newsletter_ID']; ?>)">
                            Read Newsletter <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
                <div class="digest-image">
                    <i class="fas fa-newspaper"></i>
                </div>
            </div>
        </section>
        <?php elseif ($userID): ?>
        <!-- Placeholder for logged-in users without favorites -->
        <section class="user-digest-section mb-3">
            <div class="section-header mb-4">
                <h2 class="section-title">
                    <i class="fas fa-user-circle me-2"></i>
                    Your Personalized Digest
                </h2>
                <p class="section-subtitle">Start favoriting newsletters to see your digest</p>
            </div>
            
            <div class="digest-card digest-placeholder">
                <div class="digest-content">
                    <div class="digest-label">
                        <i class="fas fa-info-circle"></i>
                        <span>Coming Soon when the functionality of the newsletter is complete</span>
                    </div>
                    <h3 class="digest-title">Your Newsletter Digest Will Appear Here</h3>
                    <p class="digest-description">
                        Once you start favoriting newsletters, we'll display your most recent personalized content right here. 
                        This is where you'll get quick access to the latest updates from your favorite sources.
                    </p>
                    <div class="digest-meta">
                        <button class="btn-explore" onclick="parent.postMessage({type: 'navigateTo', page: 'pages/newsletter_view.php'}, '*')">
                            Explore Newsletters <i class="fas fa-compass ms-1"></i>
                        </button>
                    </div>
                </div>
                <div class="digest-image">
                    <i class="fas fa-newspaper"></i>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Newest per Category Section -->
        <section class="newest-category-section mb-3">
            <div class="section-header mb-3">
                <h2 class="section-title">
                    <i class="fas fa-layer-group me-2"></i>
                    Newest per Category
                </h2>
                <p class="section-subtitle">Fresh content from your favorite topics</p>
            </div>

            <div class="row g-3">
                <?php if (count($categories) > 0): ?>
                    <?php foreach ($categories as $category): ?>
                        <?php
                        // Fetch newest newsletter for this category
                        $newestNewsletter = null;
                        try {
                            $stmt = $pdo->prepare("
                                SELECT n.* 
                                FROM tab_newsletters n
                                INNER JOIN con_newsletter_categories c ON n.newsletter_ID = c.newsletter_ID
                                WHERE c.category_ID = ?
                                ORDER BY n.creation_date DESC
                                LIMIT 1
                            ");
                            $stmt->execute([$category['category_ID']]);
                            $newestNewsletter = $stmt->fetch(PDO::FETCH_ASSOC);
                        } catch (PDOException $e) {
                            // Silent fail
                        }
                        ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="category-card">
                                <div class="category-badge">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                    <?php if ($userID): ?>
                                    <button class="favorite-btn-icon" 
                                            data-favorite-btn 
                                            data-favorite-type="category" 
                                            data-favorite-id="<?php echo $category['category_ID']; ?>"
                                            title="Follow category"
                                            style="float: right; margin-top: -2px;">
                                        <i class="far fa-heart"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <div class="category-content">
                                    <?php if ($newestNewsletter): ?>
                                        <h4 class="category-newsletter-title">
                                            <?php echo htmlspecialchars($newestNewsletter['title']); ?>
                                        </h4>
                                        <p class="category-newsletter-desc">
                                            <?php echo htmlspecialchars(substr(strip_tags($newestNewsletter['content']) ?? 'Newsletter description coming soon', 0, 100)); ?>...
                                        </p>
                                        <div class="category-footer">
                                            <span class="category-date">
                                                <i class="far fa-clock me-1"></i>
                                                <?php echo date('M j', strtotime($newestNewsletter['creation_date'])); ?>
                                            </span>
                                            <button class="btn-view-category" onclick="if(parent.window.popupSystem) parent.window.popupSystem.newsPopup(<?php echo $newestNewsletter['newsletter_ID']; ?>)">
                                                View <i class="fas fa-arrow-right ms-1"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div class="category-empty">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p>No newsletters yet</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="empty-state">
                            <i class="fas fa-folder-open fa-3x mb-3"></i>
                            <h4>No Categories Available</h4>
                            <p>Check back soon for new categories!</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Popular Section -->
        <section class="popular-section">
            <div class="section-header mb-3">
                <h2 class="section-title">
                    <i class="fas fa-fire me-2"></i>
                    Popular
                </h2>
                <p class="section-subtitle">Trending newsletters everyone's reading</p>
            </div>

            <div class="row g-3">
                <?php if (count($popularNewsletters) > 0): ?>
                    <?php foreach ($popularNewsletters as $newsletter): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="popular-card">
                                <?php if ($userID): ?>
                                <button class="favorite-btn-icon" 
                                        data-favorite-btn 
                                        data-favorite-type="newsletter" 
                                        data-favorite-id="<?php echo $newsletter['newsletter_ID']; ?>"
                                        title="Add to favorites"
                                        style="position: absolute; top: 15px; right: 15px; z-index: 10;">
                                    <i class="far fa-heart"></i>
                                </button>
                                <?php endif; ?>
                                <div class="popular-icon">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                                <div class="popular-content">
                                    <h4 class="popular-title">
                                        <?php echo htmlspecialchars($newsletter['title'] ?? 'Untitled Newsletter'); ?>
                                    </h4>
                                    <p class="popular-desc">
                                        <?php echo htmlspecialchars(substr(strip_tags($newsletter['content']) ?? 'Discover trending content', 0, 100)); ?>...
                                    </p>
                                    <div class="popular-stats">
                                        <span class="stat-item">
                                            <i class="fas fa-heart me-1"></i>
                                            <?php echo $newsletter['fav_count'] ?? 0; ?>
                                        </span>
                                        <span class="stat-item">
                                            <i class="far fa-calendar me-1"></i>
                                            <?php echo date('M j', strtotime($newsletter['creation_date'])); ?>
                                        </span>
                                    </div>
                                    <button class="btn-read-popular" onclick="if(parent.window.popupSystem) parent.window.popupSystem.newsPopup(<?php echo $newsletter['newsletter_ID']; ?>)">
                                        Read Now <i class="fas fa-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="empty-state">
                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                            <h4>No Popular Newsletters Yet</h4>
                            <p>Be the first to favorite a newsletter!</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </div>

    <script src="bootstrap-5.3.6-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize popup system with newsletter data
        if (parent.window && parent.window.popupSystem) {
            const newsletters = {
                <?php
                // Collect all newsletters to initialize popup system
                $allNewsletters = [];
                
                // Add user digest
                if (isset($userDigest) && $userDigest) {
                    $allNewsletters[$userDigest['newsletter_ID']] = $userDigest;
                }
                
                // Add category newsletters
                foreach ($categories as $category) {
                    try {
                        $stmt = $pdo->prepare("
                            SELECT n.* 
                            FROM tab_newsletters n
                            INNER JOIN con_newsletter_categories c ON n.newsletter_ID = c.newsletter_ID
                            WHERE c.category_ID = ?
                            ORDER BY n.creation_date DESC
                            LIMIT 1
                        ");
                        $stmt->execute([$category['category_ID']]);
                        $catNewsletter = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($catNewsletter) {
                            $allNewsletters[$catNewsletter['newsletter_ID']] = $catNewsletter;
                        }
                    } catch (PDOException $e) {}
                }
                
                // Add popular newsletters
                foreach ($popularNewsletters as $newsletter) {
                    $allNewsletters[$newsletter['newsletter_ID']] = $newsletter;
                }
                
                // Output as JavaScript object
                if (count($allNewsletters) > 0) {
                    $first = true;
                    foreach ($allNewsletters as $id => $newsletter) {
                        if (!$first) echo ",\n                ";
                        $first = false;
                        echo $id . ": {";
                        echo "id: " . $id . ",";
                        echo "title: " . json_encode($newsletter['title']) . ",";
                        echo "content: " . json_encode($newsletter['content']) . ",";
                        echo "creation_date: " . json_encode($newsletter['creation_date']) . ",";
                        echo "status: " . json_encode($newsletter['status'] ?? 'published');
                        echo "}";
                    }
                }
                ?>
            };
            
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
    </script>
    
    <!-- Favorites System -->
    <script src="scripts/favorites.js"></script>
</body>
</html>