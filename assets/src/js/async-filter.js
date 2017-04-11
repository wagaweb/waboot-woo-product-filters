import Vue from 'vue';
import $ from "jquery";
import _ from 'lodash';

class FilterController{
    constructor(){}
    getValues(){}
}

/*class Filter extends React.Component{
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
}*/

let Filter = Vue.component("wbwpf-filter",{
    data(){
        return {
            current_values: []
        }
    },
    props: ['label','slug','hidden'],
    created: function(){
        debugger;
    },
});

export {Filter, FilterController}
