import $ from "jquery";
import _ from 'lodash';

class ProductsManager{
    getProducts(filters){
        return $.ajax({
            url: wbwpf.ajax_url,
            data: {
                action: "get_products_for_filters",
                filters: filters
            },
            method: "POST",
            dataType: "json"
        });
    }
}


export { ProductsManager }