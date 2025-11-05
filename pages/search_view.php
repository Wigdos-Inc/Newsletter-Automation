<?php 

require_once '../scripts/php/search.php';

// Get old Data
$rData = isset($_SESSION['searchData']) ? $_SESSION['searchData'] : null;

// Get Query
$query = isset($_SESSION['query']) ? $_SESSION['query'] : null;

// Get new Data on Query Mismatch
if ($query && $query != $rData['query']) {

    search($query);
    $rData = $_SESSION['searchData'];
}

// Get Filter Settings
$filters = isset($_GET['filters']) ? json_encode($_GET['filters']) : null;



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Newswire AI</title>

    <base href="../">
    <link rel="stylesheet" href="bootstrap-5.3.6-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styling/main.css">
    <link rel="stylesheet" href="styling/searchResults.css">
</head>
<body>
    

    <div class="container py-5">
        <h1 class='mb-3 text-center'>Search Results</h1>

        <!-- Initial Results Check & Output -->
        <p class="text-muted text-center">
            <?php 
            if ($results) echo "Query yielded $rCount results.";
            else if (!$query) echo 'No search query given.';
            else echo 'No results found.';
            ?>
        </p>

        <!-- Newsletter Results -->
        <?php if ($results['newsletters']): ?>

            <h2 class='mb-3 text-muted'><i>Newsletter Results</i></h2>

        <?php endif; ?>

        <!-- Category Results -->
        <?php if ($results['categories']): ?>

            <h2 class='mb-3 text-muted'><i>Category Results</i></h2>

        <?php endif; ?>

        <!-- Creator Results -->
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