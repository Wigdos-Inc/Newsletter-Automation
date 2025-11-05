<!doctype html>
<html lang="en">
<head>
  <base href="../">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="bootstrap-5.3.6-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="styling/main.css">
  <title>Results / Categories</title>
</head>
<body>
  <div class="container py-5">
    <h1 class="mb-3">Results / Categories</h1>
    <p class="text-muted">This is the categories/results page loaded inside the main layout via an iframe.</p>

    <div class="row">
      <div class="col-md-4">
        <div class="card mb-3">
          <div class="card-body">
            <h5 class="card-title">Category A</h5>
            <p class="card-text">Short description of category A.</p>
            <a href="#" class="btn btn-sm btn-outline-primary">View</a>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card mb-3">
          <div class="card-body">
            <h5 class="card-title">Category B</h5>
            <p class="card-text">Short description of category B.</p>
            <a href="#" class="btn btn-sm btn-outline-primary">View</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <style>
    /* Dark mode styles (default) */
    body {
      background-color: #1a1c20;
      color: #ffffff;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    body h1,
    body p,
    body .card-title,
    body .card-text {
      color: #ffffff;
    }

    body .text-muted {
      color: #a4b5c6 !important;
    }

    /* Cards in dark mode - same color scheme as navigation */
    body .card {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: all 0.3s ease;
    }

    body .card:hover {
      background: rgba(255, 255, 255, 0.08);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    body .btn-outline-primary {
      color: #3498db;
      border-color: #3498db;
    }

    body .btn-outline-primary:hover {
      background-color: #3498db;
      color: #ffffff;
    }

    /* Light mode overrides */
    body.light-mode {
      background-color: #f5f7fa;
      color: #2c3e50;
    }

    body.light-mode h1,
    body.light-mode p,
    body.light-mode .card-title,
    body.light-mode .card-text {
      color: #2c3e50;
    }

    body.light-mode .text-muted {
      color: #95a5a6 !important;
    }

    body.light-mode .card {
      background: #ffffff;
      border: 1px solid rgba(0, 0, 0, 0.1);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    body.light-mode .card:hover {
      background: #ffffff;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
  </style>

  <script>
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