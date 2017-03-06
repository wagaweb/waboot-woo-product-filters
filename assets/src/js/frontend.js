import $ from "jquery";
import _ from "underscore";
import { getQueryString, getSearchParameters, getFiltersSearchParameters, getStrippedFiltersSearchParameters } from './utilities';
import "./jquery_addons";

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
    let $wc_ordering = $("form.woocommerce-ordering");

    if($wc_ordering.length > 0){
        new WooCommerce_Ordering_Form_Injection($wc_ordering);
    }
});