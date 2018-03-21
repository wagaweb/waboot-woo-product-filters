export default {
    state: {
        total_products: 0,
        total_pages: 1,
        current_page: 1,
        showing_from: 0,
        showing_to: 0
    },
    mutations: {
        setTotalProducts(state,n){
            state.total_products = n;
        },
        setTotalPages(state,n){
            state.total_pages = n;
        },
        setCurrentPage(state,n){
            state.current_page = n;
        },
        setShowingFrom(state,n){
            state.showing_from = n;
        },
        setShowingTo(state,n){
            state.showing_to = n;
        }
    }
}