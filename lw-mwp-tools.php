<?php
/*
Plugin Name: LW MWP Tools
Plugin URI: https://github.com/fs1995/lw-mwp-tools/
Description: Easy access to system logs and resource usage on the Liquid Web Managed WordPress Hosting Platform.
Author: Francis Smith
Version: 0.1
Author URI: https://github.com/fs1995
License: GPL2
*/

//check if we are on MWPv2 platform
$is_lwmwp = 1;
if (get_current_user()[0] !== 's')
  $is_lwmwp = 0;
if (!is_numeric(get_current_user()[1]))
  $is_lwmwp = 0;
if(!$is_lwmwp)
  exit("This plugin requires the Liquid Web Managed WordPress V2 platform.");

add_action('admin_menu', 'lw_mwp_tools_menu');

function lw_mwp_tools_menu(){
  add_menu_page('LW MWP Tools', 'LW MWP Tools', 'manage_options', 'lw-mwp-tools',  'lw_mwp_tools_init');
  add_submenu_page ('lw-mwp-tools', 'Server Resource Monitor', 'Resource Monitor', 'manage_options', 'lw-mwp-tools', 'lw_mwp_tools_init');
  add_submenu_page ('lw-mwp-tools', 'PHP error log', 'PHP error log', 'manage_options', 'lw-mwp-tools-php', 'lw_mwp_tools_php');
  add_submenu_page ('lw-mwp-tools', 'NGINX access log', 'NGINX access log', 'manage_options', 'lw-mwp-tools-nginx-access', 'lw_mwp_tools_nginx_access');
  add_submenu_page ('lw-mwp-tools', 'NGINX error log', 'NGINX error log', 'manage_options', 'lw-mwp-tools-nginx-error', 'lw_mwp_tools_nginx_error');
  //add_submenu_page ('null', 'NGINX log', '', 'manage_options', 'lw-mwp-tools-nginx', 'lw_mwp_tools_nginx');
}

function lw_mwp_tools_init(){
  require 'core.php';
}

function lw_mwp_tools_php(){
  $lw_mwp_tools_log = file_get_contents('/var/log/' . get_current_user() . '-php-fpm-errors.log') or exit("Unable to access PHP error log. Please report this <a href=\"https://wordpress.org/support/plugin/lw-mwp-tools\" target=\"_blank\">here</a>.");
  echo "<h2>PHP Error Log viewer</h2>This page does not automatically update, you will need to refresh it. If you are troubleshooting WordPress code, have you turned on <a href=\"https://codex.wordpress.org/Debugging_in_WordPress\" target=\"_blank\">WP_DEBUG</a> in wp-config.php?<pre>" . $lw_mwp_tools_log . "</pre>"; //NGINX access and error logs can be found <a href=\"" . admin_url('admin.php?page=lw-mwp-tools-nginx') . "\">in the Managed WordPress control panel</a>.
}

function lw_mwp_tools_nginx_access(){
  $lw_mwp_tools_log = file_get_contents('/var/log/nginx/' . get_current_user() . '.access.log') or exit("Unable to access NGINX access log. Please report this <a href=\"https://wordpress.org/support/plugin/lw-mwp-tools\" target=\"_blank\">here</a>.");
  echo "<h2>NGINX access Log viewer</h2>This page does not automatically update, you will need to refresh it.<pre>" . $lw_mwp_tools_log . "</pre>";
}

function lw_mwp_tools_nginx_error(){
  $lw_mwp_tools_log = file_get_contents('/var/log/nginx/' . get_current_user() . '.error.log') or exit("Unable to access NGINX error log. Please report this <a href=\"https://wordpress.org/support/plugin/lw-mwp-tools\" target=\"_blank\">here</a>.");
  echo "<h2>NGINX Error Log viewer</h2>This page does not automatically update, you will need to refresh it.<pre>" . $lw_mwp_tools_log . "</pre>";
}

/*function lw_mwp_tools_nginx(){
  echo "<h2>NGINX logs can be found here:</h2><img src=\"" . plugins_url('nginx_logs.png', __FILE__ ) . "\" height=\"617\" width=\"1027\">";
}*/
