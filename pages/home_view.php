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
    <style>
        /* Remove overflow restrictions */
        html, body {
            overflow: visible !important;
            overflow-x: visible !important;
            overflow-y: visible !important;
        }
        
        .container {
            overflow: visible !important;
        }
        
        /* Ensure dropdown menu can escape bounds */
        .newest-category-section {
            overflow: visible !important;
        }
        
        .dropdown {
            position: static !important;
        }
        
        .dropdown-menu {
            position: absolute !important;
        }
        
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
        
        /* Category View More Styling */
        .category-hidden {
            display: none;
        }
        
        #viewMoreCategoriesBtn {
            transition: all 0.3s ease;
            padding: 8px 16px;
            font-weight: 500;
            border-radius: 8px;
            font-size: 0.875rem;
            max-width: 280px;
        }
        
        #viewMoreCategoriesBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        /* Dropdown menu styling - consistent with site style */
        .category-dropdown-menu {
            max-height: 400px;
            overflow-y: auto;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
            border-radius: 12px;
            padding: 8px;
            min-width: 280px;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .category-dropdown-item {
            padding: 10px 16px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            border-radius: 8px;
            color: #e0e0e0;
            text-decoration: none;
        }
        
        .category-dropdown-item:hover {
            background-color: rgba(52, 152, 219, 0.2);
            color: #3498db;
            transform: translateX(4px);
        }
        
        .category-dropdown-item span {
            display: flex;
            align-items: center;
        }
        
        .favorite-btn-icon-small {
            background: none;
            border: none;
            color: #e74c3c;
            cursor: pointer;
            padding: 4px 8px;
            transition: all 0.2s ease;
            font-size: 0.85rem;
            border-radius: 50%;
        }
        
        .favorite-btn-icon-small:hover {
            color: #c0392b;
            background-color: rgba(231, 76, 60, 0.1);
            transform: scale(1.1);
        }
        
        .favorite-btn-icon-small.active i {
            color: #e74c3c;
            font-weight: 900;
        }
        
        /* Light mode dropdown */
        body.light-mode .category-dropdown-menu {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            backdrop-filter: none;
        }
        
        body.light-mode .category-dropdown-item {
            color: #2c3e50;
        }
        
        body.light-mode .category-dropdown-item:hover {
            background-color: rgba(52, 152, 219, 0.08);
            color: #2980b9;
        }
        
        /* Highlight flash animation */
        @keyframes highlightFlash {
            0%, 100% {
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }
            50% {
                box-shadow: 0 0 20px rgba(52, 152, 219, 0.6);
                transform: scale(1.02);
            }
        }
        
        .highlight-flash .category-card {
            animation: highlightFlash 1s ease-in-out;
        }
        
        .category-dropdown-wrapper {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-end;
        }
        
        /* Category card clickable styling */
        .category-card {
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 180px;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
        }
        
        .category-count-display {
            text-align: center;
            padding: 20px 15px;
        }
        
        .count-number {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #3498db, #2ecc71);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 8px;
        }
        
        .count-label {
            font-size: 0.95rem;
            color: #95a5a6;
            font-weight: 500;
        }
        
        .category-icon-display {
            font-size: 1.8rem;
            color: #3498db;
            margin-bottom: 10px;
        }
        
        body.light-mode .count-label {
            color: #7f8c8d;
        }
        
        body.light-mode .category-icon-display {
            color: #2980b9;
        }
        
        /* Notification animations */
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
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
        
        // ========================================
        // AUTO-REFRESH FEATURED NEWSLETTER SYSTEM
        // ========================================
        
        // Track current featured newsletter ID and auto-refresh interval
        let autoRefreshInterval = null;
        let currentFeaturedId = <?php echo $featuredNewsletter ? $featuredNewsletter['newsletter_ID'] : 'null'; ?>;
        
        /**
         * Check for newest newsletter via AJAX
         */
        function checkForNewestNewsletter() {
            console.log('Checking for newest newsletter... Current ID:', currentFeaturedId);
            
            fetch('pages/home_view.php?check_newest=1')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('AJAX response:', data);
                    
                    if (data.has_new && data.newsletter) {
                        const newId = parseInt(data.newsletter.newsletter_ID);
                        console.log('Comparing - New ID:', newId, 'Current ID:', currentFeaturedId, 'Type check:', typeof newId, typeof currentFeaturedId);
                        
                        // Only update if we have a new newsletter ID
                        // Convert both to numbers for proper comparison
                        if (currentFeaturedId === null || newId != currentFeaturedId) {
                            console.log('✓ New newsletter detected! Updating...');
                            updateFeaturedNewsletter(data.newsletter);
                            currentFeaturedId = newId;
                            showNewContentNotification();
                        } else {
                            console.log('✗ Newsletter is already the newest one');
                        }
                    } else {
                        console.log('No newsletter data in response');
                    }
                })
                .catch(error => {
                    console.error('Error checking for newest newsletter:', error);
                });
        }
        
        /**
         * Update the featured newsletter section with new content
         */
        function updateFeaturedNewsletter(newsletter) {
            const sectionEl = document.getElementById('featuredSection');
            const titleEl = document.getElementById('featuredTitle');
            const descEl = document.getElementById('featuredDescription');
            const dateEl = document.getElementById('featuredDate');
            const btnEl = document.getElementById('featuredReadBtn');
            
            if (!sectionEl || !titleEl || !descEl || !dateEl || !btnEl) {
                console.error('Featured section elements not found');
                return;
            }
            
            // Add highlight animation
            sectionEl.style.animation = 'highlightFlash 1s ease-in-out';
            setTimeout(() => {
                sectionEl.style.animation = '';
            }, 1000);
            
            // Update content
            titleEl.textContent = newsletter.title;
            
            // Strip HTML tags and truncate description
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = newsletter.content || 'Latest newsletter content.';
            const strippedContent = tempDiv.textContent || tempDiv.innerText || '';
            descEl.textContent = strippedContent.substring(0, 150) + '...';
            
            // Format and update date
            const date = new Date(newsletter.creation_date);
            const formattedDate = date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            dateEl.innerHTML = '<i class="far fa-calendar-alt me-1"></i>' + formattedDate;
            
            // Update read button onclick
            btnEl.setAttribute('onclick', `if(parent.window.popupSystem) parent.window.popupSystem.newsPopup(${newsletter.newsletter_ID})`);
            
            console.log('Featured newsletter updated to:', newsletter.title);
        }
        
        /**
         * Show notification when new content is available
         */
        function showNewContentNotification() {
            // Check if notification already exists
            if (document.getElementById('newContentNotification')) {
                return;
            }
            
            const notification = document.createElement('div');
            notification.id = 'newContentNotification';
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, #2ecc71, #27ae60);
                color: white;
                padding: 16px 24px;
                border-radius: 12px;
                box-shadow: 0 8px 24px rgba(46, 204, 113, 0.4);
                z-index: 10000;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 12px;
                animation: slideInRight 0.5s ease;
                max-width: 350px;
            `;
            
            notification.innerHTML = `
                <i class="fas fa-check-circle" style="font-size: 1.3rem;"></i>
                <div style="flex: 1;">
                    <div style="font-weight: 600; margin-bottom: 4px;">New Newsletter Available!</div>
                    <div style="font-size: 0.85rem; opacity: 0.95;">The featured section has been updated.</div>
                </div>
                <button onclick="this.parentElement.remove()" style="
                    background: rgba(255, 255, 255, 0.2);
                    border: none;
                    color: white;
                    width: 24px;
                    height: 24px;
                    border-radius: 50%;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 0.9rem;
                    transition: all 0.2s ease;
                " onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto-dismiss after 8 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.5s ease';
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 500);
            }, 8000);
        }
        
        /**
         * Start auto-refresh polling
         */
        function startAutoRefresh() {
            // Check immediately on load
            checkForNewestNewsletter();
            
            // Then check every 30 seconds
            autoRefreshInterval = setInterval(checkForNewestNewsletter, 30000);
            
            console.log('Auto-refresh started - checking every 30 seconds');
        }
        
        /**
         * Stop auto-refresh polling
         */
        function stopAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
                console.log('Auto-refresh stopped');
            }
        }
        
        // Start auto-refresh when page loads
        startAutoRefresh();
        
        // Pause auto-refresh when page is hidden, resume when visible
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopAutoRefresh();
            } else {
                startAutoRefresh();
            }
        });
        
        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            stopAutoRefresh();
        });
        
        // ========================================
        // END AUTO-REFRESH SYSTEM
        // ========================================
        
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
        
        // Scroll to a specific category card when selected from dropdown
        function scrollToCategoryCard(index) {
            const categoryCard = document.getElementById('category-card-' + index);
            if (categoryCard) {
                // Show the hidden category
                categoryCard.style.display = 'block';
                categoryCard.classList.remove('category-hidden');
                
                // Smooth scroll to the category
                categoryCard.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                
                // Add highlight effect
                categoryCard.classList.add('highlight-flash');
                setTimeout(() => {
                    categoryCard.classList.remove('highlight-flash');
                }, 2000);
            }
        }
        
        // Refresh button with animation
        const refreshBtn = document.getElementById('refreshHomeBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const icon = this.querySelector('i');
                
                // Add spinning animation
                icon.classList.add('fa-spin');
                this.disabled = true;
                
                // Check for new newsletter instead of reloading
                checkForNewestNewsletter();
                
                // Re-enable button after check
                setTimeout(() => {
                    icon.classList.remove('fa-spin');
                    this.disabled = false;
                }, 1000);
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
        
        // Request parent window to remove overflow restrictions on iframe
        if (window.parent !== window) {
            // Send message to parent to adjust iframe styles
            window.parent.postMessage({
                type: 'removeIframeOverflow'
            }, '*');
            
            // Also try direct manipulation if accessible
            try {
                const iframe = window.parent.document.querySelector('iframe');
                if (iframe) {
                    iframe.style.overflow = 'visible';
                    iframe.parentElement.style.overflow = 'visible';
                }
            } catch (e) {
                // Cross-origin restriction, message should handle it
                console.log('Using postMessage for overflow control');
            }
        }
    </script>
    
    <!-- Favorites System -->
    <script src="scripts/favorites.js"></script>
</body>
</html>