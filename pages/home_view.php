<?php
session_start();
require_once '../scripts/db/config.php';

// Get UserID from cookie
$userID = isset($_COOKIE['userID']) ? intval($_COOKIE['userID']) : null;

// Handle AJAX check for newest newsletter
if (isset($_GET['check_newest']) && $_GET['check_newest'] == '1') {
    header('Content-Type: application/json');
    
    try {
        // Get the most recent published newsletter
        $stmt = $pdo->query("
            SELECT newsletter_ID, title, content, creation_date
            FROM tab_newsletters
            WHERE status = 'published'
            ORDER BY creation_date DESC
            LIMIT 1
        ");
        $newestNewsletter = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($newestNewsletter) {
            echo json_encode([
                'has_new' => true,
                'newsletter' => $newestNewsletter
            ]);
        } else {
            echo json_encode(['has_new' => false]);
        }
    } catch (PDOException $e) {
        echo json_encode(['has_new' => false, 'error' => 'Database error']);
    }
    exit;
}

// Fetch the most recent published newsletter for Featured section
$featuredNewsletter = null;
try {
    $stmt = $pdo->query("
        SELECT newsletter_ID, title, content, creation_date
        FROM tab_newsletters
        WHERE status = 'published'
        ORDER BY creation_date DESC
        LIMIT 1
    ");
    $featuredNewsletter = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Silent fail
}

// Fetch all categories (no limit)
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM tab_categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Silent fail for now
}

// Define how many categories to display before dropdown
$maxDisplayedCategories = 3;

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
        
        <?php if ($featuredNewsletter): ?>
        <!-- Featured Newsletter Section -->
        <section class="user-digest-section mb-3" id="featuredSection">
            <div class="section-header mb-4">
                <h2 class="section-title">
                    <i class="fas fa-star me-2"></i>
                    Featured Newsletter
                </h2>
                <p class="section-subtitle">Latest published newsletter</p>
            </div>
            
            <div class="digest-card">
                <div class="digest-content">
                    <div class="digest-label">
                        <i class="fas fa-fire"></i>
                        <span>Newest</span>
                    </div>
                    <h3 class="digest-title" id="featuredTitle"><?php echo htmlspecialchars($featuredNewsletter['title']); ?></h3>
                    <p class="digest-description" id="featuredDescription">
                        <?php echo htmlspecialchars(substr(strip_tags($featuredNewsletter['content']) ?? 'Latest newsletter content.', 0, 150)); ?>...
                    </p>
                    <div class="digest-meta">
                        <span class="digest-date" id="featuredDate">
                            <i class="far fa-calendar-alt me-1"></i>
                            <?php echo date('M j, Y', strtotime($featuredNewsletter['creation_date'])); ?>
                        </span>
                        <button class="btn-read-digest" id="featuredReadBtn" onclick="if(parent.window.popupSystem) parent.window.popupSystem.newsPopup(<?php echo $featuredNewsletter['newsletter_ID']; ?>)">
                            Read Newsletter <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
                <div class="digest-image">
                    <i class="fas fa-newspaper"></i>
                </div>
            </div>
        </section>
        <?php else: ?>
        <!-- Placeholder when no newsletters exist -->
        <section class="user-digest-section mb-3">
            <div class="section-header mb-4">
                <h2 class="section-title">
                    <i class="fas fa-star me-2"></i>
                    Featured Newsletter
                </h2>
                <p class="section-subtitle">No newsletters published yet</p>
            </div>
            
            <div class="digest-card digest-placeholder">
                <div class="digest-content">
                    <div class="digest-label">
                        <i class="fas fa-info-circle"></i>
                        <span>Coming Soon</span>
                    </div>
                    <h3 class="digest-title">Latest Newsletter Will Appear Here</h3>
                    <p class="digest-description">
                        Once newsletters are published, the most recent one will be featured right here.
                    </p>
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

            <?php if (count($categories) > $maxDisplayedCategories): ?>
            <!-- View More Dropdown at the top -->
            <div class="category-dropdown-wrapper">
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="viewMoreCategoriesBtn" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-layer-group me-2"></i>
                        View All Categories (<?php echo count($categories) - $maxDisplayedCategories; ?> more)
                    </button>
                    <ul class="dropdown-menu category-dropdown-menu" aria-labelledby="viewMoreCategoriesBtn">
                        <?php
                        // Show categories from index maxDisplayedCategories onwards in dropdown
                        for ($i = $maxDisplayedCategories; $i < count($categories); $i++):
                            $dropdownCategory = $categories[$i];
                        ?>
                        <li>
                            <a class="dropdown-item category-dropdown-item" href="#" onclick="scrollToCategoryCard(<?php echo $i; ?>); return false;">
                                <span>
                                    <i class="fas fa-folder me-2"></i>
                                    <?php echo htmlspecialchars($dropdownCategory['name']); ?>
                                </span>
                                <?php if ($userID): ?>
                                <button class="favorite-btn-icon-small" 
                                        data-favorite-btn 
                                        data-favorite-type="category" 
                                        data-favorite-id="<?php echo $dropdownCategory['category_ID']; ?>"
                                        title="Follow category"
                                        onclick="event.stopPropagation();">
                                    <i class="far fa-heart"></i>
                                </button>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <div class="row g-3" id="categoryGrid">
                <?php if (count($categories) > 0): ?>
                    <?php foreach ($categories as $index => $category): ?>
                        <?php
                        // Fetch ALL newsletters for this category to get count
                        $categoryNewslettersCount = 0;
                        try {
                            $stmt = $pdo->prepare("
                                SELECT COUNT(*) as count
                                FROM tab_newsletters n
                                INNER JOIN con_newsletter_categories c ON n.newsletter_ID = c.newsletter_ID
                                WHERE c.category_ID = ?
                            ");
                            $stmt->execute([$category['category_ID']]);
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            $categoryNewslettersCount = $result['count'];
                        } catch (PDOException $e) {
                            // Silent fail
                        }
                        ?>
                        <div class="col-lg-4 col-md-6 category-item <?php echo ($index >= $maxDisplayedCategories) ? 'category-hidden' : ''; ?>" id="category-card-<?php echo $index; ?>">
                            <div class="category-card" onclick="openCategoryPopup(<?php echo $category['category_ID']; ?>, '<?php echo htmlspecialchars(addslashes($category['name'])); ?>')">
                                <div class="category-badge">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                    <?php if ($userID): ?>
                                    <button class="favorite-btn-icon" 
                                            data-favorite-btn 
                                            data-favorite-type="category" 
                                            data-favorite-id="<?php echo $category['category_ID']; ?>"
                                            title="Follow category"
                                            style="float: right; margin-top: -2px;"
                                            onclick="event.stopPropagation();">
                                        <i class="far fa-heart"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <div class="category-content">
                                    <div class="category-count-display">
                                        <div class="category-icon-display">
                                            <i class="fas fa-newspaper"></i>
                                        </div>
                                        <div class="count-number"><?php echo $categoryNewslettersCount; ?></div>
                                        <div class="count-label">Newsletter<?php echo $categoryNewslettersCount !== 1 ? 's' : ''; ?></div>
                                        <div style="margin-top: 15px; color: #3498db; font-size: 0.8rem;">
                                            <i class="fas fa-mouse-pointer me-1"></i> Click to view all
                                        </div>
                                    </div>
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
        // Store all category newsletters data for popup
        const categoryNewslettersData = <?php
            $categoryData = [];
            foreach ($categories as $category):
                // Fetch ALL newsletters for this category
                $categoryNewsletters = [];
                try {
                    $stmt = $pdo->prepare("
                        SELECT n.* 
                        FROM tab_newsletters n
                        INNER JOIN con_newsletter_categories c ON n.newsletter_ID = c.newsletter_ID
                        WHERE c.category_ID = ?
                        ORDER BY n.creation_date DESC
                    ");
                    $stmt->execute([$category['category_ID']]);
                    $categoryNewsletters = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    // Silent fail
                }
                $categoryData[$category['category_ID']] = $categoryNewsletters;
            endforeach;
            echo json_encode($categoryData);
        ?>;
        
        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Create popup in parent window
        function createPopupInParent() {
            if (!window.parent || window.parent === window) return null;
            
            const parentDoc = window.parent.document;
            
            // Check if popup already exists
            let popup = parentDoc.getElementById('categoryPopupOverlay');
            if (popup) return popup;
            
            // Create popup HTML
            const popupHTML = `
                <div class="category-popup-overlay" id="categoryPopupOverlay" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.85);
                    backdrop-filter: blur(10px);
                    z-index: 99999;
                    display: none;
                    align-items: center;
                    justify-content: center;
                    animation: fadeIn 0.3s ease;
                ">
                    <div class="category-popup-content" style="
                        background: #1a1a1a;
                        border-radius: 16px;
                        max-width: 900px;
                        width: 90%;
                        max-height: 85vh;
                        overflow: hidden;
                        display: flex;
                        flex-direction: column;
                        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
                        animation: slideUp 0.3s ease;
                    ">
                        <div class="category-popup-header" style="
                            padding: 24px;
                            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            background: linear-gradient(135deg, rgba(52, 152, 219, 0.15), rgba(46, 204, 113, 0.15));
                        ">
                            <h3 id="categoryPopupTitle" style="
                                font-size: 1.5rem;
                                font-weight: 700;
                                color: #ecf0f1;
                                margin: 0;
                            ">
                                <i class="fas fa-layer-group" style="margin-right: 8px;"></i>Category Newsletters
                            </h3>
                            <button onclick="window.closeCategoryPopup()" aria-label="Close" style="
                                background: none;
                                border: none;
                                color: #ecf0f1;
                                font-size: 1.5rem;
                                cursor: pointer;
                                padding: 8px;
                                border-radius: 50%;
                                transition: all 0.2s ease;
                                width: 40px;
                                height: 40px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            " onmouseover="this.style.background='rgba(231, 76, 60, 0.2)'; this.style.color='#e74c3c'; this.style.transform='rotate(90deg)';" onmouseout="this.style.background='none'; this.style.color='#ecf0f1'; this.style.transform='rotate(0deg)';">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="categoryPopupBody" style="
                            padding: 24px;
                            overflow-y: auto;
                            flex: 1;
                        "></div>
                    </div>
                </div>
            `;
            
            // Add animations CSS to parent
            if (!parentDoc.getElementById('categoryPopupStyles')) {
                const style = parentDoc.createElement('style');
                style.id = 'categoryPopupStyles';
                style.textContent = `
                    @keyframes fadeIn {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }
                    @keyframes slideUp {
                        from { opacity: 0; transform: translateY(30px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                    .category-popup-overlay.active {
                        display: flex !important;
                    }
                    #categoryPopupBody::-webkit-scrollbar {
                        width: 8px;
                    }
                    #categoryPopupBody::-webkit-scrollbar-track {
                        background: rgba(255, 255, 255, 0.05);
                        border-radius: 4px;
                    }
                    #categoryPopupBody::-webkit-scrollbar-thumb {
                        background: rgba(52, 152, 219, 0.5);
                        border-radius: 4px;
                    }
                    #categoryPopupBody::-webkit-scrollbar-thumb:hover {
                        background: rgba(52, 152, 219, 0.7);
                    }
                    .popup-newsletter-item {
                        padding: 20px;
                        margin-bottom: 16px;
                        background: rgba(255, 255, 255, 0.03);
                        border-radius: 12px;
                        border-left: 4px solid #3498db;
                        transition: all 0.3s ease;
                        cursor: pointer;
                    }
                    .popup-newsletter-item:hover {
                        background: rgba(255, 255, 255, 0.06);
                        border-left-color: #2ecc71;
                        transform: translateX(8px);
                        box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
                    }
                    .popup-newsletter-item:last-child {
                        margin-bottom: 0;
                    }
                `;
                parentDoc.head.appendChild(style);
            }
            
            // Insert popup into parent body
            const div = parentDoc.createElement('div');
            div.innerHTML = popupHTML;
            parentDoc.body.appendChild(div.firstElementChild);
            
            popup = parentDoc.getElementById('categoryPopupOverlay');
            
            // Add click outside to close
            popup.addEventListener('click', function(e) {
                if (e.target === popup) {
                    window.parent.closeCategoryPopup();
                }
            });
            
            // Add ESC key to close
            parentDoc.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && popup.classList.contains('active')) {
                    window.parent.closeCategoryPopup();
                }
            });
            
            return popup;
        }
        
        // Open category popup in parent window
        function openCategoryPopup(categoryId, categoryName) {
            try {
                const popup = createPopupInParent();
                if (!popup) return;
                
                const parentDoc = window.parent.document;
                const title = parentDoc.getElementById('categoryPopupTitle');
                const body = parentDoc.getElementById('categoryPopupBody');
                
                title.innerHTML = '<i class="fas fa-layer-group" style="margin-right: 8px;"></i>' + escapeHtml(categoryName);
                
                const newsletters = categoryNewslettersData[categoryId] || [];
                
                if (newsletters.length === 0) {
                    body.innerHTML = `
                        <div style="text-align: center; padding: 60px 20px; color: #95a5a6;">
                            <i class="fas fa-inbox" style="font-size: 4rem; margin-bottom: 20px; color: #7f8c8d;"></i>
                            <h4>No Newsletters Yet</h4>
                            <p>This category doesn't have any newsletters at the moment.</p>
                        </div>
                    `;
                } else {
                    let html = '';
                    newsletters.forEach(newsletter => {
                        const content = newsletter.content || 'Newsletter description coming soon';
                        const strippedContent = content.replace(/<[^>]*>/g, '');
                        const description = strippedContent.substring(0, 200);
                        const date = new Date(newsletter.creation_date);
                        const formattedDate = date.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });
                        
                        html += `
                            <div class="popup-newsletter-item" onclick="window.frames[0].openNewsletter(${newsletter.newsletter_ID})">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                                    <h4 style="font-size: 1.15rem; font-weight: 600; color: #ecf0f1; margin: 0; flex: 1; line-height: 1.3;">${escapeHtml(newsletter.title)}</h4>
                                </div>
                                <p style="font-size: 0.9rem; color: #95a5a6; margin-bottom: 12px; line-height: 1.5;">${escapeHtml(description)}${strippedContent.length > 200 ? '...' : ''}</p>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid rgba(255, 255, 255, 0.05);">
                                    <span style="font-size: 0.85rem; color: #7f8c8d;">
                                        <i class="far fa-calendar-alt" style="margin-right: 8px;"></i>${formattedDate}
                                    </span>
                                    <span style="color: #3498db; font-size: 0.9rem; font-weight: 500;">
                                        Click to read <i class="fas fa-arrow-right" style="margin-left: 4px;"></i>
                                    </span>
                                </div>
                            </div>
                        `;
                    });
                    body.innerHTML = html;
                }
                
                popup.classList.add('active');
                parentDoc.body.style.overflow = 'hidden';
            } catch (e) {
                console.error('Could not open popup in parent window:', e);
            }
        }
        
        // Close category popup (exposed to parent window)
        window.parent.closeCategoryPopup = function() {
            try {
                const parentDoc = window.parent.document;
                const popup = parentDoc.getElementById('categoryPopupOverlay');
                if (popup) {
                    popup.classList.remove('active');
                    parentDoc.body.style.overflow = '';
                }
            } catch (e) {
                console.error('Could not close popup:', e);
            }
        };
        
        // Open newsletter from popup
        function openNewsletter(newsletterId) {
            if (parent.window && parent.window.popupSystem) {
                parent.window.popupSystem.newsPopup(newsletterId);
                // Close category popup after opening newsletter
                if (window.parent.closeCategoryPopup) {
                    window.parent.closeCategoryPopup();
                }
            }
        }
        
        // Initialize popup system with newsletter data
        if (parent.window && parent.window.popupSystem) {
            const newsletters = <?php
                // Collect all newsletters to initialize popup system
                $allNewsletters = [];
                
                // Add user digest
                if (isset($userDigest) && $userDigest) {
                    $allNewsletters[$userDigest['newsletter_ID']] = [
                        'id' => $userDigest['newsletter_ID'],
                        'title' => $userDigest['title'],
                        'content' => $userDigest['content'],
                        'creation_date' => $userDigest['creation_date'],
                        'status' => $userDigest['status'] ?? 'published'
                    ];
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
                            $allNewsletters[$catNewsletter['newsletter_ID']] = [
                                'id' => $catNewsletter['newsletter_ID'],
                                'title' => $catNewsletter['title'],
                                'content' => $catNewsletter['content'],
                                'creation_date' => $catNewsletter['creation_date'],
                                'status' => $catNewsletter['status'] ?? 'published'
                            ];
                        }
                    } catch (PDOException $e) {}
                }
                
                // Add popular newsletters
                foreach ($popularNewsletters as $newsletter) {
                    $allNewsletters[$newsletter['newsletter_ID']] = [
                        'id' => $newsletter['newsletter_ID'],
                        'title' => $newsletter['title'],
                        'content' => $newsletter['content'],
                        'creation_date' => $newsletter['creation_date'],
                        'status' => $newsletter['status'] ?? 'published'
                    ];
                }
                
                echo json_encode($allNewsletters);
            ?>;
            
            parent.window.popupSystem.initData(newsletters, {}, {});
        }
    </script>
    <script src="scripts/theme.js"></script>
    <script src="scripts/homeView.js"></script>
    <!-- Favorites System -->
    <script src="scripts/favorites.js"></script>
</body>
</html>