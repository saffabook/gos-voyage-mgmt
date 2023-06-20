<?php

namespace App\Helpers;

class CheckSimilarWords
{
    public static function execute($word1, $word2, $distanceToCheck = 1)
    {
        // Convert the words to lowercase for case-insensitive comparison
        $word1Lower = strtolower($word1);
        $word2Lower = strtolower($word2);

        // Sort the characters of each word
        $sortedWord1 = str_split($word1Lower);
        sort($sortedWord1);
        $sortedWord2 = str_split($word2Lower);
        sort($sortedWord2);

        // Convert the sorted characters back to strings
        $sortedWord1 = implode('', $sortedWord1);
        $sortedWord2 = implode('', $sortedWord2);

        // Check if the sorted words are equal
        $levenshteinDistance = levenshtein($sortedWord1, $sortedWord2);
        if ($sortedWord1 === $sortedWord2 || $levenshteinDistance <= $distanceToCheck) {
            return true;
        } else {
            return false;
        }
    }
}
