import $ from "jquery";
import _ from "lodash";

import Vue from "vue";
import UriManager from "./uri-manager";

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
                    currentValues: [],
                    items: []
                }
            },
            props: {
                'label': String,
                'slug': String,
                'hidden': Boolean,
                'is_current': Boolean
            },
            mounted(){},
            methods: {
                /**
                 * Update displayed values of the filter via ajax.
                 */
                updateValues(){
                    let self = this,
                        req = this.controller.getValues();
                    $(this.$el).addClass("loading");
                    $(this.$el).find("input").attr("disabled",true);
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
                        if(!self.is_current){
                            self.hidden = self.items.length === hidden_items.length; //Toggle filter visibility accordingly to the actual visible items
                        }
                        $(this.$el).removeClass("loading");
                        $(this.$el).find("input").attr("disabled",false);
                    },(jqXHR, textStatus, errorThrown) => {
                        //Reject
                        self.items = [];
                        $(this.$el).removeClass("loading");
                        $(this.$el).find("input").attr("disabled",false);
                    });
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
                 */
                updateChildrenValues(){
                    _.each(this.$children,function(filter){
                        filter.updateValues();
                    });
                    this.$emit("filtersUpdated");
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

        window.ProductList = new Vue({
            el: el,
            data: {
                products: [],
                ordering: $("select.orderby").val() || "menu_order",
                result_count_label: ""
            },
            created(){},
            mounted(){
                //Getting the current products
                this.updateProducts(_app.FiltersManager.getFilters());
                if(_app.reactiveProductList){
                    //Listen to filters changes:
                    window.FiltersList.$on("filtersUpdated",function(){
                        window.ProductList.updateProducts(_app.FiltersManager.getFilters());
                    });
                    //Listen to ordering changing
                    //This is a nasty nasty trick to make ordering works without further modifications
                    this.$on("orderingChanged", function(new_order){
                        this.ordering = new_order;
                        window.ProductList.updateProducts(_app.FiltersManager.getFilters());
                    });
                }
            },
            methods: {
                /**
                 * Update the current product list via ajax
                 * @param {array} currentFilters
                 */
                updateProducts(currentFilters){
                    let self = this,
                        req = _app.ProductManager.getProducts(currentFilters,this.ordering);
                    req.then((response, textStatus, jqXHR) => {
                        //Resolve

                        //Update app:
                        _app.total_products = response.data.found_produts;
                        _app.current_page = response.data.current_page;
                        _app.showing_from = response.data.showing_from;
                        _app.showing_to = response.data.showing_to;

                        //Update self:
                        self.products = response.data.products;
                        self.result_count_label = response.data.result_count_label;

                        //Update URI:
                        if(!_app.just_started){
                            _app.UriManager.updateFilters(_app.FiltersManager.getFilters());
                        }
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