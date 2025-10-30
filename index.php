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

        <!-- Logo -->
        <div class="logo-section">
          <img src="media/images/icons/favicon.svg" alt="Logo" class="header-logo">
          <span class="logo-text d-none d-md-inline ms-2">Newswire AI</span>
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
          <!-- Profile Dropdown -->
          <div class="position-relative">
            <div class="profile-icon" id="profileIcon">
              <i class="fas fa-user"></i>
            </div>
            <div class="profile-dropdown" id="profileDropdown">
              <a href="#" data-page="pages/newsletters.php"><i class="fas fa-user me-2"></i> Profile</a>
              <a href="#" data-page="pages/newsletters.php"><i class="fas fa-inbox me-2"></i> Inbox</a>
              <a href="#" data-page="pages/newsletters.php"><i class="fas fa-sign-in-alt me-2"></i> Login</a>
              <a href="#" data-page="pages/newsletters.php"><i class="fas fa-user-plus me-2"></i> Register</a>
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

  <!-- Main Content -->
  <main class="flex-grow-1 py-5">
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
    // Profile Dropdown Toggle
    const profileIcon = document.getElementById('profileIcon');
    const profileDropdown = document.getElementById('profileDropdown');

    if (profileIcon && profileDropdown) {
      profileIcon.addEventListener('click', (e) => {
        e.stopPropagation();
        profileDropdown.classList.toggle('show');
      });

      // Close dropdown when clicking outside
      document.addEventListener('click', (e) => {
        if (!profileIcon.contains(e.target) && !profileDropdown.contains(e.target)) {
          profileDropdown.classList.remove('show');
        }
      });
    }

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
  </script>
</body>
</html>
