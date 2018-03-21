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
        store.state.app.reactiveProductList = wbwpf.reloadProductsListOnSubmit; //whether the product list must respond to filters changes
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
        //Re-bind ordering
        $("select.orderby").closest("form").removeClass("woocommerce-ordering").addClass("wbwpf-woocommerce-ordering");
        $("body").on( 'change', '.wbwpf-woocommerce-ordering select.orderby', function() {
            try{
                jQuery(window).trigger("orderingChanged",$(this).val());
            }catch(err){
                console.log(err);
            }
        });
        new Vue(jQuery.extend({ el: this.productsList, store },ProductsList));
    }
}

export {FiltersApp}