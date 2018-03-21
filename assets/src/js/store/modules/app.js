export default {
    state: {
        just_started: true,
    },
    mutations: {
        appIsNotJustStarted(state){
            state.just_started = false;
        }
    }
}