<?php
namespace WBWPF\widgets;

use WBF\components\widgets\WBF_Widget;
use WBWPF\Plugin;

class Filters extends WBF_Widget {
	/**
	 * Filters constructor.
	 */
	public function __construct() {
		$plugin = Plugin::get_instance_from_global();
		if(!$plugin instanceof Plugin) return;

		parent::__construct( "waboot_woo_product_filters_widget", _x("Waboot Product Filters","Widget name","waboot-woo-product-filters") );
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$plugin = Plugin::get_instance_from_global();
		$settings = $plugin->get_plugin_settings();
		if(!$plugin instanceof Plugin) return;

		$default = [
			'title' => '',
			'use_async' => false
		];
		$instance = wp_parse_args($instance,$default);

		if(!function_exists("is_shop")) return;

		if(is_shop() || is_product_category()){

			echo $args['before_widget']; ?>

			<h3 class="widget-title"><?php echo $instance['title']; ?></h3>

			<?php
			$display_apply_button = $settings['widget_display_apply_button'];

			if($settings['widget_use_js']){
				wbwpf_show_filters([],true,$display_apply_button);
			}else{
				wbwpf_show_filters([],false,$display_apply_button);
			}

			echo $args['after_widget'];
		}
	}

	/**
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$plugin = Plugin::get_instance_from_global();
        if(!isset($new_instance['display_apply_button'])){
            //If the user chose to to not display the apply button, we force the plugin to use the async version of the product list.
            //Otherwise, filters can not be applied.
            $plugin->Settings->save_plugin_settings(['use_async_product_list'=>true]);
        }else{
            //With the apply button, we do not need the async product list
	        $plugin->Settings->save_plugin_settings(['use_async_product_list'=>false]);
        }
        if(!isset($new_instance['use_async'])){
            //If the filters are static, we can't use the async product list
	        $plugin->Settings->save_plugin_settings(['use_async_product_list'=>false]);
        }

		return parent::update( $new_instance, $old_instance );
	}

	/**
	 * @param array $instance
	 * @return void
	 */
	public function form( $instance ) {
		$plugin = Plugin::get_instance_from_global();
		if(!$plugin instanceof Plugin) return;

		$form_options = [
			'title' => [
				'type' => 'text',
				'label' => _x('Title','Widget title',$plugin->get_textdomain()),
				'default' => ''
			],
			/*'use_async' => [
				'type' => 'checkbox',
				'label' => sprintf(_x("Update filters asynchronously.<br />If you leave this unchecked, make sure 'Use async product list' is also unchecked in <a href='%s' title='Go to settings'>settings</a>.","Widget option",$plugin->get_textdomain()),admin_url("admin.php?page=wbwpf_settings&tab=options")),
				'default' => false
			],
      'display_apply_button' => [
        'type' => 'checkbox',
        'label' => sprintf(_x("Display 'search' button (with async filters).<br />If you choose not to, you have to select 'Use async product list' in <a href='%s' title='Go to settings'>settings</a>.","Widget option",$plugin->get_textdomain()),admin_url("admin.php?page=wbwpf_settings&tab=options")),
        'default' => true
      ]*/
		];

		$this->print_options($instance,$form_options);
	}
}
