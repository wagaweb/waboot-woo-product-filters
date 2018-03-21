import Vue from 'vue'
import Vuex from 'vuex'
import app from './modules/app.js'
import filters from './modules/filters.js'
import products from './modules/products.js'

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        app,
        filters,
        products
    },
})