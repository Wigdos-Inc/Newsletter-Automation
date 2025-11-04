<?php

session_start();

function search(string $query) {

    // Return if Empty or Null
    if (!isset($query) || !strlen($query)) return null;


    // Easier Access
    $newsletters = &$_SESSION['newsletters'];
    $categories  = &$_SESSION['categories'];
    $users       = &$_SESSION['users'];

    // Prep Results
    $results = [
        'n' => [],  // Newsletters
        'c' => [],  // Categories
        'u' => []   // Users/Creators
    ];


    // Format Query
    $query = strtolower(trim($query));

    // Split Search into Terms/Words
    $sTerms = array_filter(
        explode(' ', $query),              // Seperate Words
        fn($term) => !empty(trim($term))   // Remove Empty Terms
    );


    // Store Relevant Newsletters
    foreach ($newsletters as $id => $data) {

        $score = relevance($query, $sTerms, [$data['title'], $data['content']]);
        if ($score > 5) $results['n'][] = [$id, $score];
    }

    // Store Relevant Categories
    foreach ($categories as $id => $data) {

        $score = relevance($query, $sTerms, [$data['name']]);
        if ($score > 5) $results['c'][] = [$id, $score];
    }

    // Store Relevant Creators
    foreach ($users as $id => $data) {

        $score = relevance($query, $sTerms, [$data['username']]);
        if ($score > 5) $results['u'][] = [$id, $score];
    }


    // Sort results by Score
    foreach ($results as &$type) usort($type, fn($a, $b) => $b[1] <=> $a[1]);

    
    // Check Result Count
    $rAmount = 0;
    foreach ($results as $type) $rAmount += count($type);

    if ($rAmount) {
        $_SESSION['qResults'] = $results;   // Store for faster Reload
        return $results;
    }
    else {
        $_SESSION['qResults'] = null;       // Empty to prevent Confusion
        return null;
    }
}



function relevance(string $query, array $qTerms, array $data) {

    $score   = 0;
    $weights = [5, 2, 1];

    // Check if Query Matches Data
    foreach ($data as $type => $text) {

        $weight = $weights[$type] ?? 1;
        $text = strtolower($text);
        $tScore = 0;

        // Split Text into Words
        $tWords = array_filter(
            explode(' ', $text),                 // Seperate Words
            fn($tWord) => !empty(trim($tWord))   // Remove Empty Entries
        );

        // Query Match or Appearance
        if      ($query == $text)                                       $tScore = $weight*10;
        else if (strpos($text, $query) !== false && strlen($query) > 5) $tScore = $weight*8;
        else {

            // Term Matching
            foreach($qTerms as $term) {

                $term = trim($term, '.,!?;:/|=');

                // Term Appearance
                if (strpos($text, $term) !== false) {
                    $tScore += round($weight / count($qTerms) * 5, 2);
                    continue;
                }

                foreach ($tWords as $word) {

                    $found = 0;
                    $word = trim($word, '.,!?;:/|=');

                    // Word Similarity (for Typos)
                    $similarity = round(similar_text($term, $word) / max(strlen($term), strlen($word)), 2);
                    if ($similarity > 0.6) {
                        $tScore += round($weight * $similarity / count($qTerms) * 2, 2);
                        $found++;
                    }

                    // Phonetic Match
                    if (metaphone($term) === metaphone($word)) {
                        $tScore += round($weight / count($qTerms), 2);
                        $found++;
                    }

                    // Check next Term
                    if ($found) break;
                }
            }

        }

        // Overwrite Score if higher
        $score += $tScore;
    }

    return $score;
}