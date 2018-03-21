export default {
    props: {
        'outerWrapper': {
            type: String,
            default: 'ul'
        },
        'innerWrapper': {
            type: String,
            default: 'li'
        },
        'current_page': Number,
        'total_pages': Number,
        'mid_size': {
            type: [String,Number],
            default: 3
        }
    },
    computed: {
        next_page: function(){
            if(this.current_page === this.total_pages){
                return this.total_pages;
            }else{
                return this.current_page + 1;
            }
        },
        prev_page: function(){
            if(this.current_page === 1 || this.total_pages === 1){
                return 1;
            }else{
                return this.current_page - 1;
            }
        }
    },
    created(){},
    mounted(){},
    render(createElement){
        let output,
            innerElements = [];

        /**
         * @param {number} number
         * @param {Array} to
         * @param {Boolean} is_current
         * @returns {Array}
         */
        let pushPage = (number,to,is_current = false) => {
            to.push(createElement(this.innerWrapper,{
                'class': {
                    'wbwpf-navigation-item': true,
                    'current': is_current,
                    'page-link': true
                }
            },[
                createElement("a",{
                    domProps: {
                        innerHTML: number
                    },
                    attrs: {
                        href: "#page"+number,
                        title: "Go to page "+number,
                        'data-goto': number
                    },
                    on: {
                        click: this.changePage
                    }
                })
            ]));
            return to;
        };

        /**
         * @param {Array} to
         * @returns {Array}
         */
        let pushDots = (to) => {
            to.push(createElement(this.innerWrapper,{
                'class': {
                    'wbwpf-navigation-item': true,
                    'separator': true
                }
            },[
                createElement("span",{
                    domProps: {
                        innerHTML: "..."
                    }
                })
            ]));
            return to;
        };

        let can_push_dots = true;
        let midrange = this.getMidRange(this.mid_size,this.current_page);

        for(let i = 1; i <= this.total_pages; i++){
            let is_first_half = 1 <= this.current_page && this.current_page <= this.mid_size;
            let is_last_half = (this.total_pages - this.mid_size) <= this.current_page && this.current_page <= this.total_pages;

            if( (is_first_half && i <= this.mid_size) || i === 1){ //We are in the head part of the pagination (current page is below mid size)
                innerElements = pushPage(i,innerElements, i === this.current_page);
            }else if( (is_last_half && i >= this.total_pages - this.mid_size) || i === this.total_pages){ //We are in the last part of the pagination (current page is between total pages and total pages - mid size)
                innerElements = pushPage(i,innerElements, i === this.current_page);
            }else if(_.indexOf(midrange,i) !== -1){ //We are in the mid range
                innerElements = pushPage(i,innerElements, i === this.current_page);
                can_push_dots = true;
            }else{
                if(can_push_dots){ //Otherwise, put separator
                    innerElements = pushDots(innerElements);
                    can_push_dots = false;
                }
            }
        }
        output = createElement(this.outerWrapper,{
            'class': {
                'wbwpf-navigation-wrapper': true
            }
        },innerElements);
        return output;
    },
    methods: {
        /**
         * Handles the click event on a page link. Emits "pageChanged" from window.ProductList, which in turn force the product update.
         * @param event
         */
        changePage(event){
            event.preventDefault();
            let $clickedLink = jQuery(event.target);
            let pageToGo = $clickedLink.data('goto');
            this.$store.commit('appIsNotJustStarted');
            this.$emit("pageChanged",pageToGo);
            jQuery(window).trigger("pageChanged");
        },
        /**
         * Detects the mid range for pagination.
         *
         * @param {number} range_size
         * @param {number} range_pivot
         *
         * @example: put range_size = 3, range_pivot = 25 => [24,25,26]
         * @example: put range_size = 4, range_pivot = 25 => [23,24,25,26]
         */
        getMidRange(range_size,range_pivot){
            if(range_size % 2 === 0){
                var tmp = range_size / 2;
                var tail_size_left = tmp;
                var tail_size_right = tail_size_left - 1;
            }else{
                var tmp = range_size - 1;
                var tail_size_left = tmp / 2;
                var tail_size_right = tmp / 2;
            }
            let range = [];
            for(let i = tail_size_left; i >= 1; i--){
                range.push(range_pivot-i);
            }
            range.push(range_pivot);
            for(let i = 1; i <= tail_size_right; i++){
                range.push(range_pivot+i);
            }
            return range;
        }
    }
}