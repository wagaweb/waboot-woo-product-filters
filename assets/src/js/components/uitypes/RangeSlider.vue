<template>
    <div class="slider-wrapper">
        <div :id="'slider-'+this.key" class="range-slider"></div>
        {{ this.selectedMin }} - {{ this.selectedMax }}
    </div>
</template>

<script>
    import noUiSlider from 'nouislider';
    module.exports = {
        props: {
            'min' : {
                type: Number,
                required: true,
                default: function(){
                    return 0;
                }
            },
            'max' : {
                type: Number,
                required: true
            },
            'values': {
                type: Array,
                required: true,
                default: function(){
                    return []
                }
            },
        },
        data(){
            return {
                key: '',
                sliderEl: undefined,
                sliderInstance: undefined,
                selectedMin: 0,
                selectedMax: 0,
                selectedValues: []
            }
        },
        computed: {
            range: function(){
                let range = {};
                range['min'] = this.min;
                for(let i = 0; i < this.values.length; i++){
                    let percentage = (this.values[i] / this.max) * 100;
                    if(this.values[i] !== this.min && this.values[i] !== this.max){
                        range[parseInt(percentage)+'%'] = this.values[i];
                    }
                }
                range['max'] = this.max;
                return range;
            }
        },
        created(){
            this.key = this.generateKey();
        },
        mounted(){
            let slider = document.getElementById('slider-'+this.key);

            if(typeof slider !== 'undefined'){
                this.sliderEl = slider;
                this.sliderInstance = noUiSlider.create(slider, {
                    range: this.range,
                    start: [this.min,this.max],
                    snap: true,
                    connect: true,
                    behavior: 'tap-drag',
                });
                this.updateRangeValues();
                this.sliderInstance.on('change', () => {
                    this.updateRangeValues();
                });
            }
        },
        methods: {
            generateKey(){
                let text = "",
                    possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
                for (let i = 0; i < 5; i++){
                    text += possible.charAt(Math.floor(Math.random() * possible.length));
                }
                return text;
            },
            updateRangeValues(){
                if(this.sliderInstance){
                    let v = this.sliderInstance.get();
                    this.selectedMin = v[0];
                    this.selectedMax = v[1];
                    this.selectedValues = v;
                }else{
                    this.selectedMin = this.min;
                    this.selectedMax = this.max;
                    this.selectedValues = [this.min,this.max];
                }
            }
        }
    }
</script>

<style lang="scss">
    .range-slider{
        margin: 10px auto;
    }
</style>