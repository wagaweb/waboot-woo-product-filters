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

/**
 *
 * @param key
 * @param value
 * @param url
 */
function GetUpdatedUriWithQueryString(key, value, url) {
    if (!url) url = window.location.href;
    let re = new RegExp("([?&])" + key + "=.*?(&|#|$)(.*)", "gi"),
        hash;

    if (re.test(url)) {
        if (typeof value !== 'undefined' && value !== null)
            return url.replace(re, '$1' + key + "=" + value + '$2$3');
        else {
            hash = url.split('#');
            url = hash[0].replace(re, '$1$3').replace(/(&|\?)$/, '');
            if (typeof hash[1] !== 'undefined' && hash[1] !== null)
                url += '#' + hash[1];
            return url;
        }
    }
    else {
        if (typeof value !== 'undefined' && value !== null) {
            let separator = url.indexOf('?') !== -1 ? '&' : '?';
            hash = url.split('#');
            url = hash[0] + separator + key + '=' + value;
            if (typeof hash[1] !== 'undefined' && hash[1] !== null)
                url += '#' + hash[1];
            return url;
        }
        else
            return url;
    }
}

export {getQueryString, getSearchParameters, getFiltersSearchParameters, getStrippedFiltersSearchParameters, GetUpdatedUriWithQueryString};