import $ from "jquery";
import _ from 'lodash';

class ProductsManager{
    getProducts(filters,ordering,page){
        if(typeof ordering === "undefined") ordering = "menu_order";
        if(typeof page === "undefined") page = 1;

        return $.ajax({
            url: wbwpf.ajax_url,
            data: {
                action: "get_products_for_filters",
                filters: filters,
                ordering: ordering,
                page: page
            },
            method: "POST",
            dataType: "json"
        });
    }
}


export { ProductsManager };