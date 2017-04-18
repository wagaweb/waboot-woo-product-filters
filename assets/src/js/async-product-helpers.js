import $ from "jquery";
import _ from 'lodash';

class ProductsManager{
    constructor(){
        this.total_products = 0;
        this.current_page = 1;
        this.showing_to = 0;
        this.showing_from = 0;
    }
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