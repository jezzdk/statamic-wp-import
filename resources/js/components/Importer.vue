<script>
import _ from 'lodash';

export default {
    props: {
        token: String
    },

    data: function() {
        return {
            exporting: false,
            exported: false,
            exportFailed: false,
            exportError: null,
            importing: false,
            imported: false,
            importFailed: false,
            importError: null,
            summary: null,
            showAllPages: false,
            showCollections: [],
            showTaxonomies: [],
        }
    },

    computed: {
        totalPages: function () {
            return this.summary.pages && Object.keys(this.summary.pages).length;
        },
        showPagesSection: function () {
            return this.summary && this.summary.pages && !this.importing && !this.imported && !this.importFailed
        },
        showCollectionsSection: function () {
            return this.summary && this.summary.collections
        },
        showTaxonomiesSection: function () {
            return this.summary && this.summary.taxonomies
        },
        totalEntries: function () {
            return this.calculateTotalEntries()
        }
    },

    mounted() {
        this.summary = window.ImportSummary
    },

    methods: {

        startImport: function () {
            this.importing = true
            this.imported = false
            this.importFailed = false
            this.importError = null

            this.$progress.start('wp-import')

            fetch(cp_url('wp-import/import'), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    _token: this.token,
                    summary: this.summary
                })
            }).then((response) => {
                this.importing = false;

                if (response.ok) {
                    this.imported = true;
                }
                else {
                    this.importFailed = true;
                    this.importError = response.statusText + ' (' + response.status + ')';
                }

                this.$progress.complete('wp-import')

                return response.json();
            }).then((data) => {
                console.log(data)
            });
        },

        hasDuplicates (collection) {
            return !! this.duplicateCount(collection);
        },

        duplicateCount: function (items) {
            let count = 0;

            _.each(items, (item) => {
                if (! item.exists) {
                    return;
                }

                count++;
            });

            return count;
        },

        uncheckDuplicates: function(items) {
            _.each(items, (item) => {
                if (! item.exists) {
                    return;
                }

                item._checked = false;
            });
        },

        uncheckAll: function(items) {
            _.each(items, (item) => {
                item._checked = false;
            });
        },

        checkAll: function(items) {
            _.each(items, (item) => {
                item._checked = true;
            });
        },

        size: function (obj) {
            return _.size(obj);
        },

        showCollection: function (collection) {
            this.showCollections.push(collection);
            _.uniq(this.showCollections);
        },

        hideCollection: function (hidden) {
            this.showCollections = _.reject(this.showCollections, function (c) {
                return c === hidden;
            })
        },

        shouldShowCollection: function (collection) {
            return _.includes(this.showCollections, collection);
        },

        showTaxonomy: function (taxonomy) {
            this.showTaxonomies.push(taxonomy);
            _.uniq(this.showTaxonomies);
        },

        hideTaxonomy: function (hidden) {
            this.showTaxonomies = _.reject(this.showTaxonomies, function (t) {
                return t === hidden;
            })
        },

        shouldShowTaxonomy: function (taxonomy) {
            return _.includes(this.showTaxonomies, taxonomy);
        },

        calculateTotalEntries: function () {
            let totalEntries = 0

            if (this.summary.pages) {
                Object.keys(this.summary.pages).forEach(key => {
                    if (this.summary.pages[key]['_checked']) {
                        totalEntries++
                    }
                })
            }

            if (this.summary.collections) {
                Object.keys(this.summary.collections).forEach(key => {
                    Object.keys(this.summary.collections[key]['entries']).forEach(entry => {
                        if (this.summary.collections[key]['entries'][entry]['_checked']) {
                            totalEntries++
                        }
                    })
                })
            }

            if (this.summary.taxonomies) {
                Object.keys(this.summary.taxonomies).forEach(key => {
                    Object.keys(this.summary.taxonomies[key]['terms']).forEach(entry => {
                        if (this.summary.taxonomies[key]['terms'][entry]['_checked']) {
                            totalEntries++
                        }
                    })
                })
            }

            return totalEntries
        }
    }
};
</script>