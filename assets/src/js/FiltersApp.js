import $ from "jquery";

import Vue from "vue";
import Vuex from 'vuex';
import store from './store/index.js';
import FiltersList from './components/FiltersList.js'
import ProductsList from './components/ProductsList.js'

Vue.use(Vuex);

class FiltersApp{
    /**
     * Build up the application
     *
     * @param {string} filtersList the vue root element of the filters list
     * @param {string} productsList the vue root element of the products list
     */
    constructor(filtersList,productsList){
        this.filtersList = filtersList;
        this.productsList = productsList;
        if(wbwpf.components.filtersList.reloadProductsListOnSubmit){
            store.commit('appHasReactiveProductList'); //whether the product list must respond to filters changes
        }
        this.start();
    }

    /**
     * Startup the application
     */
    start(){
        if(typeof this.filtersList !== "undefined"){
            this._startFiltersList(this.filtersList);
        }
        if(typeof this.productsList !== "undefined"){
            this._startProductsList(this.productsList);
        }
        $(window).trigger("filtersAppStarted");
    }

    /**
     * Startup the filters list vue instance
     */
    _startFiltersList(){
        //Init a new Vue instance for the filters
        new Vue({
            el: this.filtersList,
            store,
            components : {
                FiltersList
            }
        });
    }

    /**
     * Startup the products list vue instance
     */
    _startProductsList(){
        new Vue({
            el: this.productsList,
            store,
            components: {
                ProductsList
            }
        })
    }
}

export {FiltersApp}