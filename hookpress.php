<?php
/*
Plugin Name: HookPress
Plugin URI: http://mitcho.com/code/hookpress/
Description: HookPress turns all of your WordPress-internal hooks into webhooks. Possible uses include generating push notifications or using non-PHP web technology to extend WordPress. Read more about webhooks at <a href='http://webhooks.org/'>the webhooks site</a>.
Version: 0.1.8
Author: mitcho (Michael Yoshitaka Erlewine)
Author URI: http://mitcho.com/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=66G4DATK4999L&item_name=mitcho%2ecom%2fcode%2fhookpress%3a%20donate%20to%20Michael%20Yoshitaka%20Erlewine&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&charset=UTF%2d8
*/

define('HOOKPRESS_PRIORITY',12838790321);
require('includes.php');

function hookpress_init() {
	global $hookpress_version, $hookpress_value_options;
  $hookpress_version = "0.1.8";
  if (version_compare($hookpress_version,get_option('hookpress_version')) > 0)
    update_option('hookpress_version',$hookpress_version);

  foreach ($hookpress_value_options as $key => $value) {
    if (get_option("hookpress_$key") == '')
      update_option("hookpress_$key",$value);
  }

	add_action('admin_menu', 'hookpress_config_page');
}
add_action('init', 'hookpress_init');
hookpress_register_hooks();

// register ajax service
add_action('wp_ajax_hookpress_get_fields', 'hookpress_ajax_get_fields');
add_action('wp_ajax_hookpress_add_fields', 'hookpress_ajax_add_fields');
add_action('wp_ajax_hookpress_delete_hook', 'hookpress_ajax_delete_hook');
add_action('wp_ajax_hookpress_get_hooks', 'hookpress_ajax_get_hooks');
add_action('wp_ajax_hookpress_set_enabled', 'hookpress_ajax_set_enabled');

function hookpress_config_page() {
  $hook = add_submenu_page('options-general.php', __('Webhooks','hookpress'), __('Webhooks','hookpress'), 'manage_options', 'webhooks', 'hookpress_options');
	add_action("load-$hook",'hookpress_load_thickbox');
}

function hookpress_load_thickbox() {
	wp_enqueue_script( 'thickbox' );
	if (function_exists('wp_enqueue_style')) {
		wp_enqueue_style( 'thickbox' );
	}
}

function hookpress_options() {
  global $wpdb, $hookpress_actions, $hookpress_version;
	require(str_replace('hookpress.php','options.php',__FILE__));
}
