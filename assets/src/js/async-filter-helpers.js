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
        let actualIndex = _.findIndex(this.activeFilters,(o) => {
            return o.slug === slug;
        });
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
        let actualIndex = _.findIndex(this.activeFilters,(o) => {
            return o.slug === slug;
        });
        if(actualIndex !== -1){
            this.activeFilters.splice(actualIndex,1);
        }
    }

    /**
     * Update the DOM input with the current filters
     */
    getFilters(){
        //Clean double values
        this.activeFilters.map((filter) => {
            filter.value = _.uniq(filter.value);
            return filter;
        });
        //Then return
        return this.activeFilters
    }
}

class FilterController{
    /**
     * FilterController constructor
     * @param slug
     * @param FiltersManager
     */
    constructor(slug,FiltersManager){
        this.slug = slug;
        this.manager = FiltersManager;
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
                current_filters: this.manager.getFilters()
            },
            method: "POST",
            dataType: "json"
        });
    }
}

export {FiltersManager, FilterController}
