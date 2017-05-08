import $ from "jquery";
import _ from "lodash";

import Vue from "vue";
import UriManager from "./uri-manager";
import { getPageParameter } from "./utilities";

import {FilterController} from "./async-filter-helpers";

class FiltersApp{
    /**
     * Build up the application
     *
     * @param {FiltersManager} fm
     * @param {ProductsManager} pm
     */
    constructor(fm,pm){
        this.FiltersManager = fm;
        if(typeof pm !== "undefined"){
            this.ProductManager = pm;
        }
        this.UriManager = new UriManager();
        this.reactiveProductList = false; //whether the product list must respond to filters changes
        this.just_started = true;
    }

    /**
     * Startup the application
     *
     * @param {string} filtersList the vue root element of the filters list
     * @param {string} productsList the vue root element of the products list
     * @param {boolean} reactiveProductList whether the product list must respond to filters changes
     */
    start(filtersList,productsList,reactiveProductList){
        if(_.isUndefined(reactiveProductList)){
            reactiveProductList = this.reactiveProductList; //set to the default
        }
        if(typeof filtersList !== "undefined"){
            this._startFiltersList(filtersList);
        }
        if(typeof productsList !== "undefined"){
            this.reactiveProductList = reactiveProductList;
            this._startProductsList(productsList);
        }
        $(window).trigger("filtersAppStarted");
    }

    /**
     * Startup the filters list vue instance
     * @param {string} el the root element
     */
    _startFiltersList(el){
        let _app = this;

        Vue.component("wbwpf-filter",{
            data(){
                let controller = new FilterController(this.slug,_app.FiltersManager);
                return {
                    controller: controller,
                    state: "updating",
                    currentValues: [],
                    items: [],
                    hidden_items: []
                }
            },
            props: {
                'label': String,
                'slug': String,
                'hidden': Boolean,
                'is_current': Boolean
            },
            computed: {
                hidden: function(){
                    let is_hidden =  this.items.length === this.hidden_items.length; //Toggle filter visibility accordingly to the actual visible items
                    this.hidden = is_hidden;
                    return is_hidden;
                }
            },
            watch: {
                state: function(new_state){
                    if(new_state === "updated"){
                        let is_hidden =  this.items.length === this.hidden_items.length; //Toggle filter visibility accordingly to the actual visible items
                        this.hidden = is_hidden;
                    }
                }
            },
            mounted(){},
            methods: {
                /**
                 * Update displayed values of the filter via ajax.
                 * return {Promise}
                 */
                updateValues(){
                    let self = this,
                        req = this.controller.getValues();
                    $(this.$el).addClass("loading");
                    $(this.$el).find("input").attr("disabled",true);
                    this.state = "updating";
                    this.$parent.updatingFilters.push(this.slug);
                    req.then((data, textStatus, jqXHR) => {
                        //Resolve
                        let items = data.data;
                        let hidden_items = [];
                        self.items = items;
                        _.forEach(items,(item,index) => {
                            if(item.selected && _.indexOf(self.currentValues,item.id) === -1){
                                self.currentValues.push(item.id); //Insert into currentValues the items signed ad selected (useful when page loads with wbwpf_query string)
                            }
                            if(!item.visible){
                                hidden_items.push(item.id);
                            }
                        });
                        self.hidden_items = hidden_items;
                        $(this.$el).removeClass("loading");
                        $(this.$el).find("input").attr("disabled",false);
                        self.state = "updated";
                        self.$parent.updatingFilters.splice(_.indexOf(self.$parent.updatingFilters,self.slug),1); //Remove the filters from the updating filters
                    },(jqXHR, textStatus, errorThrown) => {
                        //Reject
                        self.items = [];
                        $(this.$el).removeClass("loading");
                        $(this.$el).find("input").attr("disabled",false);
                        self.state = "updated";
                        self.$parent.updatingFilters.splice(_.indexOf(self.$parent.updatingFilters,self.slug),1); //Remove the filters from the updating filters
                    });
                    return req;
                },
                /**
                 * Callback for currentValues changes.
                 * @param {object} event
                 */
                valueSelected(event){
                    let currentValues = this.currentValues;
                    _app.FiltersManager.updateFilter(this.slug,currentValues);
                    this.$parent.$emit("valueSelected");
                    _app.just_started = false;
                }
            }
        });

        //Init a new Vue instance for the filters
        window.FiltersList = new Vue({
            el: el,
            data: {
                updatingFilters: []
            },
            computed: {
                updated: function(){
                    return _.isEmpty(this.updatingFilters); //whether all filters are updated or not
                }
            },
            mounted(){
                this.$on("valueSelected",function(){
                    this.updateChildrenValues(); //Every time a value is selected in a child, then "valueSelected" is emitted
                });

                this.$on("filtersDetected",function(){
                    this.updateChildrenValues();
                });

                this.detectActiveFilters();
            },
            methods: {
                /**
                 * Detect current active filters based on data- attribute of root element.
                 */
                detectActiveFilters(){
                    let activeFilters = $(this.$el).data("filters"); //detect the active filters (thanks jQuery! :))
                    if(typeof activeFilters.filters === "object"){
                        //Let's add the active filters to FiltersManager
                        _.forEach(activeFilters.filters,function(filter_params,filter_slug){
                            if(typeof activeFilters.values === "object" && !_.isUndefined(activeFilters.values[filter_slug])){
                                let filter_value = activeFilters.values[filter_slug];
                                _app.FiltersManager.updateFilter(filter_slug,filter_value);
                            }
                        })
                    }

                    this.$emit("filtersDetected");
                },
                /**
                 * Calls "updateValues" on each children.
                 *
                 * Called on 'valueSelected' and 'filtersDetected'.
                 *
                 * The first is emitted through this instance children,
                 * the latter is called by this.detectActiveFilters() during mount().
                 */
                updateChildrenValues(){
                    let updatingPromises = [];
                    jQuery(this.$el).find("[data-apply_button]").attr("disabled",true); //todo: is there a better way?
                    _.each(this.$children,function(filter){
                        updatingPromises.push(filter.updateValues());
                    });
                    Promise.all(updatingPromises).then(() => {
                        this.$emit("filtersUpdated");
                        $(window).trigger("filtersUpdated");
                        jQuery(this.$el).find("[data-apply_button]").attr("disabled",false);
                    });
                }
            }
        });
    }

