import _ from "lodash";
import {GetUpdatedUriWithQueryString} from "./utilities";

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
        if(this.canPushState()){
            let new_qs = this._generateQueryString(filters);
            let new_location = GetUpdatedUriWithQueryString("wbwpf_query",new_qs);
            let state = {
                qs: new_qs
            };
            window.history.pushState(state,document.title,new_location);
        }
    }

    /**
     * Generate a new query string from filters array. This will mimic the "stringified version" managed by the plugin.
     * For more info see: Filter_Factory.php @ stringify_from_params()
     *
     * @param {array} filters
     * @private
     */
    _generateQueryString(filters){
        let qs = "";
        _.forEach(filters,(filter,key) => {
            let values = filter.value !== "undefined" ? filter.value.join(",") : "";
            if(key > 0){
                qs += "-";
            }
            qs += filter.slug+"|"+values;
        });
        return qs;
    }

    /**
     * Perform actions during popstate
     */
    onLocationChange(){
        //console.log("URI Updated");
    }
}