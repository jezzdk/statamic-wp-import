<?php

namespace Jezzdk\StatamicWpImport\Helpers;

use Exception;
use Statamic\Facades\Entry;
use Statamic\Facades\Term;

class WpImporter
{
    public function prepare($data)
    {
        if (!$data = json_decode($data, true)) {
            throw new Exception('Invalid export data format.');
        }

        return (new Preparer)->prepare($data);
    }

    public function summary($prepared)
    {
        $summary = [];

        foreach ($prepared['pages'] as $page_url => $page) {
            $summary['pages'][$page_url] = [
                'url' => $page_url,
                'title' => array_get($page['data'], 'title'),
                'exists' => !!Entry::findByUri($page_url),
                '_checked' => true,
            ];
        }

        foreach ($prepared['entries'] as $collection => $entries) {
            $duplicates = 0;
            $collection_entries = [];

            foreach ($entries as $slug => $entry) {
                if ($has_duplicates = !!Entry::query()->where('collection', $collection)->where('slug', $slug)->first()) {
                    $duplicates++;
                }

                $collection_entries[$slug] = [
                    'slug' => $slug,
                    'exists' => $has_duplicates,
                    '_checked' => true,
                ];
            }

            $summary['collections'][$collection] = [
                'title' => $collection,
                'route' => $prepared['collections'][$collection]['route'],
                'entries' => $collection_entries,
                'duplicates' => $duplicates,
            ];
        }

        foreach ($prepared['terms'] as $taxonomy => $terms) {
            $taxonomy_terms = [];

            foreach ($terms as $slug => $term) {
                $taxonomy_terms[$slug] = [
                    'slug' => $slug,
                    'exists' => !!Term::query()->where('taxonomy', $taxonomy)->where('slug', $slug)->first(),
                    '_checked' => true,
                ];
            }

            $summary['taxonomies'][$taxonomy] = [
                'title' => $taxonomy,
                'route' => $prepared['taxonomies'][$taxonomy]['route'],
                'terms' => $taxonomy_terms,
                '_checked' => true,
            ];
        }

        return $summary;
    }

    public function import($prepared, $summary)
    {
        (new Migrator)->migrate($prepared, $summary);
    }
}
