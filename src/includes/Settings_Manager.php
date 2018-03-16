<?php
namespace WBWPF\includes;

use WBWPF\Plugin;

class Settings_Manager{
	const SETTINGS_OPTION_NAME = "wbwpf_settings";

	/**
	 * @var Plugin
	 */
	var $plugin;

	public function __construct(Plugin &$plugin) {
		$this->plugin = $plugin;
	}

	/**
	 * Get the default settings
	 *
	 * @return array
	 */
	public function get_plugin_default_settings(){
		$defaults = [
			'filters' => [],
			'filters_params' => [],
			'show_variations' => false,
			'hide_parent_products' => true,
			'use_async_product_list' => false,
			'use_custom_product_loop_template' => false,
			'widget_display_apply_button' => false,
		];
		$defaults = apply_filters("wbwpf/settings/defaults",$defaults);
		return $defaults;
	}

	/**
	 * Save the plugin settings
	 *
	 * @param array $settings
	 * @param bool $autodetect_types
	 */
	public function save_plugin_settings($settings,$autodetect_types = true){
		$actual = $this->get_plugin_settings(); //Get current values

		//Do some standardizations
		$settings['show_variations'] = isset($settings['show_variations']) ? (bool) $settings['show_variations'] : false;
		$settings['hide_parent_products'] = isset($settings['hide_parent_products']) ? (bool) $settings['hide_parent_products'] : false;
		$settings['use_async_product_list'] = isset($settings['use_async_product_list']) ? (bool) $settings['use_async_product_list'] : false;
		$settings['use_custom_product_loop_template'] = isset($settings['use_custom_product_loop_template']) ? (bool) $settings['use_custom_product_loop_template'] : false;

		//Merge the differences
		$settings = wp_parse_args($settings,$actual);

		if($autodetect_types){
			//Automatically detect dataType and uiType params
			$dataType_data_to_ui_relations = $this->plugin->get_dataType_uiType_relations();
			$get_uiType_of_dataType = function($dataType) use($dataType_data_to_ui_relations){
				foreach ($dataType_data_to_ui_relations as $k => $v){
					if($k == $dataType){
						return $v;
					}
				}
				return false;
			};
			unset($settings['filters_params']);
			foreach ($settings['filters'] as $dataType_slug => $filter_slugs){
				foreach ($filter_slugs as $filter_slug){
					$settings['filters_params'][$filter_slug]['dataType'] = $dataType_slug;
					$settings['filters_params'][$filter_slug]['uiType'] = $get_uiType_of_dataType($dataType_slug);
				}
			}
		}

		update_option(self::SETTINGS_OPTION_NAME,$settings);
	}

	/**
	 * Get the plugin settings
	 *
	 * @return array
	 */
	public function get_plugin_settings(){
		$defaults = $this->get_plugin_default_settings();
		$settings = get_option(self::SETTINGS_OPTION_NAME);
		$settings = wp_parse_args($settings,$defaults);
		return $settings;
	}

	/**
	 * Automatically retrieve setting by $name
	 *
	 * @param $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		$settings = $this->get_plugin_settings();
		if(isset($settings[$name])){
			return $settings[$name];
		}else{
			$trace = debug_backtrace();
			trigger_error(
				'Undefined property via __get(): ' . $name .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_NOTICE);
			return null;
		}
	}
}