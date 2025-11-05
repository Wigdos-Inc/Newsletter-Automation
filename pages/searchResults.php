<?php 

require_once '../scripts/php/search.php';

// Get Query
$query = isset($_GET['query']) ? str_replace('%20', ' ', $_GET['query']) : '';

// Get Filter Settings
$filters = isset($_GET['filters']) ? json_encode($_GET['filters']) : null;

// Get Results
$results = isset($_SESSION['qResults']) ? $_SESSION['qResults'] : null;
if (!$results && $query) $results = search($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Newswire AI</title>
</head>
<body>
    <h1>Search Results</h1>

    <?php

    // Testing Output
    # https://102871.stu.sd-lab.nl/beroeps/newsletters/pages/searchResults.php
    if ($results) print_r($results);
    else if (!$query) echo 'No search query given.';
    else echo 'No results.'

    ?>

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