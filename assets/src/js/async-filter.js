import Vue from 'vue';
import $ from "jquery";
import _ from 'lodash';

class FiltersManager{
    constructor() {
        this.activeFilters = [];
    }
    updateFilter(slug,value){
        debugger;
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
    removeFilter(slug){
        let actualIndex = _.findIndex(this.activeFilters,{slug:slug});
        if(actualIndex !== -1){
            this.activeFilters.splice(actualIndex,1);
        }
    }
}

class FilterController{
    constructor(slug){
        this.slug = slug;
    }
    getValues(){
        return $.ajax({
            url: wbwpf.ajax_url,
            data: {
                action: "get_values_for_filter",
                slug: this.slug
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
    props: ['label','slug','hidden'],
    created: function(){
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
    methods: {
        valueSelected: function(event){
            let $target = $(event.target);
            this.manager.updateFilter(this.slug,this.currentValues);
        }
    }
});

export {Filter, FilterController}
