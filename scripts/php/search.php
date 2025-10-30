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
    $query = strtolower($query);

    // Split Search into Terms/Words
    $sTerms = array_filter(
        explode(' ', $query),        // Seperate Words
        fn($term) => !empty($term)   // Remove Empty Terms
    );


    // Store Relevant Newsletters
    foreach ($newsletters as $id => $data) {

        $score = relevance($query, $sTerms, [$data['name'], $data['content']]);
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

    
    // Check Result Count
    $rAmount = 0;
    foreach ($results as $type) $rAmount += count($type);

    return $rAmount ? $results : null;
}



function relevance(string $query, array $terms, array $data) {

    $score   = 0;
    $weights = [5, 2, 1];

    // Check if Query Matches Data
    foreach ($data as $index => $text) {

        $weight = $weights[$index];

        // Temp Score
        $tScore = [0, 0];

        // Query Matching
        if      ($query == $text)                  $tScore[0] += $weight*2;               // Full Match
        else if ($strpos($text, $query) !== false) $tScore[0] += $weight;                 // Substring Match
        else if (
            similar_text($query, $text) / max(strlen($query), strlen($text)) > 0.6 ||     // String Similarity
            metaphone($query) === metaphone($text)                                        // Phonetic Similarity
        ) {
            $tScore[0] += $weight/2;
        }

        // Term Matching
        function termScore(float $amount) { &$tScore[1] += $amount / (count($terms)) }
        foreach($terms as $term) {

            if      ($term == $text)                  termScore($weight*2);
            else if ($strpos($text, $term) !== false) termScore($weight);
            else if (
                similar_text($term, $text) / max(strlen($term), strlen($text)) > 0.6 ||     
                metaphone($term) === metaphone($text)                                             
            ) {
                termScore($weight/2);
            }
        }

        // Add Highest Temp Score to Total Score
        $score += max($tScore);
    }

    return $score;
}