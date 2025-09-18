<?php
// build_articles.php
// Run in CI to generate a static dataset and index.html for GitHub Pages.
// Requires env MYSQL_* if using MySQL. Falls back to local SQLite (same logic as config.php simplified).

declare(strict_types=1);

function logInfo(string $msg): void { echo "[INFO] $msg\n"; }
function logWarn(string $msg): void { fwrite(STDERR, "[WARN] $msg\n"); }
function logError(string $msg): void { fwrite(STDERR, "[ERROR] $msg\n"); }

// 1. Connect to MySQL (static-only mode expects MySQL; no SQLite fallback)
$pdo = null;
$mysqlHost = getenv('MYSQL_HOST') ?: '';
$mysqlDb   = getenv('MYSQL_DB') ?: '';
$mysqlUser = getenv('MYSQL_USER') ?: '';
$mysqlPass = getenv('MYSQL_PASS') ?: '';
$mysqlPort = getenv('MYSQL_PORT') ?: '3306';

logInfo('Starting static build...');
logInfo('Checking required MySQL env vars (values hidden)...');
foreach (['MYSQL_HOST'=>$mysqlHost,'MYSQL_DB'=>$mysqlDb,'MYSQL_USER'=>$mysqlUser,'MYSQL_PASS'=>$mysqlPass] as $k=>$v) {
    if ($v === '') { logWarn("Env var $k is MISSING"); }
}

if ($mysqlHost && $mysqlDb && $mysqlUser && $mysqlPass !== '') {
    $dsn = "mysql:host={$mysqlHost};dbname={$mysqlDb};port={$mysqlPort};charset=utf8mb4";
    try {
        $pdo = new PDO($dsn, $mysqlUser, $mysqlPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        logInfo('MySQL connection established.');
    } catch (Throwable $e) {
        logError('MySQL connection failed: ' . $e->getMessage());
    }
} else {
    logError('Missing one or more MySQL env vars (MYSQL_HOST, MYSQL_DB, MYSQL_USER, MYSQL_PASS).');
}

$articles = [];
if ($pdo instanceof PDO) {
    // Column sanity check to detect naming mismatches early
    try {
        $colRows = $pdo->query('SHOW COLUMNS FROM articles');
        $cols = $colRows ? $colRows->fetchAll(PDO::FETCH_COLUMN) : [];
        $hasTitle  = in_array('title', $cols, true);
        $hastitle = in_array('title', $cols, true); // legacy misspelling support
        $expectedCore = ['ID','text_body','sources','date'];
        $missingCore = array_diff($expectedCore, $cols);
        if ($missingCore) {
            logWarn('Missing required columns: ' . implode(', ', $missingCore));
        }
        if (!$hasTitle && !$hastitle) {
            logError("Neither 'title' nor legacy 'title' column exists. Add one.");
        } elseif ($hasTitle && $hastitle) {
            logWarn("Both 'title' and 'title' exist; using 'title'. Consider dropping 'title'.");
        } elseif ($hastitle) {
            logWarn("Using legacy column 'title'. Rename it to 'title' when possible.");
        } else {
            logInfo("Using 'title' column.");
        }
    } catch (Throwable $e) {
        logWarn('Could not inspect columns: ' . $e->getMessage());
    }
    try {
        // Prefer 'title'; fallback to 'title' if 'title' not present.
        $titleExpr = '(CASE WHEN (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = "articles" AND COLUMN_NAME = "title") > 0 THEN title ELSE title END)';
        $sql = "SELECT 
                    ID        AS id,
                    $titleExpr AS title,
                    text_body AS text_body,
                    sources   AS sources,
                    date      AS date
                FROM articles
                ORDER BY date DESC, ID DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $articles = $stmt->fetchAll();
        logInfo('Fetched ' . count($articles) . ' articles.');
    } catch (Throwable $e) {
        logError('Query failed: ' . $e->getMessage());
    }
}

// 2. Ensure docs directory
$docsDir = __DIR__ . '/../docs';
if (!is_dir($docsDir)) {
    mkdir($docsDir, 0777, true);
}

// 3. Write articles.json
file_put_contents($docsDir . '/articles.json', json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
logInfo('Generated docs/articles.json with ' . count($articles) . ' articles');

// Write build log for quick inspection via web if desired
$logSummary = [
    'timestamp_utc' => gmdate('c'),
    'article_count' => count($articles),
    'had_connection' => $pdo instanceof PDO,
    'mysql_host_present' => $mysqlHost !== '',
    'warnings' => []
];
if (!($pdo instanceof PDO)) { $logSummary['warnings'][] = 'No DB connection established'; }
if (empty($articles)) { $logSummary['warnings'][] = 'Zero articles returned'; }
file_put_contents($docsDir . '/build_log.json', json_encode($logSummary, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

if (!($pdo instanceof PDO)) {
    logError('Build failed: No database connection.');
    exit(1);
}
if (count($articles) === 0 && getenv('BUILD_ALLOW_EMPTY') !== '1') {
    logError('Build failed: Zero articles (set BUILD_ALLOW_EMPTY=1 to allow).');
    exit(2);
}

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

logInfo('Wrote docs/index.html');

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

logInfo('Copied assets to docs/. Done.');
logInfo('Build completed successfully.');

?>
