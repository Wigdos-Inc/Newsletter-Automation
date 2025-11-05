<?php 

require_once '../scripts/php/search.php';

// Get old Data
$rData = isset($_SESSION['searchData']) ? $_SESSION['searchData'] : null;

// Get new Data on Query Mismatch
$query = isset($_GET['query']) ? $_GET['query'] : null;
if ($query) {

    search(str_replace('%20', ' ', $_GET['query']));
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
    <link rel="stylesheet" href="styling/search.css">
</head>
<body>
    

    <div class="container py-5">
        <h1 class='mb-3 text-center'>Search Results</h1>

        <!-- Initial Results Check & Output -->
        <p class="text-center">
            <?php 
            if ($rData['results']) echo "Query yielded {$rData['amount']} results.";
            else if (!$rData['query'] && !$query) echo 'No search query given.';
            else echo 'No results found.';
            ?>
        </p>

        <!-- Newsletter Results -->
        <?php if ($results['newsletters']): ?>

            <h2 class='mb-3'><i>Newsletter Results</i></h2>

            <div class="resultBox">

            <?php foreach ($results['newsletters'] as $result): ?>

                <div class="resultItem">
                    <h2><?= $newsletters[$result[0]]['title'] ?></h2>
                    <p><?= $result[1] ?></p>
                </div>

            <?php endforeach; ?>

            </div>

        <?php endif; ?>

        <!-- Category Results -->
        <?php if ($results['categories']): ?>

            <h2 class='mb-3'><i>Category Results</i></h2>

        <?php endif; ?>

        <!-- Creator Results -->
        <?php if ($results['users']): ?>

            <h2 class='mb-3'><i>Creator Results</i></h2>

        <?php endif; ?>
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