import {getPageParameter} from "../utilities";
import UriManager from "../uri-manager.js";
import Product from './Product.js'
import Pagination from './Pagination.js'

export default {
    components: {
        'wbwpf-product': Product,
        'wbwpf-pagination': Pagination
    },
    data() {
        return {
            products: [],
            ordering: "menu_order",
            result_count_label: ""
        }
    },
    computed: {
        current_page: function(){
            return this.$store.state.products.current_page;
        },
        total_pages: function(){
            return this.$store.state.products.total_pages;
        },
        currentFilters: function(){
            return this.$store.getters.filters;
        }
    },
    watch: {
        currentFilters: function(){
            if(this.$store.state.app.reactiveProductList){
                this.$store.commit('setCurrentPage',1); //Reset the page when filters are updated
                this.updateProducts(this.currentFilters,false);
            }
        },
        current_page: function(){
            this.updateProducts(this.currentFilters,false);
        },
        ordering: function(){
            this.updateProducts(this.currentFilters);
        }
    },
    created(){
        //Getting the current products
        this.updateProducts(this.currentFilters);
    },
    mounted(){
        try{
            let $orderby = jQuery("select.orderby");
            if($orderby.length > 0){
                this.ordering = $orderby.val();
            }
            //Re-bind ordering
            $orderby.closest("form").removeClass("woocommerce-ordering").addClass("wbwpf-woocommerce-ordering");
            jQuery("body").on( 'change', '.wbwpf-woocommerce-ordering select.orderby', (e) => {
                try{
                    let newOrdering = jQuery(e.currentTarget).val();
                    jQuery(window).trigger("orderingChanged",newOrdering);
                    this.ordering = newOrdering;
                }catch(err){
                    console.log(err);
                }
            });
            this.updateCurrentPageFromUri();
        }catch(err){
            console.log(err);
        }
    },
    methods: {
        /**
         * Gets a products request
         * @param payload
         * @returns {Promise}
         */
        getProductsRequest(payload){
            if(typeof payload.ordering === "undefined") payload.ordering = "menu_order";
            if(typeof payload.page === "undefined") payload.page = 1;

            return jQuery.ajax({
                url: wbwpf.ajax_url,
                data: {
                    action: "get_products_for_filters",
                    filters: payload.filters,
                    ordering: payload.ordering,
                    page: payload.page
                },
                method: "POST",
                dataType: "json"
            });
        },
        /**
         * Update the current page from URI
         */
        updateCurrentPageFromUri(){
            let uriPage = getPageParameter();
            if(parseInt(uriPage) > 1){
                this.$store.commit('setCurrentPage',uriPage);
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
                req = this.getProductsRequest({filters: currentFilters, ordering: this.ordering, page: this.current_page});
            req.then((response, textStatus, jqXHR) => {
                //Resolve

                //Update store
                this.$store.commit('setTotalProducts',response.data.found_products);
                this.$store.commit('setTotalPages',response.data.total_pages);
                this.$store.commit('setCurrentPage',response.data.current_page);
                this.$store.commit('setShowingFrom',response.data.showing_from);
                this.$store.commit('setShowingTo',response.data.showing_to);

                //Update self:
                self.products = response.data.products;
                self.result_count_label = response.data.result_count_label;

                //Update URI:
                if(!self.$store.state.app.just_started){
                    let um = new UriManager();
                    um.updateFilters(self.currentFilters,self.current_page);
                }
                jQuery(window).trigger("filteredProductsUpdated");
            });
        }
    }
}