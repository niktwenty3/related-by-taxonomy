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

        // Get all entries in the current collection
        $allEntries = Entry::query()->where('collection', $collection)->get();

        // Array to store related entries
        $relatedEntries = [];


        // Loop through all entries
        foreach ($allEntries as $otherEntry) {

            // Skip the current entry
            if ($entry->id() == $otherEntry->id()) continue;

            // Initialize score and index
            $score = 0;
            $index = 0;

            // Loop through each taxonomy
            foreach ($taxonomies as $taxonomy) {
                
                // if both entries have the taxonomy
                if ($entry->get($taxonomy) && $otherEntry->get($taxonomy)) {
                
                    // Get the shared items
                    $sharedItems = array_intersect($entry->get($taxonomy), $otherEntry->get($taxonomy));
                
                    // Add the score to the total score multiplied by the modifier for the taxonomy
                    $score += count($sharedItems)*(float)$modifiers[$index];
                }

                // Increment the index
                $index++;
            }

            // [TODO] Add other scoring rules here...

            // If the score is greater than 0, add the entry to the related entries array
            if ($score > 0) {
                $relatedEntries[] = [
                    'entry' => $otherEntry,
                    'score' => $score, // Add the score in order to sort by score
                    'date' => $otherEntry->date()->timestamp, // Add the timestamp of the entry's date in order to sort by date
                ];
            }
        }

        // Sort the entries by score in descending order
        usort($relatedEntries, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Limit the number of related entries
        $relatedEntries = array_slice($relatedEntries, 0, 4);

        // Sort the limited related entries by date in descending order
        usort($relatedEntries, function ($a, $b) {
            return $b['date'] <=> $a['date'];
        });
        
        return [
            'related_entries' => array_map(function ($item) {
                return $item['entry']->toAugmentedArray();
            }, $relatedEntries),
            'scores' =>  array_map(function ($item) {
                return $item['score'];
            }, $relatedEntries)
        ];
    }
}