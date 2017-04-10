import React from 'react';
import $ from "jquery";
import _ from 'lodash';

class FilterController{
    constructor(){}
    getValues(){}
}

class Filter extends React.Component{
    constructor(props){
        debugger;
        super(props);
        this.Controller = new FilterController();
        this.tpl = _template();
        this.state = {
            current_values: {}
        };
    }

    componentDidMount(){
        debugger;
        this.Controller.getValues().then(function(){
            debugger;
            //Resolve
        },function(){
            debugger;
            //Reject
        });
    }

    render(){
        debugger;
    }
}

export {Filter,FilterController}
