import {getPageParameter} from "../utilities";

export default {
    data: {
        products: [],
        current_page: 1,
        total_pages: 1,
        ordering: jQuery("select.orderby").val() || "menu_order", //This is a nasty nasty trick to make ordering works without further modifications
        result_count_label: ""
    },
    created(){},
    mounted(){
        //Getting the current products
        this.updateProducts(window.FiltersApp.FiltersManager.getFilters());
        if(window.FiltersApp.reactiveProductList){
            //Listen to filters changes:
            window.FiltersList.$on("filtersUpdated",function(){
                window.ProductList.current_page = 1; //Reset the page when filters are updated
                window.ProductList.updateProducts(window.FiltersApp.FiltersManager.getFilters(),false);
            });
        }
        //Listen to ordering changing. This is emitted by jQuery click event.
        this.$on("orderingChanged", function(new_order){
            this.ordering = new_order;
            window.ProductList.updateProducts(window.FiltersApp.FiltersManager.getFilters());
        });
        //Listen to page changing. This is emitted by <wbwpf-pagination> component.
        this.$on('pageChanged', function(new_page){
            this.current_page = new_page;
            window.ProductList.updateProducts(window.FiltersApp.FiltersManager.getFilters(),false);
        });
        this.updateCurrentPageFromUri();
    },
    methods: {
        /**
         * Update the current page from URI
         */
        updateCurrentPageFromUri(){
            let uriPage = getPageParameter();
            if(parseInt(uriPage) > 1){
                this.current_page = uriPage;
            }
        },
        /**
         * Update the current product list via ajax
         * @param {array} currentFilters
         * @param {boolean} get_page_from_uri
         */
        updateProducts(currentFilters,get_page_from_uri = true){
            if(get_page_from_uri){
                this.updateCurrentPageFromUri();
            }
            let self = this,
                req = window.FiltersApp.ProductManager.getProducts(currentFilters,this.ordering,this.current_page);
            req.then((response, textStatus, jqXHR) => {
                //Resolve

                //Update app:
                window.FiltersApp.total_products = response.data.found_products;
                window.FiltersApp.total_pages = response.data.total_pages;
                window.FiltersApp.current_page = response.data.current_page;
                window.FiltersApp.showing_from = response.data.showing_from;
                window.FiltersApp.showing_to = response.data.showing_to;

                //Update self:
                self.products = response.data.products;
                self.current_page = response.data.current_page;
                self.total_pages = response.data.total_pages;
                self.result_count_label = response.data.result_count_label;

                //Update URI:
                if(!window.FiltersApp.just_started){
                    window.FiltersApp.UriManager.updateFilters(window.FiltersApp.FiltersManager.getFilters(),self.current_page);
                }
                jQuery(window).trigger("filteredProductsUpdated");
            },(jqXHR, textStatus, errorThrown) => {
                //Reject
                self.products = [];
            });
        }
    }
}