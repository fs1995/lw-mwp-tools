<?php
/*
Plugin Name: LW MWP Tools
Plugin URI: https://github.com/fs1995/lw-mwp-tools/
Description: Easy access to system logs and resource usage on the Liquid Web Managed WordPress Hosting Platform.
Author: Francis Smith
Version: 0.3.2
Author URI: https://github.com/fs1995
License: GPL2
*/

defined('ABSPATH') or die('No!');

//check if we are on MWPv2 platform. this is not a thorough check, just seeing if the user php is running as begins with 's' then a number.
$is_lwmwp = 1;
if (get_current_user()[0] !== 's')
  $is_lwmwp = 0;
if (!is_numeric(get_current_user()[1]))
  $is_lwmwp = 0;
if(!$is_lwmwp)
  exit("This plugin requires the Liquid Web Managed WordPress V2 platform."); //prevent plugin from activating if not MWPv2.

add_action('admin_menu', 'lw_mwp_tools_menu'); //hook into WP menu

function lw_mwp_tools_menu(){ //create the plugins menu
  add_menu_page('LW MWP Tools', 'LW MWP Tools', 'manage_options', 'lw-mwp-tools',  'lw_mwp_tools_monitor');
  add_submenu_page ('lw-mwp-tools', 'Server Resource Monitor', 'Resource Monitor', 'manage_options', 'lw-mwp-tools', 'lw_mwp_tools_monitor');
  add_submenu_page ('lw-mwp-tools', 'System Information', 'System Info', 'manage_options', 'lw-mwp-tools-info', 'lw_mwp_tools_info');
  add_submenu_page ('lw-mwp-tools', 'PHP error log', 'PHP error log', 'manage_options', 'lw-mwp-tools-php', 'lw_mwp_tools_php');
  add_submenu_page ('lw-mwp-tools', 'NGINX access log', 'NGINX access log', 'manage_options', 'lw-mwp-tools-nginx-access', 'lw_mwp_tools_nginx_access');
  add_submenu_page ('lw-mwp-tools', 'NGINX error log', 'NGINX error log', 'manage_options', 'lw-mwp-tools-nginx-error', 'lw_mwp_tools_nginx_error');

  add_action('admin_init', 'register_lwmwptools_settings');
}

function lw_mwp_tools_monitor(){ //generate the resource monitor page
  require 'monitor.php'; //in a separate file cause theres a bit to this page.
}

function lw_mwp_tools_info(){ //generate the resource monitor page
  echo "<h1>System Information</h1>Hostname: " . gethostname() . "<br>PHP version: " . phpversion() . "<br>Platform: " . PHP_OS;
}

function lw_mwp_tools_php(){ //generate the php error log page
  $lw_mwp_tools_log = file_get_contents('/var/log/' . get_current_user() . '-php-fpm-errors.log') or exit("Unable to access PHP error log. Please report this <a href=\"https://wordpress.org/support/plugin/lw-mwp-tools\" target=\"_blank\">here</a>."); //try to get the php error log
  echo "<h1>PHP Error Log viewer</h1>This page does not automatically update, you will need to refresh it. If you are troubleshooting WordPress code, have you turned on <a href=\"https://codex.wordpress.org/Debugging_in_WordPress\" target=\"_blank\">WP_DEBUG</a> in wp-config.php?<pre>" . $lw_mwp_tools_log . "</pre>";
}

function lw_mwp_tools_nginx_access(){ //generate the nginx access log page
  $lw_mwp_tools_log = file_get_contents('/var/log/nginx/' . get_current_user() . '.access.log') or exit("Unable to access NGINX access log. Please report this <a href=\"https://wordpress.org/support/plugin/lw-mwp-tools\" target=\"_blank\">here</a>.");
  echo "<h1>NGINX access Log viewer</h1>This page does not automatically update, you will need to refresh it.<pre>" . $lw_mwp_tools_log . "</pre>";
}

function lw_mwp_tools_nginx_error(){ //generate the nginx error log page
  $lw_mwp_tools_log = file_get_contents('/var/log/nginx/' . get_current_user() . '.error.log') or exit("Unable to access NGINX error log. Please report this <a href=\"https://wordpress.org/support/plugin/lw-mwp-tools\" target=\"_blank\">here</a>.");
  echo "<h2>NGINX Error Log viewer</h2>This page does not automatically update, you will need to refresh it.<pre>" . $lw_mwp_tools_log . "</pre>";
}

function register_lwmwptools_settings(){ //register the plugins settings
  register_setting('lwmwptools-settings-group', 'lwmwptools_update_interval');
}
