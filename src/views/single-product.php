<?php if(wp_get_theme()->get_template() === 'waboot'): ?>
	<div class="wbwpf-single-product-wrapper" v-html="data.content"></div>
<?php else: ?>
	<ul :class="data.wrapper_class" v-html="data.content"></ul>
<?php endif;
