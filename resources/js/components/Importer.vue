<script>
export default {
  props: {
    token: String,
  },

  data: function () {
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
      counter: null,
      hours: 0,
      minutes: 0,
      seconds: 0,
    };
  },

  computed: {
    totalPages: function () {
      return this.summary.pages && Object.keys(this.summary.pages).length;
    },
    showPagesSection: function () {
      return (
        this.summary &&
        this.summary.pages &&
        !this.importing &&
        !this.imported &&
        !this.importFailed
      );
    },
    showCollectionsSection: function () {
      return this.summary && this.summary.collections;
    },
    showTaxonomiesSection: function () {
      return this.summary && this.summary.taxonomies;
    },
    totalEntries: function () {
      return this.calculateTotalEntries();
    },
  },

  mounted() {
    this.summary = window.ImportSummary;
  },

  methods: {
    startImport: function () {
      this.importing = true;
      this.imported = false;
      this.importFailed = false;
      this.importError = null;

      this.$progress.start("wp-import");

      this.startTimer();

      fetch(cp_url("wp-import/import"), {
        method: "POST",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          _token: this.token,
          summary: this.summary,
        }),
      })
        .then((response) => {
          this.importing = false;

          if (response.ok) {
            this.imported = true;
          } else {
            this.importFailed = true;
            this.importError =
              response.statusText + " (" + response.status + ")";
          }

          this.$progress.complete("wp-import");

          this.stopTimer();

          return response.json();
        })
        .then((data) => {
          console.log(data);
        });
    },

    hasDuplicates(collection) {
      return !!this.duplicateCount(collection);
    },

    duplicateCount: function (items = {}) {
      let count = 0;

      items = Object.values(items);

      if (!items || !Array.isArray(items)) return count;

      items.forEach((item) => {
        if (!item.exists) {
          return;
        }

        count++;
      });

      return count;
    },

    uncheckDuplicates: function (items = {}) {
      items = Object.values(items);
      if (!items.length || !Array.isArray(items)) return;

      items.forEach((item) => {
        if (!item.exists) {
          return;
        }

        item._checked = false;
      });
    },

    uncheckAll: function (items = {}) {
      items = Object.values(items);
      if (!items.length || !Array.isArray(items)) return;
      items.forEach((item) => {
        item._checked = false;
      });
    },

    checkAll: function (items = {}) {
      items = Object.values(items);
      if (!items.length || !Array.isArray(items)) return;
      items.forEach((item) => {
        item._checked = true;
      });
    },

    size: function (obj) {
      return Object.keys(obj).length;
    },

    showCollection: function (collection) {
      this.showCollections.push(collection);
      this.showCollections = [...new Set(this.showCollections)];
    },

    hideCollection: function (hidden) {
      this.showCollections = this.showCollections.filter((c) => {
        return c !== hidden;
      });
    },

    shouldShowCollection: function (collection) {
      return this.showCollections.includes(collection);
    },

    showTaxonomy: function (taxonomy) {
      this.showTaxonomies.push(taxonomy);
      this.showTaxonomies = [...new Set(this.showTaxonomies)];
    },

    hideTaxonomy: function (hidden) {
      this.showTaxonomies = this.showTaxonomies.filter((t) => {
        return t !== hidden;
      });
    },

    shouldShowTaxonomy: function (taxonomy) {
      return this.showTaxonomies.includes(taxonomy);
    },

    calculateTotalEntries: function () {
      let totalEntries = 0;

      if (this.summary.pages) {
        Object.keys(this.summary.pages).forEach((key) => {
          if (this.summary.pages[key]["_checked"]) {
            totalEntries++;
          }
        });
      }

      if (this.summary.collections) {
        Object.keys(this.summary.collections).forEach((key) => {
          Object.keys(this.summary.collections[key]["entries"]).forEach(
            (entry) => {
              if (this.summary.collections[key]["entries"][entry]["_checked"]) {
                totalEntries++;
              }
            }
          );
        });
      }

      if (this.summary.taxonomies) {
        Object.keys(this.summary.taxonomies).forEach((key) => {
          Object.keys(this.summary.taxonomies[key]["terms"]).forEach(
            (entry) => {
              if (this.summary.taxonomies[key]["terms"][entry]["_checked"]) {
                totalEntries++;
              }
            }
          );
        });
      }

      return totalEntries;
    },

    startTimer() {
      this.minutes = this.checkSingleDigit(0);
      this.seconds = this.checkSingleDigit(0);

      this.counter = setInterval(() => {
        const date = new Date(
          0,
          0,
          0,
          parseInt(this.hours),
          parseInt(this.minutes),
          parseInt(this.seconds) + 1
        );
        this.hours = date.getHours();
        this.minutes = this.checkSingleDigit(date.getMinutes());
        this.seconds = this.checkSingleDigit(date.getSeconds());
      }, 1000);
    },

    stopTimer() {
      clearInterval(this.counter);
    },

    checkSingleDigit(digit) {
      return ("0" + digit).slice(-2);
    },
  },
};
</script>
