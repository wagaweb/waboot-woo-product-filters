import InstancesStore from '../InstancesStore.js';
import SingleFilter from './Filter.js'

export default {
    components: {
        SingleFilter
    },
    data() {
        return {
            updatingFilters: [],
            submitOnSelect: window.wbwpf.components.filtersList.submitOnSelect,
            hasSubmitButton: window.wbwpf.components.filtersList.hasSubmitButton,
            reloadFiltersOnSelect: window.wbwpf.components.filtersList.hasSubmitButton,
            reloadProductsListOnSubmit: window.wbwpf.components.filtersList.reloadProductsListOnSubmit,
            $form: undefined,
        };
    },
    computed: {
        updated: function(){
            return _.isEmpty(this.updatingFilters); //whether all filters are updated or not
        }
    },
    mounted(){
        this.$on("filtersDetected",function(){
            this.updateFiltersValues();
        });

        this.$form = jQuery(this.$el).find('form');

        this.detectActiveFilters();
    },
    methods: {
        /**
         * Detect current active filters based on data- attribute of root element.
         * "Active filters" means filters with selected values.
         */
        detectActiveFilters(){
            let activeFilters = jQuery(this.$el).data("filters"); //detect the active filters (thanks jQuery! :))
            if(typeof activeFilters.filters === "object"){
                //Let's add the active filters to FiltersManager
                _.forEach(activeFilters.filters,function(filter_params,filter_slug){
                    if(typeof activeFilters.values === "object" && !_.isUndefined(activeFilters.values[filter_slug])){
                        let filter_value = activeFilters.values[filter_slug];
                        this.$store.updateFilter({slug: filter_slug, value: filter_value});
                    }
                });
            }

            this.$emit("filtersDetected");
        },
        /**
         * Called when a filter has been selected.
         */
        onFilterSelected(){
            /*if(this.submitOnSelect){
                this.$form.submit();
            }else if(this.reloadFiltersOnSelect){
                this.updateFiltersValues();
            }else if(this.reloadProductsListOnSubmit){
                this.updateFiltersValues();
            }*/
        },
        /**
         * Calls "updateValues" on each children.
         *
         * Called on 'valueSelected' and 'filtersDetected'.
         *
         * The first is emitted through this instance children,
         * the latter is called by this.detectActiveFilters() during mount().
         */
        updateFiltersValues(){
            let updatingPromises = [];
            jQuery(this.$el).find("[data-apply_button]").attr("disabled",true); //todo: is there a better way?
            _.each(this.$children,function(filter){
                updatingPromises.push(filter.updateValues());
            });
            Promise.all(updatingPromises).then(() => {
                this.$parent.$emit("filtersUpdated");
                jQuery(window).trigger("filtersUpdated");
                jQuery(this.$el).find("[data-apply_button]").attr("disabled",false);
            });
        }
    }
}