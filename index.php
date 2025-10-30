<?php require_once 'scripts/db/data.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="bootstrap-5.3.6-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="styling/main.css">
  <link rel="stylesheet" href="styling/index.css">
  <link rel="shortcut icon" href="media/images/icons/favicon.svg" type="image/x-icon">
  <title>Newswire AI</title>
</head>
<body>

  <!-- Header -->
  <header class="bg-light shadow-sm py-3">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center">

        <!-- Logo -->
        <div class="d-flex align-items-center">
          <img src="media/images/icons/favicon.svg" alt="Logo" class="me-2">
          <span class="fw-bold fs-5 d-none d-md-inline">Newswire AI</span>
        </div>
        
        <!-- Desktop Navigation -->
        <nav class="d-none d-md-flex gap-3">
          <a href="#" data-page="pages/newsletters.php" class="nav-link text-dark">Home</a>
          <a href="#" data-page="pages/categories.php" class="nav-link text-dark">Results</a>
          <a href="#" data-page="pages/newsletters.php" class="nav-link text-dark">Inbox</a>
          <a href="#" data-page="pages/newsletters.php" class="nav-link text-dark">Login</a>
        </nav>

        <!-- Right Section -->
        <div class="d-flex align-items-center gap-2">
          <input type="text" class="form-control form-control-sm d-none d-md-block" placeholder="Search...">
          <a href="#" class="btn text-dark d-none d-md-block"><i class="ri-login-circle-line"></i></a>
          <a href="#" class="btn text-dark d-none d-md-block"><i class="ri-shopping-cart-2-line"></i></a>
          <button class="btn btn-outline-secondary d-md-none" id="menuToggle">
            <i class="ri-menu-line"></i>
          </button>
        </div>

      </div>

      <!-- Mobile Menu -->
      <div id="mobileMenu" class="d-md-none mt-3" style="display:none;">
        <nav class="d-flex flex-column align-items-center gap-2 mb-3">
          <a href="#" data-page="pages/newsletters.php" class="nav-link text-dark">Home</a>
          <a href="#" data-page="pages/categories.php" class="nav-link text-dark">Results</a>
          <a href="#" data-page="pages/newsletters.php" class="nav-link text-dark">Inbox</a>
          <a href="#" data-page="pages/newsletters.php" class="nav-link text-dark">Login</a>
        </nav>
        <div class="d-flex flex-column gap-2 mb-3">
          <a href="#" class="btn w-100"><i class="ri-login-circle-line"></i></a>
          <a href="#" class="btn w-100"><i class="ri-shopping-cart-2-line"></i></a>
        </div>
        <input type="text" class="form-control" placeholder="Search...">
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
          <div class="footer-logo">NEWSfeedAI</div>
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
            <li><a href="#">About Us</a></li>
            <li><a href="#">Services</a></li>
            <li><a href="#">Products</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="#">Careers</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-6 footer-section">
          <h5>Support</h5>
          <ul class="footer-links">
            <li><a href="#">FAQ</a></li>
            <li><a href="#">Help Center</a></li>
            <li><a href="#">Privacy Policy</a></li>
            <li><a href="#">Terms of Service</a></li>
            <li><a href="#">Contact Support</a></li>
          </ul>
        </div>

        <div class="col-lg-4 footer-section">
          <h5>Newsletter</h5>
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
        <p>© 2025 CompanyName. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <script src="bootstrap-5.3.6-dist/js/bootstrap.bundle.min.js"></script>
  <script src="scripts/main.js"></script>
</body>
</html>
