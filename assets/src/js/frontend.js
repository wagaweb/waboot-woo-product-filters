import $ from "jquery";
import _ from "lodash";
import { getQueryString, getSearchParameters, getFiltersSearchParameters, getStrippedFiltersSearchParameters } from './utilities';
import "./jquery_addons";
import {FiltersApp} from './FiltersApp';
import {FiltersManager,ProductsManager} from "./Helpers.js";
import InstancesStore from './InstancesStore.js';
import ConfigsStore from './ConfigsStore.js';

$(document).ready(function($){
    "use strict";
    let $wc_ordering = $("form.woocommerce-ordering"),
        $async_product_list = $(ConfigsStore.productsListSelector);

    if($wc_ordering.length > 0 && $async_product_list.length === 0){
        inject_woocommerce_ordering($wc_ordering);
    }

    filters_app_startup();
    $(window).on("startupFiltersApp",function(){
        filters_app_startup();
    });
});

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
    let $async_filters = $(ConfigsStore.filtersListSelector),
        $async_product_list = $(ConfigsStore.productsListSelector);

    if($async_filters.length > 0){
        if($async_product_list.length > 0){
            new FiltersApp(ConfigsStore.filtersListSelector,ConfigsStore.productsListSelector);
        }else{
            new FiltersApp(ConfigsStore.filtersListSelector);
        }
    }
}