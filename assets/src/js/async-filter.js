import Vue from 'vue';
import $ from "jquery";
import _ from 'lodash';

class FiltersManager{
    constructor() {
        this.activeFilters = [];
    }

    /**
     * Adds or update a filter
     * @param slug
     * @param value
     */
    updateFilter(slug,value){
        let actualIndex = _.findIndex(this.activeFilters,{slug:slug});
        if(actualIndex !== -1){
            //Update
            this.activeFilters[actualIndex] = {
                slug: slug,
                value: value
            };
        }else{
            //Push
            this.activeFilters.push({
                slug: slug,
                value: value
            });
        }
    }

    /**
     * Remove a filter
     * @param slug
     */
    removeFilter(slug){
        let actualIndex = _.findIndex(this.activeFilters,{slug:slug});
        if(actualIndex !== -1){
            this.activeFilters.splice(actualIndex,1);
        }
    }

    /**
     * Update the DOM input with the current filters
     */
    getFilters(){
        return this.activeFilters
    }
}

class FilterController{
    /**
     * FilterController constructor
     * @param slug
     */
    constructor(slug){
        this.slug = slug;
    }

    /**
     * Ajax to make the update values request
     * @returns {$.ajax}
     */
    getValues(){
        return $.ajax({
            url: wbwpf.ajax_url,
            data: {
                action: "get_values_for_filter",
                slug: this.slug,
                current_filters: manager.getFilters()
            },
            method: "POST",
            dataType: "json"
        });
    }
}

let manager = new FiltersManager();

let Filter = Vue.component("wbwpf-filter",{
    data(){
        let controller = new FilterController(this.slug);
        return {
            manager: manager,
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

export {Filter, FilterController}
