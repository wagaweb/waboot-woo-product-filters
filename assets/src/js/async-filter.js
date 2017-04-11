import Vue from 'vue';
import $ from "jquery";
import _ from 'lodash';

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

let Filter = Vue.component("wbwpf-filter",{
    data(){
        let controller = new FilterController(this.slug);
        return {
            controller: controller,
            items: []
        }
    },
    props: ['label','slug','hidden'],
    created: function(){
        debugger;
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
});

export {Filter, FilterController}
