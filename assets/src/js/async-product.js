import Vue from 'vue';
import $ from "jquery";
import _ from 'lodash';

let Product = Vue.component('wbwpf-product',{
    template: "#wbwpf-product-template",
    props: ['data']
});

export { Product }