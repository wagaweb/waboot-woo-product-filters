export default {
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
            let activeFilters = jQuery(this.$el).data("filters"); //detect the active filters (thanks jQuery! :))
            if(typeof activeFilters.filters === "object"){
                //Let's add the active filters to FiltersManager
                _.forEach(activeFilters.filters,function(filter_params,filter_slug){
                    if(typeof activeFilters.values === "object" && !_.isUndefined(activeFilters.values[filter_slug])){
                        let filter_value = activeFilters.values[filter_slug];
                        window.FiltersApp.FiltersManager.updateFilter(filter_slug,filter_value);
                    }
                });
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
                jQuery(window).trigger("filtersUpdated");
                jQuery(this.$el).find("[data-apply_button]").attr("disabled",false);
            });
        }
    }
}