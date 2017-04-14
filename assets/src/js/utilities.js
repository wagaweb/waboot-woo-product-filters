import _ from 'lodash';

/**
 * Return the current query string
 *
 * @returns {string}
 */
function getQueryString(){
    let qs = window.location.search.substr(1);
    //Clear square brackets
    qs = qs.replace(/%5B/g,"[");
    qs = qs.replace(/%5D/g,"]");
    return qs;
}

/**
 * Get all $_GET parameters
 *
 * @returns {object}
 */
function getSearchParameters() {
    let transformToAssocArray = function(prmstr){
        let params = {};
        let prmarr = prmstr.split("&");
        for ( let i = 0; i < prmarr.length; i++) {
            let tmparr = prmarr[i].split("=");
            //Clear square brackets
            tmparr[0] = tmparr[0].replace(/%5B/g,"[");
            tmparr[0] = tmparr[0].replace(/%5D/g,"]");
            params[tmparr[0]] = tmparr[1];
        }
        return params;
    };
    let prmstr = window.location.search.substr(1);
    return prmstr != null && prmstr != "" ? transformToAssocArray(prmstr) : {};
}

/**
 * Get filter search params
 * @returns {object}
 */
function getFiltersSearchParameters(){
    let query_parameters = getSearchParameters();
    let filters_parameters = {};
    _.each(query_parameters,function(value,key,list){
        if(key.match(/^wbwpf_/)){
            filters_parameters[key] = value;
        }
    });
    return filters_parameters;
}

/**
 * Get filters search params without "wbwpf_" prefix
 * @returns {object}
 */
function getStrippedFiltersSearchParameters(){
    let query_parameters = getFiltersSearchParameters();
    let filters_parameters = {};
    _.each(query_parameters,function(value,key,list){
        let filter_key = key.replace(/^wbwpf_/,"");
        filters_parameters[filter_key] = value;
    });
    return filters_parameters;
}

export {getQueryString, getSearchParameters, getFiltersSearchParameters, getStrippedFiltersSearchParameters};