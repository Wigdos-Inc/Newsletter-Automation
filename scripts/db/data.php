<?php


// DB Connect
require_once 'config.php';


// Start Session (If necessary)
if (session_status() === PHP_SESSION_NONE) session_start();

// Get User ID if it exists
$userID = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;


/* DB Stuff */

// Query & Storage Prep
$structure = [
    'newsletters' => [
        'contributors' => [],
        'favorited_by' => [],
        'categories'   => []
    ],
    'categories' => [
        'favorited_by' => [],
        'newsletters'  => []
    ],
    'creators' => [
        'newsletters'   => [],
        'contributions' => [],
        'categories'    => []
    ]
];


// Prep Query Function
function iniData(PDO $pdo, string $query, string $idKey, array $extra): array {

    $output = [];
    foreach ($pdo->query($query, PDO::FETCH_ASSOC) as $row) {

        if (!array_key_exists($idKey, $row)) {
            trigger_error("Warning: $idKey not found in query results", E_USER_WARNING);
            return;
        }
        $output[$row[$idKey]] = $row + $extra;
    }

    return $output;
}

// Fetch Data
$newsletters = iniData(
    $pdo,
    "SELECT * FROM tab_newsletters", 
    'newsletter_ID', 
    $structure['newsletters']
);
$categories  = iniData(
    $pdo,
    "SELECT * FROM tab_categories", 
    'category_ID', 
    $structure['categories']
);
$creators    = iniData(
    $pdo,
    "SELECT user_ID, username, creation_date FROM tab_users WHERE role = 'admin' OR role = 'owner'", 
    'user_ID', 
    $structure['creators']
);

// Fetch Connections


// Display data for testing
echo "<pre>";
print_r($data);
echo "</pre>";