<?php
// build_articles.php
// Run in CI to generate a static dataset and index.html for GitHub Pages.
// Requires env MYSQL_* if using MySQL. Falls back to local SQLite (same logic as config.php simplified).

declare(strict_types=1);

// 1. Connect to MySQL (static-only mode expects MySQL; no SQLite fallback)
$pdo = null;
$mysqlHost = getenv('MYSQL_HOST') ?: '';
$mysqlDb   = getenv('MYSQL_DB') ?: '';
$mysqlUser = getenv('MYSQL_USER') ?: '';
$mysqlPass = getenv('MYSQL_PASS') ?: '';
$mysqlPort = getenv('MYSQL_PORT') ?: '3306';

if ($mysqlHost && $mysqlDb && $mysqlUser) {
    $dsn = "mysql:host={$mysqlHost};dbname={$mysqlDb};port={$mysqlPort};charset=utf8mb4";
    try {
        $pdo = new PDO($dsn, $mysqlUser, $mysqlPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (Throwable $e) {
        fwrite(STDERR, "MySQL connection failed: {$e->getMessage()}\n");
    }
} else {
    fwrite(STDERR, "Missing one or more MySQL env vars (MYSQL_HOST, MYSQL_DB, MYSQL_USER, MYSQL_PASS).\n");
}

$articles = [];
if ($pdo instanceof PDO) {
    try {
        $sql = "SELECT 
                    ID        AS id,
                    titels    AS title,
                    text_body AS text_body,
                    sources   AS sources,
                    date      AS date
                FROM articles
                ORDER BY date DESC, ID DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $articles = $stmt->fetchAll();
    } catch (Throwable $e) {
        fwrite(STDERR, "Query failed: {$e->getMessage()}\n");
    }
}

// 2. Ensure docs directory
$docsDir = __DIR__ . '/../docs';
if (!is_dir($docsDir)) {
    mkdir($docsDir, 0777, true);
}

// 3. Write articles.json
file_put_contents($docsDir . '/articles.json', json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "Generated docs/articles.json with " . count($articles) . " articles\n";

// 4. Create static index.html (does not embed data; JS fetches articles.json)
$indexHtml = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>AI Articles</title>
        <link rel="stylesheet" href="css/main.css">
</head>
<body>
        <div id="header">
                <h1>AI Articles</h1>
        </div>
        <div id="articles">Loading articles...</div>
        <script src="js/main.js"></script>
</body>
</html>
HTML;

file_put_contents($docsDir . '/index.html', $indexHtml);

echo "Wrote docs/index.html\n";

// 5. Optionally copy assets (css/js) for Pages. We'll create shallow copies referencing root? Easiest: duplicate.
function copyDir($src, $dest) {
    if (!is_dir($src)) return;
    if (!is_dir($dest)) mkdir($dest, 0777, true);
    foreach (scandir($src) as $f) {
        if ($f === '.' || $f === '..') continue;
        $sp = "$src/$f";
        $dp = "$dest/$f";
        if (is_dir($sp)) {
            copyDir($sp, $dp);
        } else {
            copy($sp, $dp);
        }
    }
}

copyDir(__DIR__ . '/../css', $docsDir . '/css');
copyDir(__DIR__ . '/../js', $docsDir . '/js');

echo "Copied assets to docs/. Done.\n";

?>
