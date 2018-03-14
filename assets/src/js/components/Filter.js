import InstancesStore from '../InstancesStore.js';
import {FilterController} from "../Helpers.js";

export default {
    data(){
        let controller = new FilterController(this.slug,InstancesStore.FiltersApp().FiltersManager);
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
        'is_current': Boolean
    },
    computed: {
        hidden: function(){
            let is_hidden =  this.items.length === this.hidden_items.length; //Toggle filter visibility accordingly to the actual visible items
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
            jQuery(this.$el).addClass("loading");
            jQuery(this.$el).find("input").attr("disabled",true);
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
                jQuery(this.$el).removeClass("loading");
                jQuery(this.$el).find("input").attr("disabled",false);
                self.state = "updated";
                self.$parent.updatingFilters.splice(_.indexOf(self.$parent.updatingFilters,self.slug),1); //Remove the filters from the updating filters
            },(jqXHR, textStatus, errorThrown) => {
                //Reject
                self.items = [];
                jQuery(this.$el).removeClass("loading");
                jQuery(this.$el).find("input").attr("disabled",false);
                self.state = "updated";
                self.$parent.updatingFilters.splice(_.indexOf(self.$parent.updatingFilters,self.slug),1); //Remove the filters from the updating filters
            });
            return req;
        },
        /**
         * Callback for currentValues changes. This is binded via v-on on filter template (eg: async-checkbox.php)
         * @param {object} event
         */
        valueSelected(event){
            let currentValues = this.currentValues;
            InstancesStore.FiltersApp().FiltersManager.updateFilter(this.slug,currentValues);
            this.$emit("value-selected");
            InstancesStore.FiltersApp().just_started = false;
        }
    }
}