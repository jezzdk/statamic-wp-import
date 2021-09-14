<?php

namespace Jezzdk\StatamicWpImport\Helpers;

use Exception;
use Illuminate\Support\Facades\Storage;
use Statamic\Assets\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Statamic\Facades\Term;
use Statamic\Facades\Taxonomy;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Migrator
{
    /**
     * The migration array
     *
     * @var array
     */
    private $migration;

    /**
     * The summary array
     *
     * @var array
     */
    private $summary;

    /**
     * Perform the migration
     *
     * @param $migration
     * @param $summary
     */
    public function migrate($migration, $summary)
    {
        $this->migration = $this->prepareMigration($migration);
        $this->summary = $summary;

        $this->createTaxonomies();
        $this->createTaxonomyTerms();

        $this->createCollections();
        $this->createEntries();

        $this->createPages();

        Stache::clear();
    }

    /**
     * Prepare the migration
     *
     * @param array $migration
     * @return array
     */
    private function prepareMigration($migration)
    {
        $migration['pages'] = collect(
            $this->sortDeepest(
                array_get($migration, 'pages', [])->all()
            )
        );

        return $migration;
    }

    /**
     * Sort an array by folder depth (amount of slashes)
     *
     * @param  array $arr An array with paths for keys
     * @return array      The sorted array
     */
    private function sortDeepest($arr)
    {
        uksort($arr, function ($a, $b) {
            return (substr_count($a, '/') >= substr_count($b, '/')) ? 1 : -1;
        });

        // Move homepage to top
        if (isset($arr['/'])) {
            $arr = ['/' => $arr['/']] + $arr;
        }

        return $arr;
    }

    /**
     * Create taxonomies
     *
     * @return void
     */
    private function createTaxonomies()
    {
        foreach (array_get($this->migration, 'taxonomies', []) as $taxonomy_slug => $taxonomy_data) {
            $taxonomy = Taxonomy::findByHandle($taxonomy_slug);

            if (!$taxonomy) {
                $taxonomy = Taxonomy::make($taxonomy_slug);
            }

            $taxonomy->data($taxonomy_data);

            $taxonomy->save();
        }
    }

    /**
     * Create taxonomy terms
     *
     * @return void
     */
    private function createTaxonomyTerms()
    {
        foreach (array_get($this->migration, 'terms', []) as $taxonomy_slug => $terms) {
            foreach ($terms as $term_slug => $term_data) {
                // Skip if this term was not checked in the summary.
                if (!$this->summary['taxonomies'][$taxonomy_slug]['terms'][$term_slug]['_checked']) {
                    continue;
                }

                $term = Term::findBySlug($term_slug, $taxonomy_slug);

                if (!$term) {
                    $term = Term::make($term_slug)->taxonomy($taxonomy_slug);
                }

                $term->data($term_data)->save();
            }
        }
    }

    /**
     * Create collections
     *
     * @return void
     */
    private function createCollections()
    {
        foreach (array_get($this->migration, 'collections', []) as $handle => $data) {
            $collection = Collection::findByHandle($handle);

            if (!$collection) {
                $collection = Collection::make($handle);
            }

            $collection->dated(true);
            $collection->sortDirection('desc');
            $collection->futureDateBehavior('private');
            $collection->pastDateBehavior('public');
            $collection->save();
        }
    }

    /**
     * Create entries
     *
     * @return void
     */
    private function createEntries()
    {
        foreach ($this->migration['entries'] as $collection => $entries) {
            foreach ($entries as $slug => $meta) {
                // Skip if this entry was not checked in the summary.
                if (!$this->summary['collections'][$collection]['entries'][$slug]['_checked']) {
                    continue;
                }

                $entry = Entry::query()->where('collection', $collection)->where('slug', $slug)->first();

                if (!$entry) {
                    $entry = Entry::make()->collection($collection)->slug($slug);
                }

                $entry->date($meta['order']);
                $entry->data(array_merge($meta['data'], [
                    'slug' => $slug
                ]));

                if (config('statamic-wp-import.download_images')) {
                    $asset = $this->downloadAsset($meta['data']['featured_image_url'] ?? '', $collection, $slug);

                    if ($asset) {
                        $entry->set('featured_image', $asset->path());
                    }
                }

                $entry->save();
            }
        }
    }

    /**
     * Create pages
     *
     * @return void
     */
    private function createPages()
    {
        foreach ($this->migration['pages'] as $url => $meta) {
            // Skip if this page was not checked in the summary.
            if (!$this->summary['pages'][$url]['_checked']) {
                continue;
            }

            $urlParts = explode('/', $url);
            $slug = array_pop($urlParts);

            $page = Entry::query()->where('collection', 'pages')->where('slug', $slug)->first();

            if (!$page) {
                $page = Entry::make()->collection('pages')->slug($slug);
            }

            $page->data(array_merge($meta['data'], [
                'slug' => $slug
            ]));

            if (config('statamic-wp-import.download_images')) {
                $asset = $this->downloadAsset($meta['data']['featured_image_url'] ?? '', 'pages', $slug);

                if ($asset) {
                    $page->set('featured_image', $asset->path());
                }
            }

            $page->save();
        }
    }

    /**
     * Create an asset from a URL
     *
     * @param string|null $url
     * @return Asset|bool
     */
    private function downloadAsset(string $url = null, string $collection, string $slug): Asset|bool
    {
        if (!$url) {
            return false;
        }

        try {
            $image = @file_get_contents($url);
            $originalImageName = basename($url);

            Storage::put($tempFile = 'temp', $image);

            $assetContainer = AssetContainer::findByHandle('assets');

            $asset = $assetContainer->makeAsset("{$collection}/{$slug}/{$originalImageName}")
                ->upload(
                    new UploadedFile(
                        Storage::path($tempFile),
                        $originalImageName,
                    )
                );

            $asset->save();

            return $asset;
        } catch (Exception $e) {
            return false;
        }
    }
}
