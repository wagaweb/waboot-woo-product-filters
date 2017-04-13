import $ from "jquery";
import _ from "lodash";

import Vue from "vue";

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
    }

    /**
     * Startup the application
     *
     * @param {string} filtersList the vue root element of the filters list
     * @param {string} productsList the vue root element of the products list
     */
    start(filtersList,productsList){
        if(typeof filtersList !== "undefined"){
            this._startFiltersList(filtersList);
        }
        if(typeof productsList !== "undefined"){
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
            props: ['label','slug','hidden','update'],
            created(){
                this.updateValues();
            },
            methods: {
                /**
                 * Update displayed values of the filter via ajax.
                 */
                updateValues(){
                    let self = this,
                        req = this.controller.getValues();
                    req.then((data, textStatus, jqXHR) => {
                        //Resolve
                        self.items = data.data;
                    },(jqXHR, textStatus, errorThrown) => {
                        //Reject
                        self.items = [];
                    });
                },
                /**
                 * Callback for currentValues changes.
                 * @param {object} event
                 */
                valueSelected(event){
                    let $target = $(event.target);
                    _app.FiltersManager.updateFilter(this.slug,this.currentValues);
                    this.$parent.$emit("valueSelected");
                }
            }
        });

        //Init a new Vue instance for the filters
        window.FiltersList = new Vue({
            el: el
        });
        //Listen on value changes on components
        window.FiltersList.$on("valueSelected",function(){
            _.each(this.$children,function(filter){
                filter.updateValues();
            });
            this.$emit("filtersUpdated");
        });
    }

    /**
     * Startup the products list vue instance
     * @param {string} el the root element
     */
    _startProductsList(el){
        let _app = this;

        Vue.component('wbwpf-product',{
            template: "#wbwpf-product-template",
            props: ['data']
        });

        window.ProductList = new Vue({
            el: el,
            data: {
                products: []
            },
            created(){
                //Getting the current products
                this.updateProducts(_app.FiltersManager.getFilters());
            },
            mounted(){
                //Listen to filters changes:
                window.FiltersList.$on("filtersUpdated",function(){
                    window.ProductList.updateProducts(_app.FiltersManager.getFilters());
                })
            },
            methods: {
                /**
                 * Update the current product list via ajax
                 * @param {array} currentFilters
                 */
                updateProducts(currentFilters){
                    let self = this,
                        req = _app.ProductManager.getProducts(currentFilters);
                    req.then((data, textStatus, jqXHR) => {
                        //Resolve
                        self.products = data.data;
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