import $ from "jquery";
import _ from "lodash";

import Vue from "vue";

import { getQueryString, getSearchParameters, getFiltersSearchParameters, getStrippedFiltersSearchParameters } from './utilities';
import "./jquery_addons";

import {Filter,FilterController} from './async-filter';

class WooCommerce_Ordering_Form_Injection{
    /**
     * Class constructor
     * @param $form_el
     */
    constructor($form_el){
        this.$form_el = $form_el;
        this.inject_inputs();
    }

    /**
     * Injects current filters data (obtained from query string) into the woocommerce ordering form
     */
    inject_inputs(){
        let current_query = getFiltersSearchParameters(),
            tpl = _.template("<input type='hidden' name='<%= name %>' value='<%= value %>'>");

        if(!_.isEmpty(current_query)){
            _.each(current_query,(value,key,list) => {
                let new_el = tpl({
                    name: key,
                    value: value
                });
                let $new_el = $(new_el);
                this.$form_el.append($new_el);
            });
        }
    }
}

$(document).ready(function($){
    let $wc_ordering = $("form.woocommerce-ordering"),
        $async_filters = $(".wbwpf-filters[data-async]");

    if($wc_ordering.length > 0){
        new WooCommerce_Ordering_Form_Injection($wc_ordering);
    }

    if($async_filters.length > 0){
        //Init a new Vue instance for the filters
        window.FiltersApp = new Vue({
            el: ".wbwpf-filters[data-async]"
        });
    }
});