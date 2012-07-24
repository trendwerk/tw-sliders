<?php
/*
Plugin Name: UI Sliders
Plugin URI: http://www.trendwerk.nl/
Description: User interface for managing sliders
Author: Ontwerpstudio Trendwerk
Version: 1.0
Author URI: http://www.trendwerk.nl/
*/

//Includes
include('includes/uis.UISlider.php');
include('includes/uis.functions.php');

//Admin page
function uis_add_menu() {
	add_theme_page(__('Sliders','ui-sliders'),__('Sliders','ui-sliders'),'read','ui-sliders','uis_admin_page');
}
add_action('admin_menu','uis_add_menu');

function uis_admin_page() {
	uis_save_changes();
	
	$slider = uis_get_current_slider(); //Get or create current slider
	$sliders = uis_get_all_sliders(); //Get all sliders
	
	include('admin/uis.admin-page.php');
}

function uis_admin_css() {
	echo '<link href="'.plugin_dir_url(__FILE__).'css/admin.css" type="text/css" rel="stylesheet" />';
}
add_action('admin_head','uis_admin_css');

//"Settings" link
function uis_add_settings_link($links,$file) {
	static $this_plugin;
	if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);

	if($file == $this_plugin) {
		$links['settings'] = '<a href="'.admin_url('themes.php?page=ui-sliders').'">'.__('Settings').'</a>';
	}
	
	return $links;
}
add_filter('plugin_action_links', 'uis_add_settings_link',10,2);

//JS
function uis_admin_js() {
	echo '<script src="'.plugin_dir_url(__FILE__).'js/edit-slider.js" type="text/javascript"></script>';
}
add_action('admin_head','uis_admin_js');

//Languages
function uis_add_translations() {
  load_plugin_textdomain('ui-sliders',false,dirname(plugin_basename(__FILE__)).'/languages/');
}
add_action('init','uis_add_translations');
?>