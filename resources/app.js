import { vue3Debounce } from 'vue-debounce'

Spork.component('TrackButton', require('./TrackButton.vue').default)
Spork.component('knowledge', require('./Knowledge.vue').default)

Spork.setupStore({
    Wiretap: require("./store").default,
})

Spork.app.directive('debounce', vue3Debounce({ lock: true }))
