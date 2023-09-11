<?php

namespace Niktwenty3\RelatedByTaxonomy;

use Statamic\Tags\Tags;
use Statamic\Facades\Entry;

class Relbytaxonomy extends \Statamic\Tags\Tags
{
    public function index()
    {   

        // Current entry ID
        $entryId = $this->context->get('id');
        
        // Current entry
        $entry = Entry::find($entryId);

        // Current entry collection
        $collection = $entry->collection()->handle();

        // Split the taxonomies parameter into an array
        $taxonomies = explode('|', $this->params->get('taxonomies'));

        
        // Split modifier scores parameter into an array
        $modifiers =  explode('|', $this->params->get('modifiers'));

        // Get the stop words list
        $stopWordsList = explode(',', $this->params->get('stopwords'));;

        // Get the number of entries to return
        $entriesLimit = $this->params->get('limit');

        // Get all entries in the current collection
        $allEntries = Entry::query()->where('collection', $collection)->where('published', true)->get();

        // Array to store related entries
        $relatedEntries = [];

        // Loop through all entries
        foreach ($allEntries as $otherEntry) {

            // Skip the current entry
            if ($entry->id() == $otherEntry->id()) continue;

            // Initialize score and index
            $score = 0;
            $index = 0;
            // Array to store common tags for each entry
            $commonTags = [];
            // Array to store taxonomies that had a match
            $found_in_taxonomies = [];

            // Tokenize the titles into arrays
            $entryTitleTokens = array_unique(explode(' ', strtolower($entry->get('title'))));
            $otherEntryTitleTokens = array_unique(explode(' ', strtolower($otherEntry->get('title'))));

            // Remove stop words from the title tokens
            $entryTitleTokens = array_diff($entryTitleTokens, $stopWordsList);
            $otherEntryTitleTokens = array_diff($otherEntryTitleTokens, $stopWordsList);

            // Count the total number of unique words in the entry's title
            $totalWordsInEntryTitle = count($entryTitleTokens);

            // Determine how many of those words were found in the other entry's title
            $commonWordsCount = count(array_intersect($entryTitleTokens, $otherEntryTitleTokens));

            // Compute the percentage of words found in the other entry's title
            $wordMatchPercentage = ($commonWordsCount / $totalWordsInEntryTitle) * 100;

            // Loop through each taxonomy
            foreach ($taxonomies as $taxonomy) {

                // if both entries have the taxonomy
                if ($entry->get($taxonomy) && $otherEntry->get($taxonomy)) {
                    
                        // Get the shared items between the two entries
                        $sharedItems = array_intersect($entry->get($taxonomy), $otherEntry->get($taxonomy));

                        // Merge the shared items into the common tags array
                        $commonTags = array_merge($commonTags, $sharedItems);
                        array_push($found_in_taxonomies, $taxonomy);
                        
                        // Increase the score by the number of shared items multiplied by the modifier
                        $score += count($sharedItems)*(float)$modifiers[$index];
                    
                }

                // Increment the index
                $index++;
            }
            
            // [TODO] Add other scoring rules here...
            
            // If the score is greater than 0, add the entry to the related entries array
            if ($score > 0 || $wordMatchPercentage > 0) {
                if ($wordMatchPercentage > 0) {
                    $calculatedTitleScore = $wordMatchPercentage/10;
                } else {
                    $calculatedTitleScore = 0;
                }
                $relatedEntries[] = [
                    'entry' => $otherEntry,
                    'score' => $score + $calculatedTitleScore, // Add the score in order to sort by score
                    'date' => $otherEntry->date()->timestamp, // Add the timestamp of the entry's date in order to sort by date
                    'common_tags' => implode(", ", $commonTags),
                    'found_in_taxonomies' => implode(", ", $found_in_taxonomies),
                    'word_match_percentage' => $wordMatchPercentage,
                ];
            }
        }

        // Sort the entries by score in descending order
        usort($relatedEntries, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Limit the number of related entries
        $relatedEntries = array_slice($relatedEntries, 0, $entriesLimit);

        // Sort the limited related entries by date in descending order
        // usort($relatedEntries, function ($a, $b) {
        //     return $b['word_match_percentage'] <=> $a['word_match_percentage'];
        // });
        
        $result = array_map(function ($item) {
            return $item['entry']->toAugmentedArray();
        }, $relatedEntries);

        // Add the score, common tags, and found in taxonomies to the result
        // Doing it this way because if I try to map weird things happen
        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['score'] = $relatedEntries[$i]['score'];
            $result[$i]['common_tags'] = $relatedEntries[$i]['common_tags'];
            $result[$i]['found_in_taxonomies'] = $relatedEntries[$i]['found_in_taxonomies'];
            $result[$i]['word_match_percentage'] = $relatedEntries[$i]['word_match_percentage'];
        }

        return [
            'related_entries' => $result
        ];
    }
}
