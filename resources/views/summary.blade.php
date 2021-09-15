@extends('statamic::layout')

@section('title', __('WP Import'))

@section('content')

    <header class="mb-3"><h1>WP Import - summary</h1></header>

    <div class="content">
        <script>
            window.ImportSummary = {!! json_encode($summary) !!};
        </script>

        <wp-importer inline-template token="{{ csrf_token() }}">
            <div>
                <template v-if="summary && !importing && !imported && !importFailed">
                    <div>
                        <div class="card mb-3" v-if="showPagesSection">
                            <h2>Pages</h2>
                            <div class="form-group">
                                <div class="py-1.5 px-2 text-sm w-full rounded-md bg-yellow border border-yellow-dark mb-3" role="alert" v-if="hasDuplicates(summary.pages)">
                                    Duplicate items found
                                    <span class="text-xs ml-1">(<a @click.prevent="uncheckDuplicates(summary.pages)" href="#" class="text-blue hover:underline">Uncheck duplicates</a>)</span>
                                </div>

                                <label>Entries</label>
                                <p>
                                    @{{ totalPages }} pages.
                                    <a @click="showAllPages = true" v-if="!showAllPages">Show</a>
                                    <a @click="showAllPages = false" v-else>Hide</a>
                                </p>

                                <div v-show="showAllPages">
                                    <a @click.prevent="uncheckAll(summary.pages)" href="#" class="text-xs text-blue hover:underline">Uncheck all</a> / <a @click.prevent="checkAll(summary.pages)" href="#" class="text-xs text-blue hover:underline">Check all</a>
                                    <table class="w-full mt-2 mb-4">
                                        <thead>
                                            <th class="text-left uppercase text-grey-60 text-xs px-2 py-1"></th>
                                            <th class="text-left uppercase text-grey-60 text-xs px-2 py-1">URL</th>
                                        </thead>
                                        <tbody class="border-t border-grey-60">
                                            <tr v-for="(page, i) in summary.pages">
                                                <td class="px-2 py-1 w-4" :class="{ 'bg-yellow': page.exists }">
                                                    <input type="checkbox" v-model="page._checked" id="page-@{{ i }}" />
                                                    <label for="page-@{{ i }}"></label>
                                                </td>
                                                <td class="px-2 py-1 text-xs" :class="{ 'bg-yellow': page.exists }">@{{ page.url }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div v-if="showCollectionsSection" v-for="(collection, collectionName) in summary.collections" class="card mb-3">
                            <h2>Collection: @{{ collectionName }}</h2>
                            <div class="form-group pb-0">
                                <label>Route</label>
                                <input type="text" v-model="collection.route" class="form-control" />
                            </div>
                            <div class="form-group">
                                <div class="py-1.5 px-2 text-sm w-full rounded-md bg-yellow border border-yellow-dark mb-3" role="alert" v-if="hasDuplicates(collection.entries)">
                                    Duplicate items found
                                    <span class="text-xs ml-1">(<a @click.prevent="uncheckDuplicates(collection.entries)" href="#" class="text-blue hover:underline">Uncheck duplicates</a>)</span>
                                </div>

                                <label>Entries</label>
                                <p>
                                    @{{ size(collection.entries) }} entries.
                                    <a href="#" @click.prevent="showCollection(collectionName)" v-if="!shouldShowCollection(collectionName)">Show</a>
                                    <a href="#" @click.prevent="hideCollection(collectionName)" v-else>Hide</a>
                                </p>

                                <div v-show="shouldShowCollection(collectionName)">
                                    <a @click.prevent="uncheckAll(collection.entries)" href="#" class="text-xs text-blue hover:underline">Uncheck all</a> / <a @click.prevent="checkAll(collection.entries)" href="#" class="text-xs text-blue hover:underline">Check all</a>
                                    <table class="w-full mt-2 mb-4">
                                        <thead>
                                            <th class="text-left uppercase text-grey-60 text-xs px-2 py-1"></th>
                                            <th class="text-left uppercase text-grey-60 text-xs px-2 py-1">Slug</th>
                                        </thead>
                                        <tbody class="border-t border-grey-60">
                                            <tr v-for="(entry, slug) in collection.entries">
                                                <td class="px-2 py-1 w-4" :class="{ 'bg-yellow': entry.exists }">
                                                    <input type="checkbox" v-model="entry._checked" id="c-@{{ collectionName }}-@{{ slug }}" />
                                                    <label for="c-@{{ collectionName }}-@{{ slug }}"></label>
                                                </td>
                                                <td class="px-2 py-1 text-xs" :class="{ 'bg-yellow': entry.exists }">@{{ entry.slug }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div v-if="showTaxonomiesSection" v-for="(taxonomy, taxonomyName) in summary.taxonomies" class="card mb-3">
                            <h2>Taxonomy: @{{ taxonomyName }}</h2>
                            <div class="form-group pb-0">
                                <label>Route</label>
                                <input type="text" v-model="taxonomy.route" class="form-control" />
                            </div>
                            <div class="form-group">
                                <div class="py-1.5 px-2 text-sm w-full rounded-md bg-yellow border border-yellow-dark mb-3" role="alert" v-if="hasDuplicates(taxonomy.terms)">
                                    Duplicate items found
                                    <span class="text-xs ml-1">(<a @click.prevent="uncheckDuplicates(taxonomy.terms)" href="#" class="text-blue hover:underline">Uncheck duplicates</a>)</span>
                                </div>
                                <label>Terms</label>
                                <p>
                                    @{{ size(taxonomy.terms) }} terms.
                                    <a href="#" @click.prevent="showTaxonomy(taxonomyName)" v-if="!shouldShowTaxonomy(taxonomyName)">Show</a>
                                    <a href="#" @click.prevent="hideTaxonomy(taxonomyName)" v-else>Hide</a>
                                </p>

                                <div v-show="shouldShowTaxonomy(taxonomyName)">
                                    <a @click.prevent="uncheckAll(taxonomy.terms)" href="#" class="text-xs text-blue hover:underline">Uncheck all</a> / <a @click.prevent="checkAll(taxonomy.terms)" href="#" class="text-xs text-blue hover:underline">Check all</a>
                                    <table class="w-full mt-2 mb-4">
                                        <thead>
                                            <th class="text-left uppercase text-grey-60 text-xs px-2 py-1 w-4"></th>
                                            <th class="text-left uppercase text-grey-60 text-xs px-2 py-1">Slug</th>
                                        </thead>
                                        <tbody class="border-t border-grey-60">
                                            <tr v-for="(term, slug) in taxonomy.terms">
                                                <td class="px-2 py-1 w-4" :class="{ 'bg-yellow': term.exists }">
                                                    <input type="checkbox" v-model="term._checked" id="t-@{{ taxonomyName }}-@{{ slug }}" />
                                                    <label for="t-@{{ taxonomyName }}-@{{ slug }}"></label>
                                                </td>
                                                <td class="px-2 py-1 text-xs" :class="{ 'bg-yellow': term.exists }">@{{ term.slug }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <button class="btn btn-primary" @click.prevent="startImport">Import</button>
                        </div>
                    </div>
                </template>

                <template v-if="importing">
                    <div>
                        <div class="card mb-3">
                            <h2 class="mb-2">Importing</h2>
                            <div class="w-full flex justify-between items-center">
                                <div class="loading loading-basic">
                                    <span class="icon icon-circular-graph animation-spin"></span> Please wait
                                </div>
                                <div class="flex text-grey-50 text-sm">
                                    <div v-show="hours > 0">@{{ hours }}</div>
                                    <div v-show="hours > 0">:</div>
                                    <div>@{{ minutes }}</div>
                                    <div>:</div>
                                    <div>@{{ seconds }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template v-if="imported">
                    <div>
                        <div class="card mb-3">
                            <h2 class="mb-2">Import complete</h2>
                            <div class="w-full flex justify-between items-center">
                                <p>Import has completed</p>
                                <div class="flex text-grey-50 text-sm">
                                    <div v-show="hours > 0">@{{ hours }}</div>
                                    <div v-show="hours > 0">:</div>
                                    <div>@{{ minutes }}</div>
                                    <div>:</div>
                                    <div>@{{ seconds }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template v-if="importFailed">
                    <div>
                        <div class="card">
                            <h2 class="mb-2">Import failed</h2>
                            <div class="w-full flex justify-between items-center">
                                <p>@{{ importError }}</p>
                                <div class="flex text-grey-50 text-sm">
                                    <div v-show="hours > 0">@{{ hours }}</div>
                                    <div v-show="hours > 0">:</div>
                                    <div>@{{ minutes }}</div>
                                    <div>:</div>
                                    <div>@{{ seconds }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </wp-importer>
    </div>

@endsection