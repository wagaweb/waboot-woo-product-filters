import _ from "lodash";

/**
 * Checks if pushState is available
 * @returns {boolean}
 */
function isPushStateAvailable() {
    return !!(
        typeof window !== 'undefined' &&
        window.history &&
        window.history.pushState
    );
}

export default class{
    constructor(){
        this._pushStateAvailable = isPushStateAvailable();
        if(this._pushStateAvailable){
            window.addEventListener('popstate',this.onLocationChange);
        }
    }

    /**
     * @returns {boolean}
     */
    canPushState(){
        return this._pushStateAvailable;
    }

    /**
     * Inject the filters in URI
     *
     * @param {array} filters
     */
    updateFilters(filters){
        /*
         * Here we receive filters from window.ProductList in an array of obkects like:
         * [
         *      {
         *          slug: "product_cat",
         *          value: [
         *              21
         *          ]
         *      }
         *      ...
         * ]
         *
         */
        let qs = this._generateQueryString(filters);
        window.location.href = window.location.href.replace(/\?([a-zA-Z0-9])/);

        console.log(filters);
    }

    /**
     * Generate a new query string from filters array. This will mimic the "stringified version" managed by the plugin.
     * For more info see: Filter_Factory.php @ stringify_from_params()
     *
     * @param filters
     * @private
     */
    _generateQueryString(filters){
        let qs = "";
        _.forEach(filters,(filter,key) => {
            let values = filter.value !== "undefined" ? filter.value.join(",") : "";
            if(key > 1){
                qs += "-";
            }
            qs += filter.slug+"|"+values;
        });
        return qs;
    }

    /**
     * Retrieve the filters currently in URI
     */
    getCurrentFilters(){
        console.log("Get the current filters");
    }

    /**
     *
     */
    onLocationChange(){
        console.log("URI Updated");
    }
}