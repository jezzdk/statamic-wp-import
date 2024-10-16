<?php

namespace RadPack\StatamicWpImport\Helpers;

use Statamic\Facades\URL;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Preparer
{
    private $data;

    private $migration = [];

    public function prepare(array $data)
    {
        $this->data = $data;

        if (!isset($this->data['pages']) || !is_array($this->data['pages'])) {
            $this->data['pages'] = [];
        }

        ksort($this->data['pages']);

        $this->migration = [
            'taxonomies' => collect(),
            'terms' => collect(),
            'collections' => collect(),
            'entries' => collect(),
            'pages' => collect($this->data['pages']),
        ];

        $this->createTaxonomies();
        $this->createCollections();

        if (config('statamic-wp-import.exclude_underscore_data')) {
            $this->filterMetaData();
        }

        return $this->migration;
    }

    protected function filterMetaData()
    {
        $this->migration['entries'] = $this->migration['entries']->map(function ($collection) {
            return $collection->map(function ($entry, $slug) {
                $entry['data'] = collect($entry['data'])->filter(function ($value, $key) {
                    return substr($key, 0, 1) != '_';
                })->toArray();

                return $entry;
            });
        });
    }

    private function createTaxonomies()
    {
        if (!isset($this->data['taxonomies'])) {
            return;
        }

        foreach ($this->data['taxonomies'] as $taxonomy_name => $terms) {
            $this->migration['taxonomies']->put($taxonomy_name, [
                'title' => Str::title($taxonomy_name),
                'route' => '/' . $taxonomy_name . '/{slug}',
            ]);

            $this->migration['terms']->put($taxonomy_name, collect());

            foreach ($terms as $slug => $term_data) {
                // Older versions of the importer saved the slugs of the terms to the json.
                // We longer need to do that. This can be removed, but keeping it here
                // for temporary backwards compatibility.
                if (is_string($term_data)) {
                    continue;
                }

                $this->migration['terms'][$taxonomy_name]->put($slug, $term_data);
            }
        }
    }

    private function createCollections()
    {
        if (!isset($this->data['collections'])) {
            return;
        }

        foreach (Arr::get($this->data, 'collections', []) as $name => $entries) {
            $this->createCollection($name, $entries);
            $this->createEntries($name, $entries);
        }
    }

    /**
     * Create a collection
     *
     * @param  string  $collection
     * @param  array  $entries
     * @return void
     */
    private function createCollection($collection, $entries)
    {
        $route = '/' . $collection . '/{slug}';

        $collection = str_replace('/', '-', $collection);

        $entry = reset($entries);

        $order = $entry['order'];
        if (is_string($order)) {
            $type = 'date';
        } elseif (is_int($order)) {
            $type = 'number';
        } else {
            $type = 'alphabetical';
        }

        $this->migration['collections']->put($collection, [
            'order' => $type,
            'route' => $route,
        ]);

        $this->migration['entries']->put($collection, collect());
    }

    /**
     * Create the entries in a collection
     *
     * @param  string  $collection
     * @param  array  $entries
     * @return void
     */
    private function createEntries($collection, $entries)
    {
        foreach ($entries as $url => $data) {
            $slug = URL::slug($url);

            $this->migration['entries'][str_replace('/', '-', $collection)]->put($slug, $data);
        }
    }

    /**
     * Replace slugs in taxonomy fields with their IDs
     *
     * @param  array  $data  The array of data to modify
     * @return array The modified array
     */
    private function replaceTaxonomies($data)
    {
        foreach ($data as $field_name => &$value) {
            if (!$this->isTaxonomyField($field_name)) {
                continue;
            }

            $is_string = false;
            if (is_string($value)) {
                $is_string = true;
                $value = [$value];
            }

            foreach ($value as $i => $slug) {
                // Replace the slug with the ID. If it's not found for whatever reason,
                // we'll just leave the slug as-is.
                $value[$i] = Arr::get($this->migration['terms'][$field_name]->get($slug), 'id', $slug);
            }

            if ($is_string) {
                $value = reset($value);
            }
        }

        return $data;
    }

    /**
     * Is a given $key a taxonomy field name?
     *
     * @param  string  $key
     * @return bool
     */
    private function isTaxonomyField($key)
    {
        return $this->migration['taxonomies']->has($key);
    }
}
