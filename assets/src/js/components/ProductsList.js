import {getPageParameter} from "../utilities";
import UriManager from "../uri-manager.js";
import Product from './Product.js'
import Pagination from './Pagination.js'

export default {
    components: {
        'wbwpf-product': Product,
        'wbwpf-pagination': Pagination
    },
    data: {
        products: [],
        ordering: jQuery("select.orderby").val() || "menu_order", //This is a nasty nasty trick to make ordering works without further modifications
        result_count_label: ""
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
    created(){},
    mounted(){
        try{
            //Getting the current products
            this.updateProducts(this.currentFilters);
            //Listen to filters changes:
            if(this.$store.state.app.reactiveProductList){
                jQuery(window).on('filtersUpdated', () => {
                    this.$store.commit('setCurrentPage',1); //Reset the page when filters are updated
                    this.updateProducts(this.currentFilters,false);
                });
            }
            //Listen to ordering changing. This is emitted by jQuery click event.
            jQuery(window).on("orderingChanged", (new_order) => {
                this.ordering = new_order;
                this.updateProducts(this.currentFilters);
            });
            //Listen to page changing. This is emitted by <wbwpf-pagination> component.
            jQuery(window).on('pageChanged', (new_page) => {
                this.$store.commit('setCurrentPage',new_page);
                this.updateProducts(this.currentFilters,false);
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
                if(!this.$store.just_started){
                    let um = new UriManager();
                    um.updateFilters(self.currentFilters,self.current_page);
                }
                jQuery(window).trigger("filteredProductsUpdated");
            });
        }
    }
}