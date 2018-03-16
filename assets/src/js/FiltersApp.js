import $ from "jquery";
import _ from "lodash";

import Vue from "vue";
import UriManager from "./uri-manager";
import FiltersList from './components/FiltersList.js'
import Product from './components/Product.js'
import ProductsList from './components/ProductsList.js'
import Pagination from './components/Pagination.js'
import InstancesStore from './InstancesStore.js';

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
        this.reactiveProductList = wbwpf.reloadProductsListOnSubmit; //whether the product list must respond to filters changes
        this.just_started = true;
    }

    /**
     * Startup the application
     *
     * @param {string} filtersList the vue root element of the filters list
     * @param {string} productsList the vue root element of the products list
     */
    start(filtersList,productsList){
        if(typeof filtersList !== "undefined"){
            this._startFiltersList(filtersList);
        }
        if(typeof productsList !== "undefined"){
            this._startProductsList(productsList);
        }
        $(window).trigger("filtersAppStarted");
    }

    /**
     * Startup the filters list vue instance
     * @param {string} el the root element
     */
    _startFiltersList(el){

        //Init a new Vue instance for the filters
        InstancesStore.setFiltersList(new Vue({
            el: el,
            components : {
                FiltersList
            }
        }));
    }

    /**
     * Startup the products list vue instance
     * @param {string} el the root element
     */
    _startProductsList(el){

        //Re-bind ordering
        $("select.orderby").closest("form").removeClass("woocommerce-ordering").addClass("wbwpf-woocommerce-ordering");
        $("body").on( 'change', '.wbwpf-woocommerce-ordering select.orderby', function() {
            try{
                InstancesStore.ProductsList().$emit("orderingChanged",$(this).val());
            }catch(err){
                console.log(err);
            }
        });

        Vue.component('wbwpf-product',Product);

        Vue.component('wbwpf-pagination',Pagination);

        InstancesStore.setProductsList(new Vue(jQuery.extend({ el: el },ProductsList)));
    }
}

export {FiltersApp}