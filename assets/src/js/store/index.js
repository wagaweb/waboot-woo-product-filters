import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

export default new Vuex.store({
    state: {
        activeFilters: []
    },
    mutations: {
        updateFilter(state,payload){

        },
        removeFilter(state,slug){

        }
    },
    getters: {
        filters(){

        }
    },
    actions: {
        getFilterValues({ commit, state}, slug){

        }
    }
})