<!doctype html>
<html lang="en">
<head>
  <base href="../">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="bootstrap-5.3.6-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="styling/main.css">
  <title>Newsletter Generator</title>
</head>
<body>
  <div class="container py-5">
    <h1 class="mb-4">AI Newsletter Generator</h1>
    
    <!-- Input Form -->
    <div class="card mb-4">
      <div class="card-body">
        <label class="form-label">What should the newsletter be about?</label>
        <textarea id="newsletter-query" class="form-control mb-3" rows="4" 
                  placeholder="Example: Latest AI trends in 2025"></textarea>
        <button id="generate-btn" class="btn btn-primary">Generate Newsletter</button>
      </div>
    </div>

    <!-- Loading Spinner -->
    <div id="loading" style="display:none;" class="text-center my-4">
      <div class="spinner-border text-primary"></div>
      <p class="mt-2">Generating newsletter... (may take 1-2 minutes)</p>
    </div>

    <!-- Results -->
    <div id="result" style="display:none;" class="card">
      <div class="card-body" id="newsletter-content"></div>
    </div>

    <!-- Errors -->
    <div id="error" style="display:none;" class="alert alert-danger"></div>
  </div>

  <script src="bootstrap-5.3.6-dist/js/bootstrap.bundle.min.js"></script>
  <script src="scripts/newsletterPrompt.js"></script>
</body>
</html>