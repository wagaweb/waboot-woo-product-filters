import $ from "jquery";
import _ from "lodash";

import Vue from "vue";

import { getQueryString, getSearchParameters, getFiltersSearchParameters, getStrippedFiltersSearchParameters } from './utilities';
import "./jquery_addons";

import {FiltersApp} from './async-filters';
import {FiltersManager} from "./async-filter-helpers";
import {ProductsManager} from "./async-product-helpers";

/**
 * Injects current filters data (obtained from query string) into the woocommerce ordering form
 */
function inject_woocommerce_ordering($form_el){
    "use strict";
    let current_query = getFiltersSearchParameters(),
        tpl = _.template("<input type='hidden' name='<%= name %>' value='<%= value %>'>");

    if(!_.isEmpty(current_query)){
        _.each(current_query,(value,key,list) => {
            let new_el = tpl({
                name: key,
                value: value
            });
            let $new_el = $(new_el);
            $form_el.append($new_el);
        });
    }
}

/**
 * Initialize the filters vue application
 */
function filters_app_startup(){
    "use strict";
    let $async_filters = $(".wbwpf-filters[data-async]"),
        $async_product_list = $(".wbwpf-product-list[data-async]");

    if($async_filters.length > 0){
        if($async_product_list.length > 0){
            let fm = new FiltersManager(),
                pm = new ProductsManager();
            window.FiltersApp = new FiltersApp(fm,pm);
            if($async_filters.data("has_button")){
                window.FiltersApp.start(".wbwpf-filters[data-async]",".wbwpf-product-list[data-async]",false);
            }else{
                window.FiltersApp.start(".wbwpf-filters[data-async]",".wbwpf-product-list[data-async]",true);
            }
        }else{
            let fm = new FiltersManager();
            window.FiltersApp = new FiltersApp(fm);
            window.FiltersApp.start(".wbwpf-filters[data-async]")
        }
    }
}

$(document).ready(function($){
    "use strict";
    let $wc_ordering = $("form.woocommerce-ordering"),
        $async_product_list = $(".wbwpf-product-list[data-async]");

    if($wc_ordering.length > 0 && $async_product_list.length === 0){
        inject_woocommerce_ordering($wc_ordering);
    }

    filters_app_startup();
    $(window).on("startupFiltersApp",function(){
        filters_app_startup();
    });
});