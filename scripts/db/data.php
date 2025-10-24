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
function iniData(PDO $pdo, string $query, string $idKey, array $extra): array {

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



/* Structure Examples:

$newsletters = [
    '1' => [
        'newsletter_ID' => 1,
        'title'         => 'Weekly Tech Digest',
        'content'       => 'Newsletter content here...',
        'status'        => 'published',
        'creation_date' => '2025-01-15',
        'user_ID'       => 5,
        'contributors'  => [],
        'favorited_by'  => [],
        'categories'    => [2, 5, 8]  // Category IDs
    ],
    '2' => [
        'newsletter_ID' => 2,
        'title'         => 'Design Weekly',
        'content'       => 'More content...',
        'status'        => 'draft',
        'creation_date' => '2025-02-20',
        'user_ID'       => 3,
        'contributors'  => [],
        'favorited_by'  => [],
        'categories'    => [1, 3]
    ]
];
$categories = [
    '1' => [
        'category_ID'   => 1,
        'name'          => 'Technology',
        'user_ID'       => 5,
        'favorited_by'  => [2, 4, 6],
        'newsletters'   => [2, 3]
    ],
    '2' => [
        'category_ID'   => 2,
        'name'          => 'Design',
        'user_ID'       => 3,
        'favorited_by'  => [4, 11],
        'newsletters'   => [1, 5, 6]
    ]
];
$creators = [
    '3' => [
        'user_ID'       => 3,
        'username'      => 'john',
        'role'          => 'admin'
        'creation_date' => '2024-06-10',
        'newsletters'   => [],
        'contributions' => [],
        'categories'    => []
    ],
    '5' => [
        'user_ID'       => 5,
        'username'      => 'sarah',
        'role'          => 'owner'
        'creation_date' => '2024-05-01',
        'newsletters'   => [],
        'contributions' => [],
        'categories'    => []
    ]
];

*/



// Display data for testing
#echo "<pre>";
#print_r($data);
#echo "</pre>";