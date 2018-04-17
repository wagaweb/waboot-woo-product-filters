<template>
    <div :id="'slider-'+key"></div>
</template>

<script>
    import noUiSlider from 'nouislider';
    module.export = {
        data(){
            return {
                key: '',
                min: '',
                max: '',
                values: []
            }
        },
        computed: {
            key() {
                let text = "",
                    possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
                for (let i = 0; i < 5; i++){
                    text += possible.charAt(Math.floor(Math.random() * possible.length));
                }
                return text;
            },
            range() {
                return [];
            }
        },
        created(){
            this.key = this.generateKey();
        },
        mounted(){
            let slider = document.getElementById('slider-'+key);
            debugger;
            noUiSlider.create(slider, {
                connect: true,
                behavior: 'tap',
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