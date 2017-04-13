import $ from "jquery";
import _ from "lodash";

import Vue from "vue";

import {FilterController,FiltersManager} from "./async-filter-helpers";
import {ProductsManager} from "./async-product-helpers";

class FiltersApp{
    constructor(FiltersManager,ProductsManager){
        this.FiltersManager = FiltersManager;
        if(typeof ProductsManager !== "undefined"){
            this.ProductManager = ProductsManager;
        }
    }
    start(filtersList,productsList){
        if(typeof filtersList !== "undefined"){
            this.startFiltersList(filtersList);
        }
        if(typeof productsList !== "undefined"){
            this.startProductsList(productsList);
        }
    }
    startFiltersList(el){
        let _app = this;

        Vue.component("wbwpf-filter",{
            data(){
                let controller = new FilterController(this.slug,_app.FiltersManager);
                return {
                    manager: _app.FiltersManager,
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
                 * @param event
                 */
                valueSelected(event){
                    let $target = $(event.target);
                    this.manager.updateFilter(this.slug,this.currentValues);
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
    startProductsList(el){
        let _app = this;

        Vue.component('wbwpf-product',{
            template: "#wbwpf-product-template",
            props: ['data']
        });

        window.ProductList = new Vue({
            el: el,
            data: {
                product_manager: new ProductsManager(),
                products: []
            },
            created(){
                //Getting the current products
                this.updateProducts(_app.FiltersManager.getFilters());
            },
            mounted(){
                //Listen to filters changes:
                window.FiltersList.$on("filtersUpdated",function(){
                    window.ProductList.updateProducts();
                })
            },
            methods: {
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