<?php

namespace WBWPF\includes;

use WBWPF\Plugin;

interface Settings_Manager_Interface{
	public function get_plugin_default_settings();
	public function get_plugin_settings();
	public function save_plugin_settings($settings,$autodetect_types);
	public function set_plugin(Plugin &$plugin);
}