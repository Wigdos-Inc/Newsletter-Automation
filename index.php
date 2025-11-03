<?php require_once 'scripts/db/data.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="bootstrap-5.3.6-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="styling/main.css">
  <link rel="stylesheet" href="styling/index.css">
  <link rel="shortcut icon" href="media/images/icons/favicon.svg" type="image/x-icon">
  <title>Newswire AI</title>
</head>
<body>

  <!-- Header -->
  <header class="custom-header">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center">

        <!-- Left Section with Hamburger and Logo -->
        <div class="d-flex align-items-center gap-3">
          <!-- Hamburger Menu Button -->
          <button class="sidebar-open-btn" id="sidebarOpenBtn">
            <span></span>
            <span></span>
            <span></span>
          </button>

          <!-- Logo -->
          <div class="logo-section">
            <img src="media/images/icons/favicon.svg" alt="Logo" class="header-logo">
            <span class="logo-text d-none d-md-inline ms-2">Newswire AI</span>
          </div>
        </div>
        
        <!-- Desktop Search Bar (centered) -->
        <div class="search-wrapper desktop-search d-none d-md-flex">
          <input type="text" class="search-input" placeholder="Search...">
          <button class="search-btn">
            <i class="fas fa-search"></i>
          </button>
        </div>

        <!-- Right Section -->
        <div class="d-flex align-items-center gap-3">
          <!-- Inbox Dropdown -->
          <div class="position-relative">
            <div class="inbox-icon" id="inboxIcon">
              <i class="fas fa-inbox"></i>
              <?php if (isset($inboxAmount) && $inboxAmount > 0): ?>
                <span class="inbox-badge"><?php echo $inboxAmount; ?></span>
              <?php endif; ?>
            </div>
            <div class="inbox-dropdown" id="inboxDropdown">
              <div class="inbox-header">
                <h6 class="mb-0">Inbox</h6>
                <span class="badge bg-primary"><?php echo isset($inboxAmount) ? $inboxAmount : 0; ?> unread</span>
              </div>
              <div class="inbox-items">
                <?php if (isset($userID) && $userID): ?>
                  <?php
                  $stmt = $pdo->prepare("SELECT * FROM tab_inbox WHERE user_ID = ? AND status = 'unread' ORDER BY created_at DESC LIMIT 5");
                  $stmt->execute([$userID]);
                  $inboxItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  
                  if (count($inboxItems) > 0):
                    foreach ($inboxItems as $item):
                  ?>
                    <a href="#" class="inbox-item" data-inbox-id="<?php echo $item['inbox_ID']; ?>">
                      <div class="inbox-item-content">
                        <strong><?php echo htmlspecialchars($item['title'] ?? 'Notification'); ?></strong>
                        <p class="mb-0"><?php echo htmlspecialchars(substr($item['message'] ?? '', 0, 50)); ?>...</p>
                        <small class="text-muted"><?php echo date('M j, Y', strtotime($item['created_at'])); ?></small>
                      </div>
                    </a>
                  <?php 
                    endforeach;
                  else:
                  ?>
                    <div class="inbox-empty">
                      <i class="fas fa-inbox fa-2x mb-2"></i>
                      <p class="mb-0">No new messages</p>
                    </div>
                  <?php endif; ?>
                <?php else: ?>
                  <div class="inbox-empty">
                    <i class="fas fa-user-lock fa-2x mb-2"></i>
                    <p class="mb-0">Please login to view inbox</p>
                  </div>
                <?php endif; ?>
              </div>
              <a href="#" class="inbox-footer" data-page="pages/inbox.php">
                View All Messages
              </a>
            </div>
          </div>

          <!-- Profile Dropdown -->
          <div class="position-relative">
            <div class="profile-icon" id="profileIcon">
              <i class="fas fa-user"></i>
            </div>
            <div class="profile-dropdown" id="profileDropdown">
              <a href="#" data-page="pages/newsletters.php"><i class="fas fa-user me-2"></i> Profile</a>
              <a href="#" data-page="pages/newsletters.php"><i class="fas fa-sign-in-alt me-2"></i> Login</a>
              <a href="#" data-page="pages/newsletters.php"><i class="fas fa-user-plus me-2"></i> Register</a>
              <div class="dropdown-divider"></div>
              <div class="theme-toggle-container">
                <i class="fas fa-moon me-2"></i>
                <span>Dark Mode</span>
                <label class="theme-toggle-switch">
                  <input type="checkbox" id="themeToggle" checked>
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>
          </div>

          <!-- Mobile Hamburger -->
          <button class="btn btn-outline-light d-md-none" id="menuToggle">
            <i class="fas fa-bars"></i>
          </button>
        </div>

      </div>

      <!-- Mobile Search Bar -->
      <div class="mobile-search" id="mobileSearch" style="display: none;">
        <div class="search-wrapper mt-3">
          <input type="text" class="search-input" placeholder="Search...">
          <button class="search-btn">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
    </div>
  </header>

  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <h5 class="sidebar-title">Navigation</h5>
      <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-times"></i>
      </button>
    </div>
    
    <nav class="sidebar-nav">
      <a href="#" class="sidebar-link active" data-page="pages/newsletters.php">
        <i class="fas fa-home"></i>
        <span>Home</span>
      </a>
      <a href="#" class="sidebar-link" data-page="pages/categories.php">
        <i class="fas fa-th-large"></i>
        <span>Categories</span>
      </a>
      <a href="#" class="sidebar-link" data-page="pages/newsletters.php">
        <i class="fas fa-newspaper"></i>
        <span>Newsletters</span>
      </a>
      <a href="#" class="sidebar-link" data-page="pages/searchResults.php">
        <i class="fas fa-search"></i>
        <span>Search</span>
      </a>
      
      <div class="sidebar-divider"></div>
      
      <a href="#" class="sidebar-link" data-page="pages/inbox.php">
        <i class="fas fa-inbox"></i>
        <span>Inbox</span>
        <?php if (isset($inboxAmount) && $inboxAmount > 0): ?>
          <span class="sidebar-badge"><?php echo $inboxAmount; ?></span>
        <?php endif; ?>
      </a>
      <a href="#" class="sidebar-link" data-page="pages/profile_view.php">
        <i class="fas fa-user"></i>
        <span>Profile</span>
      </a>
      <a href="#" class="sidebar-link" data-page="pages/profile_view.php">
        <i class="fas fa-cog"></i>
        <span>Settings</span>
      </a>
      
      <div class="sidebar-divider"></div>
      
      <a href="#" class="sidebar-link" data-page="pages/login_view.php">
        <i class="fas fa-sign-in-alt"></i>
        <span>Login</span>
      </a>
      <a href="#" class="sidebar-link" data-page="pages/register_view.php">
        <i class="fas fa-user-plus"></i>
        <span>Register</span>
      </a>
    </nav>

    <div class="sidebar-footer">
      <p class="text-muted small mb-0">© 2025 Newswire AI</p>
    </div>
  </aside>

  <!-- Sidebar Overlay -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- Main Content -->
  <main class="flex-grow-1 py-5 main-content">
    <div class="container">
      <iframe id="pageContent" src="pages/newsletters.php" title="Page content" style="width:100%; min-height:65vh; border:0;" loading="lazy"></iframe>
    </div>
  </main>

  <!-- Footer -->
  <footer class="footer mt-auto">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-4 footer-section">
          <div class="footer-logo">Newswire AI</div>
          <p class="footer-about">We provide the best service and innovative solutions that make a difference in people's lives.</p>
          <div class="social-links">
            <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
          </div>
        </div>

        <div class="col-lg-2 col-md-6 footer-section">
          <h5>Quick Links</h5>
          <ul class="footer-links">
            <li><a href="#">Pricing</a></li>
            <li><a href="#">Contact</a></li>
          </ul>
        </div>

        <div class="col-lg-4 footer-section">
          <h5>Newswire AI</h5>
          <p class="footer-about">Subscribe for updates, news, and exclusive offers.</p>
          <div class="footer-newsletter">
            <input type="email" placeholder="Enter your email">
            <button class="newsletter-btn"><i class="fas fa-paper-plane"></i></button>
          </div>
        </div>
      </div>

      <div class="footer-bottom text-center mt-5 pt-3">
        <ul class="footer-bottom-links d-flex justify-content-center gap-3 mb-2">
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Terms of Service</a></li>
          <li><a href="#">Cookie Policy</a></li>
        </ul>
        <p>© 2025 Newswire AI.</p>
      </div>
    </div>
  </footer>

  <script src="bootstrap-5.3.6-dist/js/bootstrap.bundle.min.js"></script>
  <script src="scripts/main.js"></script>
  <script>
    // Inbox Dropdown Toggle
    const inboxIcon = document.getElementById('inboxIcon');
    const inboxDropdown = document.getElementById('inboxDropdown');

    if (inboxIcon && inboxDropdown) {
      inboxIcon.addEventListener('click', (e) => {
        e.stopPropagation();
        inboxDropdown.classList.toggle('show');
        // Close profile dropdown if open
        if (profileDropdown) profileDropdown.classList.remove('show');
      });
    }

    // Profile Dropdown Toggle
    const profileIcon = document.getElementById('profileIcon');
    const profileDropdown = document.getElementById('profileDropdown');

    if (profileIcon && profileDropdown) {
      profileIcon.addEventListener('click', (e) => {
        e.stopPropagation();
        profileDropdown.classList.toggle('show');
        // Close inbox dropdown if open
        if (inboxDropdown) inboxDropdown.classList.remove('show');
      });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
      if (inboxIcon && inboxDropdown && 
          !inboxIcon.contains(e.target) && !inboxDropdown.contains(e.target)) {
        inboxDropdown.classList.remove('show');
      }
      if (profileIcon && profileDropdown &&
          !profileIcon.contains(e.target) && !profileDropdown.contains(e.target)) {
        profileDropdown.classList.remove('show');
      }
    });

    // Mobile menu toggle
    const menuToggle = document.getElementById('menuToggle');
    const mobileSearch = document.getElementById('mobileSearch');
    
    if (menuToggle && mobileSearch) {
      menuToggle.addEventListener('click', () => {
        if (mobileSearch.style.display === 'none' || mobileSearch.style.display === '') {
          mobileSearch.style.display = 'block';
        } else {
          mobileSearch.style.display = 'none';
        }
      });
    }

    // Sidebar functionality
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOpenBtn = document.getElementById('sidebarOpenBtn');
    const sidebarLinks = document.querySelectorAll('.sidebar-link');

    // Toggle sidebar - pushes content instead of overlaying
    function toggleSidebar() {
      sidebar.classList.toggle('active');
      document.body.classList.toggle('sidebar-open');
    }

    if (sidebarOpenBtn) {
      sidebarOpenBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleSidebar();
      });
    }

    if (sidebarToggle) {
      sidebarToggle.addEventListener('click', toggleSidebar);
    }

    // Handle sidebar link clicks - stays open
    sidebarLinks.forEach(link => {
      link.addEventListener('click', function(e) {
        // Remove active class from all links
        sidebarLinks.forEach(l => l.classList.remove('active'));
        // Add active class to clicked link
        this.classList.add('active');
        // Sidebar stays open - don't close it
      });
    });

    // Theme Toggle Functionality
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.querySelector('.theme-toggle-container i');
    const themeText = document.querySelector('.theme-toggle-container span');

    // Check for saved theme preference or default to dark mode
    const currentTheme = localStorage.getItem('theme') || 'dark';
    if (currentTheme === 'light') {
      document.body.classList.add('light-mode');
      themeToggle.checked = false;
      themeIcon.classList.remove('fa-moon');
      themeIcon.classList.add('fa-sun');
      themeText.textContent = 'Light Mode';
    }

    // Toggle theme
    themeToggle.addEventListener('change', function() {
      if (this.checked) {
        // Switch to dark mode
        document.body.classList.remove('light-mode');
        themeIcon.classList.remove('fa-sun');
        themeIcon.classList.add('fa-moon');
        themeText.textContent = 'Dark Mode';
        localStorage.setItem('theme', 'dark');
      } else {
        // Switch to light mode
        document.body.classList.add('light-mode');
        themeIcon.classList.remove('fa-moon');
        themeIcon.classList.add('fa-sun');
        themeText.textContent = 'Light Mode';
        localStorage.setItem('theme', 'light');
      }
    });
  </script>
</body>
</html>
