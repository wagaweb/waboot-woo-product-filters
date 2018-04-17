<template>
    <div :id="'slider-'+this.key" class="range-slider"></div>
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
            }
        },
        data(){
            return {
                key: ''
            }
        },
        created(){
            this.key = this.generateKey();
        },
        mounted(){
            let slider = document.getElementById('slider-'+this.key);
            noUiSlider.create(slider, {
                range: {
                    'min': this.min,
                    'max': this.max
                },
                start: [this.min,this.max],
                connect: true,
                behavior: 'tap-drag',
            });
        },
        methods: {
            generateKey(){
                let text = "",
                    possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
                for (let i = 0; i < 5; i++){
                    text += possible.charAt(Math.floor(Math.random() * possible.length));
                }
                return text;
            }
        }
    }
</script>

<style lang="scss">
    .range-slider{
        margin: 10px auto;
    }
</style>