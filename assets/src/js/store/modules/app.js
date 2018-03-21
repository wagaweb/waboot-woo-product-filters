export default {
    state: {
        just_started: true,
        reactiveProductList: false
    },
    mutations: {
        appIsNotJustStarted(state){
            state.just_started = false;
        }
    }
}