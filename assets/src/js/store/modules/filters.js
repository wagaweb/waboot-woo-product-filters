export default {
    state: {
        activeFilters: [],
        updatingFilters: []
    },
    getters: {
        /**
         * Get the current active filters.
         *
         * Returns an array of objects like that:
         * [
         *  {
         *      slug: "product_cat"
         *      value: [12]
         *  }
         *  ...
         * ]
         *
         *
         * @return {Array}
         */
        filters(state){
            //Clean double values and return
            return state.activeFilters.map((filter) => {
                filter.value = _.uniq(filter.value);
                return filter;
            });
        }
    },
    mutations: {
        /**
         * Adds or update a filter
         * @param state
         * @param payload
         */
        updateFilter(state,payload){
            let actualIndex = _.findIndex(state.activeFilters,(o) => {
                return o.slug === payload.slug;
            });
            if(actualIndex !== -1){
                //Update
                state.activeFilters[actualIndex] = {
                    slug: payload.slug,
                    value: payload.value
                };
            }else{
                //Push
                state.activeFilters.push({
                    slug: payload.slug,
                    value: payload.value
                });
            }
        },
        /**
         * Remove a filter
         * @param state
         * @param slug
         */
        removeFilter(state,slug){
            let actualIndex = _.findIndex(state.activeFilters,(o) => {
                return o.slug === slug;
            });
            if(actualIndex !== -1){
                state.activeFilters.splice(actualIndex,1);
            }
        },
        /**
         * Adds a filter to the updating filters
         * @param state
         * @param slug
         */
        addUpdatingFilter(state,slug){
            state.updatingFilters.push(slug)
        },
        /**
         * Remove a filter from the updating filters
         * @param state
         * @param slug
         */
        removeUpdatingFilter(state,slug){
            state.updatingFilters.splice(_.indexOf(state.updatingFilters,slug),1);
        }
    }
}