import RangeSlider from './uitypes/RangeSlider.vue';

export default {
    components: {
        'range-slider': RangeSlider
    },
    data(){
        return {
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
            return this.items.length === this.hidden_items.length; //Toggle filter visibility accordingly to the actual visible items
        }
    },
    watch: {
        /*state: function(new_state){
            if(new_state === "updated"){
                let is_hidden =  this.items.length === this.hidden_items.length; //Toggle filter visibility accordingly to the actual visible items
                this.hidden = is_hidden;
            }
        }*/
    },
    mounted(){},
    methods: {
        /**
         * Ajax to make the update values request
         * @returns {Promise}
         */
        getValues(){
            return jQuery.ajax({
                url: wbwpf.ajax_url,
                data: {
                    action: "get_values_for_filter",
                    slug: this.slug,
                    current_filters: this.$store.getters.filters
                },
                method: "POST",
                dataType: "json"
            });
        },
        /**
         * Update displayed values of the filter via ajax.
         * return {Promise}
         */
        updateValues(){
            let self = this,
                req = this.getValues();
            jQuery(this.$el).addClass("loading");
            jQuery(this.$el).find("input").attr("disabled",true);
            this.state = "updating";
            this.$store.commit('updateFilter',{slug: this.slug, value: this.currentValues}); //This is very important
            this.$store.commit('addUpdatingFilter',this.slug);
            req.then((data, textStatus, jqXHR) => {
                //Resolve
                let items = data.data;
                let hidden_items = [];
                self.items = items;
                _.forEach(items,(item,index) => {
                    if(this.$store.state.app.just_started){
                        if(item.selected && _.indexOf(self.currentValues,item.id) === -1){
                            self.currentValues.push(item.id); //Insert into currentValues the items signed ad selected (useful when page loads with wbwpf_query string)
                        }
                    }
                    if(!item.visible){
                        hidden_items.push(item.id);
                    }
                });
                self.hidden_items = hidden_items;
                jQuery(this.$el).removeClass("loading");
                jQuery(this.$el).find("input").attr("disabled",false);
                self.state = "updated";
                self.$store.commit('updateFilter',{slug: this.slug, value: this.currentValues});
                self.$store.commit('removeUpdatingFilter',self.slug); //Remove the filters from the updating filters
            },(jqXHR, textStatus, errorThrown) => {
                //Reject
                self.items = [];
                jQuery(this.$el).removeClass("loading");
                jQuery(this.$el).find("input").attr("disabled",false);
                self.state = "updated";
                self.$store.commit('removeUpdatingFilter',self.slug); //Remove the filters from the updating filters
            });
            return req;
        },
        /**
         * Callback for currentValues changes. This is binded via v-on on filter template (eg: async-checkbox.php)
         * @param {object} event
         */
        valueSelected(event){
            this.$store.commit('updateFilter',{slug: this.slug, value: this.currentValues});
            this.$store.commit('appIsNotJustStarted');
            this.$emit("value-selected"); //This will trigger FiltersLists->onFilterSelected()
        }
    }
}