import SingleFilter from './Filter.js'

export default {
    components: {
        SingleFilter
    },
    data() {
        return {
            submitOnSelect: window.wbwpf.components.filtersList.submitOnSelect,
            hasSubmitButton: window.wbwpf.components.filtersList.hasSubmitButton,
            reloadFiltersOnSelect: window.wbwpf.components.filtersList.hasSubmitButton,
            reloadProductsListOnSubmit: window.wbwpf.components.filtersList.reloadProductsListOnSubmit,
            $form: undefined,
        };
    },
    computed: {
        updated: function(){
            return _.isEmpty(this.$store.state.filters.updatingFilters); //whether all filters are updated or not
        },
        updatingFilters: function(){
            return this.$store.state.filters.updatingFilters;
        }
    },
    mounted(){
        this.$form = jQuery(this.$el).find('form');
        this.loadFilters();
    },
    methods: {
        /**
         * Detect active filters (namely the filters with selected values) and load their values
         */
        loadFilters(){
            this.detectActiveFilters();
            this.loadFiltersValues();
        },
        /**
         * Detect current active filters based on data- attribute of root element, then update the store.
         * "Active filters" means filters with selected values.
         */
        detectActiveFilters(){
            let activeFilters = jQuery(this.$el).data("filters"); //detect the active filters
            if(typeof activeFilters.filters === "object"){
                let self = this;
                //Update the store with the currently active filters
                _.forEach(activeFilters.filters,function(filter_params,filter_slug){
                    if(typeof activeFilters.values === "object" && !_.isUndefined(activeFilters.values[filter_slug])){
                        let filter_value = activeFilters.values[filter_slug];
                        self.$store.commit('updateFilter',{slug: filter_slug, value: filter_value});
                    }
                });
            }
            this.$emit("filtersDetected");
            jQuery(window).trigger("filtersDetected");
        },
        /**
         * Calls "updateValues" on each children.
         *
         * Called on 'filtersDetected' and in when this.reloadFiltersOnSelect || this.reloadProductsListOnSubmit
         */
        loadFiltersValues(){
            let updatingPromises = [];
            _.each(this.$children,function(filter){
                updatingPromises.push(filter.updateValues());
            });
            Promise.all(updatingPromises).then(() => {
                this.$parent.$emit("filtersUpdated");
                jQuery(window).trigger("filtersUpdated");
            });
        },
        /**
         * Called when a filter has been selected.
         */
        onFilterSelected(){
            if(this.submitOnSelect && !this.reloadProductsListOnSubmit){
                this.$form.submit();
            }else if(this.reloadFiltersOnSelect){
                this.loadFiltersValues();
            }else if(this.reloadProductsListOnSubmit){
                this.loadFiltersValues();
            }
        }
    }
}