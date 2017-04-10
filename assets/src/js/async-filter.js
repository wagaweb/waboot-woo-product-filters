import Vue from 'vue';
import $ from "jquery";
import _ from 'lodash';

class FilterController{
    constructor(){}
    getValues(){}
}

class Filter{
    constructor(props) {
        debugger;
        this.Controller = new FilterController();
        this.tpl = _template();
        this.state = {
            current_values: {}
        };
    }

    componentDidMount() {
        debugger;
        this.Controller.getValues().then(function () {
            debugger;
            //Resolve
        }, function () {
            debugger;
            //Reject
        });
    }

    render() {
        debugger;
    }
}

export default Vue.component("Filter",{
    data(){
        return {
            current_values: []
        }
    },
    props: ['label','slug']
});
