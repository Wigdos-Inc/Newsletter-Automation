<?php  require_once '../scripts/php/search.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Newswire AI</title>

    <base href="../">
    <link rel="stylesheet" href="bootstrap-5.3.6-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styling/main.css">
    <link rel="stylesheet" href="styling/search.css">
</head>
<body>
    
    <div class="container py-5">
        <!-- Search Header -->
        <div class="search-header text-center">
            <h1 class="search-title">Search Results</h1>
            <p class="search-info">
                <?php 
                if ($results && $rAmount > 0) {
                    echo "Found <span class='search-query-highlight'>$rAmount</span> result" . ($rAmount != 1 ? 's' : '') . " for \"<span class='search-query-highlight'>" . htmlspecialchars($query) . "</span>\"";
                } else if (!$query) {
                    echo 'No search query provided.';
                } else {
                    echo 'No results found for "' . htmlspecialchars($query) . '"';
                }
                ?>
            </p>
        </div>

        <?php if ($results && $rAmount > 0): ?>

            <!-- Newsletter Results -->
            <?php if (!empty($results['newsletters'])): ?>
                <div class="results-section">
                    <div class="section-header">
                        <i class="fas fa-newspaper"></i>
                        <h2 class="section-title">Newsletters</h2>
                        <span class="section-count"><?= count($results['newsletters']) ?></span>
                    </div>

                    <?php foreach ($results['newsletters'] as $result): 
                        $newsletter = $newsletters[$result[0]];
                        $newsletterID = $result[0];
                    ?>
                        <div class="result-card newsletter-card" onclick="openNewsletterPopup(<?= $newsletterID ?>)">
                            <div class="card-header">
                                <h3 class="newsletter-title"><?= htmlspecialchars($newsletter['title']) ?></h3>
                                <div class="newsletter-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    <?= date('M j, Y', strtotime($newsletter['creation_date'])) ?>
                                </div>
                            </div>
                            <p class="newsletter-content">
                                <?= htmlspecialchars(truncateText($newsletter['content'], 250)) ?>
                            </p>
                            <div class="newsletter-footer">
                                <span class="read-more">
                                    Read more <i class="fas fa-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Category Results -->
            <?php if (!empty($results['categories'])): ?>
                <div class="results-section">
                    <div class="section-header">
                        <i class="fas fa-th-large"></i>
                        <h2 class="section-title">Categories</h2>
                        <span class="section-count"><?= count($results['categories']) ?></span>
                    </div>

                    <?php foreach ($results['categories'] as $result): 
                        $category = $categories[$result[0]];
                        $categoryID = $result[0];
                        $newsletterCount = count($category['newsletters']);
                    ?>
                        <div class="result-card category-card" onclick="openCategoryPopup(<?= $categoryID ?>)">
                            <div class="card-header">
                                <div class="category-icon">
                                    <i class="fas fa-folder"></i>
                                </div>
                                <div class="category-info">
                                    <h3 class="category-name"><?= htmlspecialchars($category['name']) ?></h3>
                                    <div class="category-stats">
                                        <div class="stat-item">
                                            <i class="fas fa-newspaper"></i>
                                            <span class="stat-value"><?= $newsletterCount ?></span> Newsletter<?= $newsletterCount != 1 ? 's' : '' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Creator Results -->
            <?php if (!empty($results['users'])): ?>
                <div class="results-section">
                    <div class="section-header">
                        <i class="fas fa-users"></i>
                        <h2 class="section-title">Creators</h2>
                        <span class="section-count"><?= count($results['users']) ?></span>
                    </div>

                    <?php foreach ($results['users'] as $result): 
                        $user = $users[$result[0]];
                        $userID = $result[0];
                        
                        // Only show admin and owner roles
                        if (!in_array(strtolower($user['role']), ['admin', 'owner'])) continue;
                        
                        $createdCount = count($user['c_newsletters']);
                        $contributedCount = 0;
                        // Count newsletters where user is a contributor but not creator
                        foreach ($newsletters as $nID => $nData) {
                            if (in_array($userID, $nData['contributors']) && $nData['creator_ID'] != $userID) {
                                $contributedCount++;
                            }
                        }
                    ?>
                        <div class="result-card creator-card" onclick="openCreatorPopup(<?= $userID ?>)">
                            <div class="card-header">
                                <div class="creator-avatar">
                                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                </div>
                                <div class="creator-info">
                                    <h3 class="creator-name">
                                        <?= htmlspecialchars($user['username']) ?>
                                        <span class="creator-role <?= strtolower($user['role']) ?>">
                                            <?= htmlspecialchars($user['role']) ?>
                                        </span>
                                    </h3>
                                    <div class="creator-joined">
                                        <i class="fas fa-calendar-alt"></i>
                                        Joined <?= date('M j, Y', strtotime($user['creation_date'])) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="creator-stats">
                                <div class="creator-stat">
                                    <span class="creator-stat-label">Created</span>
                                    <span class="creator-stat-value">
                                        <i class="fas fa-newspaper"></i>
                                        <?= $createdCount ?>
                                    </span>
                                </div>
                                <div class="creator-stat">
                                    <span class="creator-stat-label">Contributed To</span>
                                    <span class="creator-stat-value">
                                        <i class="fas fa-hands-helping"></i>
                                        <?= $contributedCount ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-results">
                <div class="empty-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h2 class="empty-title">No results found</h2>
                <p class="empty-text">Try adjusting your search query or browse our categories</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Initialize data in parent window's popup system
        if (window.parent && window.parent.popupSystem) {
            window.parent.popupSystem.initData(
                <?= json_encode($newsletters) ?>,
                <?= json_encode($categories) ?>,
                <?= json_encode($users) ?>
            );
        }

        // Reference parent window's popup functions
        function openNewsletterPopup(id) {
            if (window.parent && window.parent.openNewsletterPopup) {
                window.parent.openNewsletterPopup(id);
            }
        }

        function openCategoryPopup(id) {
            if (window.parent && window.parent.openCategoryPopup) {
                window.parent.openCategoryPopup(id);
            }
        }

        function openCreatorPopup(id) {
            if (window.parent && window.parent.openCreatorPopup) {
                window.parent.openCreatorPopup(id);
            }
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
</body>
</html>