import $ from "jquery";
import _ from "lodash";

import Vue from "vue";
import UriManager from "./uri-manager";
import Filter from './components/Filter.js'
import FiltersList from './components/FiltersList.js'
import Product from './components/Product.js'
import ProductsList from './components/ProductsList.js'
import Pagination from './components/Pagination.js'

class FiltersApp{
    /**
     * Build up the application
     *
     * @param {FiltersManager} fm
     * @param {ProductsManager} pm
     */
    constructor(fm,pm){
        this.FiltersManager = fm;
        if(typeof pm !== "undefined"){
            this.ProductManager = pm;
        }
        this.UriManager = new UriManager();
        this.reactiveProductList = false; //whether the product list must respond to filters changes
        this.just_started = true;
    }

    /**
     * Startup the application
     *
     * @param {string} filtersList the vue root element of the filters list
     * @param {string} productsList the vue root element of the products list
     * @param {boolean} reactiveProductList whether the product list must respond to filters changes
     */
    start(filtersList,productsList,reactiveProductList){
        if(_.isUndefined(reactiveProductList)){
            reactiveProductList = this.reactiveProductList; //set to the default
        }
        if(typeof filtersList !== "undefined"){
            this._startFiltersList(filtersList);
        }
        if(typeof productsList !== "undefined"){
            this.reactiveProductList = reactiveProductList;
            this._startProductsList(productsList);
        }
        $(window).trigger("filtersAppStarted");
    }

    /**
     * Startup the filters list vue instance
     * @param {string} el the root element
     */
    _startFiltersList(el){

        Vue.component("wbwpf-filter",Filter);

        //Init a new Vue instance for the filters
        window.FiltersList = new Vue(jQuery.extend({ el: el },FiltersList));
    }

    /**
     * Startup the products list vue instance
     * @param {string} el the root element
     */
    _startProductsList(el){

        //Re-bind ordering
        $("select.orderby").closest("form").removeClass("woocommerce-ordering").addClass("wbwpf-woocommerce-ordering");
        $("body").on( 'change', '.wbwpf-woocommerce-ordering select.orderby', function() {
            if(window.ProductList !== "undefined"){
                window.ProductList.$emit("orderingChanged",$(this).val());
            }
        });

        Vue.component('wbwpf-product',Product);

        Vue.component('wbwpf-pagination',Pagination);

        window.ProductList = new Vue(jQuery.extend({ el: el },ProductsList));
    }
}

export {FiltersApp}