<!doctype html>
<html lang="en">
<head>
  <base href="../">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="bootstrap-5.3.6-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="styling/main.css">
  <title>Newsletters</title>
</head>
<body>
  <div class="container py-5">
    <h1 class="mb-3">Newsletters</h1>
    <p class="text-muted">This is the newsletters page loaded inside the main layout via an iframe.</p>
  </div>

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