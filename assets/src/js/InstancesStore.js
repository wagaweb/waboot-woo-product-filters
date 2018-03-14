export default{
    /**
     *
     * @returns {FiltersApp}
     * @constructor
     */
    FiltersApp: function(){
        if(typeof window.FiltersApp !== 'undefined'){
            return window.FiltersApp;
        }
        throw 'FiltersApp Unavailable';
    },
    /**
     *
     * @returns {Vue}
     * @constructor
     */
    FiltersList: function(){
        if(typeof window.FiltersList !== 'undefined'){
            return window.FiltersList;
        }
        throw 'FiltersList Unavailable';
    },
    /**
     *
     * @returns {Vue}
     * @constructor
     */
    ProductsList: function(){
        if(typeof window.ProductList !== 'undefined'){
            return window.ProductList;
        }
        throw 'ProductList Unavailable';
    },
    setFiltersApp(o){
        window.FiltersApp = o;
    },
    setFiltersList(o){
        window.FiltersList = o;
    },
    setProductsList(o){
        window.ProductList = o;
    }
}