import Importer from './components/Importer.vue'

Statamic.booting(() => {
    Statamic.$components.register('wp-importer', Importer)
})
