<?php


// DB Connect
require_once 'config.php';

// Start Session (If necessary) & Get User ID
if (session_status() === PHP_SESSION_NONE) session_start();
$userID = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;



/* === Main Data Queries === */

// Check if DB is up
if (!$dbStatus) die("No DB Connection");

// Prep Query Function
function iniData(PDO $pdo, string $query, string $idKey, array $extra) {

    $output = [];
    foreach ($pdo->query($query, PDO::FETCH_ASSOC) as $row) {

        if (!array_key_exists($idKey, $row)) {
            trigger_error("Warning: $idKey not found in query results", E_USER_WARNING);
            return [];
        }
        $output[$row[$idKey]] = $row + $extra;
    }

    return $output;
}

// Function Call Structure:
# DB Access                    - $pdo
# Query                        - String
# Name ID Column               - String
# Additional Output Structure  - Array

// Fetch Data
$newsletters = iniData(
    $pdo,
    "SELECT * FROM tab_newsletters", 
    'newsletter_ID', 
    [
        'contributors' => [],
        'favorited_by' => [],
        'categories'   => []
    ]
);
$categories = iniData(
    $pdo,
    "SELECT * FROM tab_categories", 
    'category_ID', 
    [
        'favorited_by' => [],
        'newsletters'  => []
    ]
);
$creators = iniData(
    $pdo,
    "SELECT user_ID, username, creation_date FROM tab_users WHERE role = 'admin' OR role = 'owner'", 
    'user_ID', 
    [
        'newsletters'   => [],
        'contributions' => [],
        'categories'    => []
    ]
);



/* === Connection Table Queries === */

// Many-to-Many Connections:
# Newsletters-Categories
# Newsletters-Contributors
# User Favorites-Newsletters
# User Favorites-Categories
# User Favorites-Creators

// Loop Prep
$loopStruc = [
    'tables'     => ['con_newsletter_categories', 'con_user_newsletters'],
    'idKeys'     => [
        ['newsletter_ID', 'category_ID'],
        ['newsletter_ID', 'user_ID']
    ],
    'targets'    => [
        [&$newsletters, &$categories],
        [&$newsletters, &$creators]
    ],
    'properties' => [
        ['categories', 'newsletters'],
        ['contributors', 'newsletters']
    ]
];

// Add Connection Data
for ($i=0; $i < count($loopStruc['tables']); $i++) {

    // Iterate through Table Rows
    try {
        foreach($pdo->query("SELECT * FROM {$loopStruc['tables'][$i]}", PDO::FETCH_ASSOC) as $row) {

            // Easier Access
            $ids   = [$row[$loopStruc['idKeys'][$i][0]], $row[$loopStruc['idKeys'][$i][1]]];
            $tgts  = &$loopStruc['targets'][$i];
            $props = $loopStruc['properties'][$i];

            // Focus Left-ID (AI Assisted)
            $tgts[0][$ids[0]][$props[0]][] = $ids[1];

            // Focus Right-ID (AI Assisted)
            $tgts[1][$ids[1]][$props[1]][] = $ids[0];
        }
    }
    catch (PDOException $e) {
        echo "Error: " . $e;
        echo "Table: " . $loopStruc['tables'][$i];
    }
}


// To see raw data structure, open: https://102871.stu.sd-lab.nl/beroeps/newsletters/scripts/db/dataTest.php