    /**
     * Startup the products list vue instance
     * @param {string} el the root element
     */
    _startProductsList(el){
        let _app = this;

        //Re-bind ordering
        $("select.orderby").closest("form").removeClass("woocommerce-ordering").addClass("wbwpf-woocommerce-ordering");
        $("body").on( 'change', '.wbwpf-woocommerce-ordering select.orderby', function() {
            if(window.ProductList !== "undefined"){
                window.ProductList.$emit("orderingChanged",$(this).val());
            }
        });

        Vue.component('wbwpf-product',{
            template: "#wbwpf-product-template",
            props: ['data']
        });

        Vue.component('wbwpf-pagination',{
            props: {
                'outerWrapper': {
                    type: String,
                    default: 'ul'
                },
                'innerWrapper': {
                    type: String,
                    default: 'li'
                },
                'current_page': Number,
                'total_pages': Number,
                'mid_size': {
                    type: [String,Number],
                    default: 3
                }
            },
            computed: {
                next_page: function(){
                    if(this.current_page === this.total_pages){
                        return this.total_pages;
                    }else{
                        return this.current_page + 1;
                    }
                },
                prev_page: function(){
                    if(this.current_page === 1 || this.total_pages === 1){
                        return 1;
                    }else{
                        return this.current_page - 1;
                    }
                }
            },
            created(){},
            mounted(){},
            render(createElement){
                let output,
                    innerElements = [];

                /**
                 * @param {number} number
                 * @param {Array} to
                 * @param {Boolean} is_current
                 * @returns {Array}
                 */
                let pushPage = (number,to,is_current = false) => {
                    to.push(createElement(this.innerWrapper,{
                        'class': {
                            'wbwpf-navigation-item': true,
                            'current': is_current,
                            'page-link': true
                        }
                    },[
                        createElement("a",{
                            domProps: {
                                innerHTML: number
                            },
                            attrs: {
                                href: "#page"+number,
                                title: "Go to page "+number,
                                'data-goto': number
                            },
                            on: {
                                click: this.changePage
                            }
                        })
                    ]));
                    return to;
                };

                /**
                 * @param {Array} to
                 * @returns {Array}
                 */
                let pushDots = (to) => {
                    to.push(createElement(this.innerWrapper,{
                        'class': {
                            'wbwpf-navigation-item': true,
                            'separator': true
                        }
                    },[
                        createElement("span",{
                            domProps: {
                                innerHTML: "..."
                            }
                        })
                    ]));
                    return to;
                };

                let can_push_dots = true;
                let midrange = this.getMidRange(this.mid_size,this.current_page);

                for(let i = 1; i <= this.total_pages; i++){
                    let is_first_half = 1 <= this.current_page && this.current_page <= this.mid_size;
                    let is_last_half = (this.total_pages - this.mid_size) <= this.current_page && this.current_page <= this.total_pages;

                    if( (is_first_half && i <= this.mid_size) || i === 1){ //We are in the head part of the pagination (current page is below mid size)
                        innerElements = pushPage(i,innerElements, i === this.current_page);
                    }else if( (is_last_half && i >= this.total_pages - this.mid_size) || i === this.total_pages){ //We are in the last part of the pagination (current page is between total pages and total pages - mid size)
                        innerElements = pushPage(i,innerElements, i === this.current_page);
                    }else if(_.indexOf(midrange,i) !== -1){ //We are in the mid range
                        innerElements = pushPage(i,innerElements, i === this.current_page);
                        can_push_dots = true;
                    }else{
                        if(can_push_dots){ //Otherwise, put separator
                            innerElements = pushDots(innerElements);
                            can_push_dots = false;
                        }
                    }
                }
                output = createElement(this.outerWrapper,{
                    'class': {
                        'wbwpf-navigation-wrapper': true
                    }
                },innerElements);
                return output;
            },
            methods: {
                /**
                 * Handles the click event on a page link. Emits "pageChanged" from window.ProductList, which in turn force the product update.
                 * @param event
                 */
                changePage(event){
                    event.preventDefault();
                    let $clickedLink = $(event.target);
                    let pageToGo = $clickedLink.data('goto');
                    _app.just_started = false;
                    window.ProductList.$emit("pageChanged",pageToGo);
                },
                /**
                 * Detects the mid range for pagination.
                 *
                 * @param {number} range_size
                 * @param {number} range_pivot
                 *
                 * @example: put range_size = 3, range_pivot = 25 => [24,25,26]
                 * @example: put range_size = 4, range_pivot = 25 => [23,24,25,26]
                 */
                getMidRange(range_size,range_pivot){
                    if(range_size % 2 === 0){
                        var tmp = range_size / 2;
                        var tail_size_left = tmp;
                        var tail_size_right = tail_size_left - 1;
                    }else{
                        var tmp = range_size - 1;
                        var tail_size_left = tmp / 2;
                        var tail_size_right = tmp / 2;
                    }
                    let range = [];
                    for(let i = tail_size_left; i >= 1; i--){
                        range.push(range_pivot-i);
                    }
                    range.push(range_pivot);
                    for(let i = 1; i <= tail_size_right; i++){
                        range.push(range_pivot+i);
                    }
                    return range;
                }
            }
        });

        window.ProductList = new Vue({
            el: el,
            data: {
                products: [],
                current_page: 1,
                total_pages: 1,
                ordering: $("select.orderby").val() || "menu_order", //This is a nasty nasty trick to make ordering works without further modifications
                result_count_label: ""
            },
            created(){},
            mounted(){
                //Getting the current products
                this.updateProducts(_app.FiltersManager.getFilters());
                if(_app.reactiveProductList){
                    //Listen to filters changes:
                    window.FiltersList.$on("filtersUpdated",function(){
                        window.ProductList.current_page = 1; //Reset the page when filters are updated
                        window.ProductList.updateProducts(_app.FiltersManager.getFilters(),false);
                    });
                }
                //Listen to ordering changing. This is emitted by jQuery click event.
                this.$on("orderingChanged", function(new_order){
                    this.ordering = new_order;
                    window.ProductList.updateProducts(_app.FiltersManager.getFilters());
                });
                //Listen to page changing. This is emitted by <wbwpf-pagination> component.
                this.$on('pageChanged', function(new_page){
                    this.current_page = new_page;
                    window.ProductList.updateProducts(_app.FiltersManager.getFilters(),false);
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
                        req = _app.ProductManager.getProducts(currentFilters,this.ordering,this.current_page);
                    req.then((response, textStatus, jqXHR) => {
                        //Resolve

                        //Update app:
                        _app.total_products = response.data.found_products;
                        _app.total_pages = response.data.total_pages;
                        _app.current_page = response.data.current_page;
                        _app.showing_from = response.data.showing_from;
                        _app.showing_to = response.data.showing_to;

                        //Update self:
                        self.products = response.data.products;
                        self.current_page = response.data.current_page;
                        self.total_pages = response.data.total_pages;
                        self.result_count_label = response.data.result_count_label;

                        //Update URI:
                        if(!_app.just_started){
                            _app.UriManager.updateFilters(_app.FiltersManager.getFilters(),self.current_page);
                        }
                        $(window).trigger("filteredProductsUpdated");
                    },(jqXHR, textStatus, errorThrown) => {
                        //Reject
                        self.products = [];
                    });
                }
            }
        });
    }
}

export {FiltersApp}