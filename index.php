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
          <input type="text" class="search-input" id="desktopSearchInput" placeholder="Search...">
          <button class="search-btn" id="desktopSearchBtn">
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
                  $stmt = $pdo->prepare("SELECT * FROM tab_inbox WHERE user_ID = ? AND status = 'unread' ORDER BY date DESC LIMIT 5");
                  $stmt->execute([$userID]);
                  $inboxItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  
                  if (count($inboxItems) > 0):
                    foreach ($inboxItems as $item):
                  ?>
                    <a href="#" class="inbox-item" data-message-id="<?php echo $item['message_ID']; ?>">
                      <div class="inbox-item-content">
                        <strong><?php echo htmlspecialchars($item['subject'] ?? 'Notification'); ?></strong>
                        <p class="mb-0"><?php echo htmlspecialchars(substr($item['snippet'] ?? '', 0, 50)); ?>...</p>
                        <small class="text-muted"><?php echo date('M j, Y', strtotime($item['date'])); ?></small>
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
              <a href="#" class="inbox-footer" data-page="pages/inbox_view.php">
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
              <a href="#" data-page="pages/profile_view.php"><i class="fas fa-user me-2"></i> Profile</a>
              <?php if (isset($userID) && $userID): ?>
                <a href="#" onclick="logout(); return false;"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
              <?php else: ?>
                <a href="#" data-page="pages/login_view.php"><i class="fas fa-sign-in-alt me-2"></i> Login</a>
                <a href="#" data-page="pages/register_view.php"><i class="fas fa-user-plus me-2"></i> Register</a>
              <?php endif; ?>
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
          <input type="text" class="search-input" id="mobileSearchInput" placeholder="Search...">
          <button class="search-btn" id="mobileSearchBtn">
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
      <a href="#" class="sidebar-link active" data-page="pages/home_view.php">
        <i class="fas fa-home"></i>
        <span>Home</span>
      </a>
      <a href="#" class="sidebar-link" data-page="pages/category_view.php">
        <i class="fas fa-th-large"></i>
        <span>Categories</span>
      </a>
      <a href="#" class="sidebar-link" data-page="pages/newsletter_view.php">
        <i class="fas fa-newspaper"></i>
        <span>Newsletters</span>
      </a>
      <a href="#" class="sidebar-link" data-page="pages/search_view.php">
        <i class="fas fa-search"></i>
        <span>Search</span>
      </a>
      
      <div class="sidebar-divider"></div>
      
      <a href="#" class="sidebar-link" data-page="pages/inbox_view.php">
        <i class="fas fa-inbox"></i>
        <span>Inbox</span>
        <?php if (isset($inboxAmount) && $inboxAmount > 0): ?>
          <span class="sidebar-badge"><?php echo $inboxAmount; ?></span>
        <?php endif; ?>
      </a>
      <a href="#" class="sidebar-link" data-page="pages/profile_view.php#profile">
        <i class="fas fa-user"></i>
        <span>Profile</span>
      </a>
      <a href="#" class="sidebar-link" data-page="pages/profile_view.php#settings">
        <i class="fas fa-cog"></i>
        <span>Settings</span>
      </a>
      
      <div class="sidebar-divider"></div>
      
      <?php if (isset($userID) && $userID): ?>
        <a href="#" class="sidebar-link" onclick="logout(); return false;">
          <i class="fas fa-sign-out-alt"></i>
          <span>Logout</span>
        </a>
      <?php else: ?>
        <a href="#" class="sidebar-link" data-page="pages/login_view.php">
          <i class="fas fa-sign-in-alt"></i>
          <span>Login</span>
        </a>
        <a href="#" class="sidebar-link" data-page="pages/register_view.php">
          <i class="fas fa-user-plus"></i>
          <span>Register</span>
        </a>
      <?php endif; ?>
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
      <iframe id="pageContent" src="pages/home_view.php" title="Page content" style="width:100%; min-height:65vh; border:0;" loading="lazy"></iframe>
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

  <!-- Welcome Tutorial Overlay -->
  <?php if (isset($_COOKIE['show_tutorial']) && $_COOKIE['show_tutorial']): ?>
  <div id="tutorialOverlay" class="tutorial-overlay">
    <div class="tutorial-container">
      <div class="tutorial-card" id="tutorialCard">
        <button class="tutorial-skip-btn" id="skipTutorial">
          <i class="fas fa-times"></i> Skip Tutorial
        </button>
        
        <!-- Welcome Step -->
        <div class="tutorial-step active" data-step="0">
          <div class="tutorial-icon">
            <i class="fas fa-rocket fa-3x"></i>
          </div>
          <h2 class="tutorial-title">Welcome to Newswire AI! 🎉</h2>
          <p class="tutorial-text">Let's take a quick tour of the key features. This will only take a moment!</p>
          <div class="tutorial-progress">
            <span class="tutorial-step-indicator active"></span>
            <span class="tutorial-step-indicator"></span>
            <span class="tutorial-step-indicator"></span>
            <span class="tutorial-step-indicator"></span>
            <span class="tutorial-step-indicator"></span>
            <span class="tutorial-step-indicator"></span>
          </div>
          <button class="tutorial-next-btn" onclick="nextTutorialStep()">Get Started</button>
        </div>

        <!-- Step 1: Sidebar -->
        <div class="tutorial-step" data-step="1">
          <div class="tutorial-icon">
            <i class="fas fa-bars fa-3x"></i>
          </div>
          <h2 class="tutorial-title">Navigation Sidebar</h2>
          <p class="tutorial-text">Click the <strong>hamburger menu</strong> in the top left to access all pages including Home, Categories, Newsletters, and Search.</p>
          <div class="tutorial-mini-card">
            <i class="fas fa-bars"></i>
            <span>Opens the main navigation menu</span>
          </div>
          <div class="tutorial-progress">
            <span class="tutorial-step-indicator completed"></span>
            <span class="tutorial-step-indicator active"></span>
            <span class="tutorial-step-indicator"></span>
            <span class="tutorial-step-indicator"></span>
            <span class="tutorial-step-indicator"></span>
            <span class="tutorial-step-indicator"></span>
          </div>
          <div class="tutorial-buttons">
            <button class="tutorial-back-btn" onclick="prevTutorialStep()">Back</button>
            <button class="tutorial-next-btn" onclick="nextTutorialStep()">Next</button>
          </div>
        </div>

        <!-- Step 2: Search -->
        <div class="tutorial-step" data-step="2">
          <div class="tutorial-icon">
            <i class="fas fa-search fa-3x"></i>
          </div>
          <h2 class="tutorial-title">Search Bar</h2>
          <p class="tutorial-text">Use the <strong>search bar</strong> in the header to quickly find newsletters, articles, and topics that interest you.</p>
          <div class="tutorial-mini-card">
            <i class="fas fa-search"></i>
            <span>Find content across all newsletters</span>
          </div>
          <div class="tutorial-progress">
            <span class="tutorial-step-indicator completed"></span>
            <span class="tutorial-step-indicator completed"></span>
            <span class="tutorial-step-indicator active"></span>
            <span class="tutorial-step-indicator"></span>
            <span class="tutorial-step-indicator"></span>
            <span class="tutorial-step-indicator"></span>
          </div>
          <div class="tutorial-buttons">
            <button class="tutorial-back-btn" onclick="prevTutorialStep()">Back</button>
            <button class="tutorial-next-btn" onclick="nextTutorialStep()">Next</button>
          </div>
        </div>

        <!-- Step 3: Inbox -->
        <div class="tutorial-step" data-step="3">
          <div class="tutorial-icon">
            <i class="fas fa-inbox fa-3x"></i>
          </div>
          <h2 class="tutorial-title">Inbox</h2>
          <p class="tutorial-text">The <strong>inbox icon</strong> shows your unread messages and notifications. Click it to see recent updates.</p>
          <div class="tutorial-mini-card">
            <i class="fas fa-inbox"></i>
            <span>View notifications and messages</span>
          </div>
          <div class="tutorial-progress">
            <span class="tutorial-step-indicator completed"></span>
            <span class="tutorial-step-indicator completed"></span>
            <span class="tutorial-step-indicator completed"></span>
            <span class="tutorial-step-indicator active"></span>
            <span class="tutorial-step-indicator"></span>
            <span class="tutorial-step-indicator"></span>
          </div>
          <div class="tutorial-buttons">
            <button class="tutorial-back-btn" onclick="prevTutorialStep()">Back</button>
            <button class="tutorial-next-btn" onclick="nextTutorialStep()">Next</button>
          </div>
        </div>

        <!-- Step 4: Profile -->
        <div class="tutorial-step" data-step="4">
          <div class="tutorial-icon">
            <i class="fas fa-user fa-3x"></i>
          </div>
          <h2 class="tutorial-title">Profile Menu</h2>
          <p class="tutorial-text">Click the <strong>profile icon</strong> to access your profile, settings, and theme preferences (Light/Dark mode).</p>
          <div class="tutorial-mini-card">
            <i class="fas fa-user"></i>
            <span>Manage your account and preferences</span>
          </div>
          <div class="tutorial-progress">
            <span class="tutorial-step-indicator completed"></span>
            <span class="tutorial-step-indicator completed"></span>
            <span class="tutorial-step-indicator completed"></span>
            <span class="tutorial-step-indicator completed"></span>
            <span class="tutorial-step-indicator active"></span>
            <span class="tutorial-step-indicator"></span>
          </div>
          <div class="tutorial-buttons">
            <button class="tutorial-back-btn" onclick="prevTutorialStep()">Back</button>
            <button class="tutorial-next-btn" onclick="nextTutorialStep()">Next</button>
          </div>
        </div>

        <!-- Final Step -->
        <div class="tutorial-step" data-step="5">
          <div class="tutorial-icon">
            <i class="fas fa-check-circle fa-3x"></i>
          </div>
          <h2 class="tutorial-title">You're All Set! 🚀</h2>
          <p class="tutorial-text">You're ready to explore Newswire AI. Enjoy discovering personalized news and newsletters!</p>
          <div class="tutorial-mini-card highlight">
            <i class="fas fa-lightbulb"></i>
            <span>Tip: You can always access these features from the navigation bar</span>
          </div>
          <div class="tutorial-progress">
            <span class="tutorial-step-indicator completed"></span>
            <span class="tutorial-step-indicator completed"></span>
            <span class="tutorial-step-indicator completed"></span>
            <span class="tutorial-step-indicator completed"></span>
            <span class="tutorial-step-indicator completed"></span>
            <span class="tutorial-step-indicator active"></span>
          </div>
          <button class="tutorial-finish-btn" onclick="finishTutorial()">Start Exploring</button>
        </div>
      </div>
    </div>
  </div>

  <style>
    .tutorial-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.85);
      backdrop-filter: blur(5px);
      z-index: 9999;
      display: flex;
      align-items: center;
      justify-content: center;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .tutorial-container {
      width: 100%;
      max-width: 600px;
      padding: 20px;
    }

    .tutorial-card {
      background: linear-gradient(135deg, #1a1c20 0%, #2c3e50 100%);
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
      position: relative;
      animation: slideUp 0.4s ease;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .tutorial-skip-btn {
      position: absolute;
      top: 15px;
      right: 15px;
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #ffffff;
      padding: 8px 16px;
      border-radius: 20px;
      cursor: pointer;
      font-size: 0.9rem;
      transition: all 0.3s ease;
    }

    .tutorial-skip-btn:hover {
      background: rgba(231, 76, 60, 0.3);
      border-color: #e74c3c;
    }

    .tutorial-step {
      display: none;
      text-align: center;
      animation: fadeInStep 0.3s ease;
    }

    .tutorial-step.active {
      display: block;
    }

    @keyframes fadeInStep {
      from {
        opacity: 0;
        transform: scale(0.95);
      }
      to {
        opacity: 1;
        transform: scale(1);
      }
    }

    .tutorial-icon {
      color: #3498db;
      margin-bottom: 20px;
      animation: bounce 1s ease infinite;
    }

    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }

    .tutorial-title {
      color: #ffffff;
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 15px;
      background: linear-gradient(45deg, #3498db, #2ecc71);
      -webkit-background-clip: text;
      background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .tutorial-text {
      color: #a4b5c6;
      font-size: 1.1rem;
      line-height: 1.6;
      margin-bottom: 25px;
    }

    .tutorial-text strong {
      color: #ffffff;
      font-weight: 600;
    }

    .tutorial-mini-card {
      background: rgba(52, 152, 219, 0.1);
      border: 1px solid rgba(52, 152, 219, 0.3);
      border-radius: 12px;
      padding: 15px 20px;
      margin: 20px auto;
      max-width: 400px;
      display: flex;
      align-items: center;
      gap: 15px;
      transition: all 0.3s ease;
    }

    .tutorial-mini-card:hover {
      background: rgba(52, 152, 219, 0.2);
      transform: translateY(-2px);
    }

    .tutorial-mini-card.highlight {
      background: rgba(46, 204, 113, 0.1);
      border-color: rgba(46, 204, 113, 0.3);
    }

    .tutorial-mini-card i {
      color: #3498db;
      font-size: 1.5rem;
    }

    .tutorial-mini-card.highlight i {
      color: #2ecc71;
    }

    .tutorial-mini-card span {
      color: #ffffff;
      font-size: 0.95rem;
      flex: 1;
      text-align: left;
    }

    .tutorial-progress {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin: 30px 0 25px;
    }

    .tutorial-step-indicator {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease;
    }

    .tutorial-step-indicator.active {
      background: #3498db;
      width: 30px;
      border-radius: 6px;
    }

    .tutorial-step-indicator.completed {
      background: #2ecc71;
    }

    .tutorial-buttons {
      display: flex;
      gap: 15px;
      justify-content: center;
    }

    .tutorial-next-btn,
    .tutorial-back-btn,
    .tutorial-finish-btn {
      padding: 12px 30px;
      border: none;
      border-radius: 25px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .tutorial-next-btn,
    .tutorial-finish-btn {
      background: linear-gradient(135deg, #3498db, #2ecc71);
      color: #ffffff;
      box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    }

    .tutorial-next-btn:hover,
    .tutorial-finish-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
    }

    .tutorial-back-btn {
      background: rgba(255, 255, 255, 0.1);
      color: #ffffff;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .tutorial-back-btn:hover {
      background: rgba(255, 255, 255, 0.15);
    }

    @media (max-width: 768px) {
      .tutorial-card {
        padding: 30px 20px;
      }

      .tutorial-title {
        font-size: 1.5rem;
      }

      .tutorial-text {
        font-size: 1rem;
      }

      .tutorial-mini-card {
        padding: 12px 15px;
      }
    }
  </style>

  <script>
    let currentTutorialStep = 0;
    const totalSteps = 6;

    function nextTutorialStep() {
      if (currentTutorialStep < totalSteps - 1) {
        document.querySelector(`[data-step="${currentTutorialStep}"]`).classList.remove('active');
        currentTutorialStep++;
        document.querySelector(`[data-step="${currentTutorialStep}"]`).classList.add('active');
      }
    }

    function prevTutorialStep() {
      if (currentTutorialStep > 0) {
        document.querySelector(`[data-step="${currentTutorialStep}"]`).classList.remove('active');
        currentTutorialStep--;
        document.querySelector(`[data-step="${currentTutorialStep}"]`).classList.add('active');
      }
    }

    function finishTutorial() {
      const overlay = document.getElementById('tutorialOverlay');
      overlay.style.animation = 'fadeOut 0.3s ease';
      setTimeout(() => {
        overlay.remove();
        // Send request to clear tutorial flag
        fetch('scripts/db/account/clear_tutorial.php', { method: 'POST' });
      }, 300);
    }

    // Skip tutorial
    document.getElementById('skipTutorial')?.addEventListener('click', finishTutorial);

    // Add fadeOut animation
    const style = document.createElement('style');
    style.textContent = `
      @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
      }
    `;
    document.head.appendChild(style);
  </script>
  <?php endif; ?>

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

    // Function to sync theme with iframe
    function syncThemeToIframe(theme) {
      const iframe = document.getElementById('pageContent');
      if (iframe && iframe.contentWindow) {
        try {
          iframe.contentWindow.postMessage({ type: 'themeChange', theme: theme }, '*');
        } catch (err) {
          console.log('Could not sync theme to iframe:', err);
        }
      }
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
        syncThemeToIframe('dark');
      } else {
        // Switch to light mode
        document.body.classList.add('light-mode');
        themeIcon.classList.remove('fa-moon');
        themeIcon.classList.add('fa-sun');
        themeText.textContent = 'Light Mode';
        localStorage.setItem('theme', 'light');
        syncThemeToIframe('light');
      }
    });

    // Sync theme when iframe loads
    const iframe = document.getElementById('pageContent');
    if (iframe) {
      iframe.addEventListener('load', function() {
        const currentTheme = localStorage.getItem('theme') || 'dark';
        syncThemeToIframe(currentTheme);
      });
    }

    // Search functionality
    function performSearch(query) {
      if (query.trim()) {
        document.getElementById('pageContent').src = 'pages/search_view.php?query=' + encodeURIComponent(query);
      }
    }

    // Desktop search
    document.getElementById('desktopSearchBtn')?.addEventListener('click', () => {
      performSearch(document.getElementById('desktopSearchInput').value);
    });
    document.getElementById('desktopSearchInput')?.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') performSearch(e.target.value);
    });

    // Mobile search
    document.getElementById('mobileSearchBtn')?.addEventListener('click', () => {
      performSearch(document.getElementById('mobileSearchInput').value);
    });
    document.getElementById('mobileSearchInput')?.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') performSearch(e.target.value);
    });

    // Logout function
    function logout() {
      if (confirm('Are you sure you want to logout?')) {
        fetch('scripts/db/account/logout.php', { 
          method: 'POST',
          credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            window.location.reload();
          } else {
            alert('Logout failed. Please try again.');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          // Reload anyway as a fallback
          window.location.reload();
        });
      }
    }
  </script>
</body>
</html>
