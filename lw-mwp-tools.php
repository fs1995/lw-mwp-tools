<?php
/*
Plugin Name: LW MWP Tools
Plugin URI: https://github.com/fs1995/lw-mwp-tools/
Description: Easy access to system logs and resource usage on the Liquid Web Managed WordPress Hosting Platform.
Author: Francis Smith
Version: 0.3.4
Author URI: https://github.com/fs1995
License: GPL2
*/

defined('ABSPATH') or die('No!');

//check if we are on MWPv2 platform. this is not a thorough check, just seeing if the user php is running as begins with 's' then a number.
$is_lwmwp = 1;
if (PHP_OS !== "Linux")
  $is_lwmwp = 0;
if (get_current_user()[0] !== 's')
  $is_lwmwp = 0;
if (!is_numeric(get_current_user()[1]))
  $is_lwmwp = 0;
if(!$is_lwmwp){ //If not on MWP:
  delete_option('lwmwptools_update_interval'); //then cleanup db entry,
  exit("This plugin requires the Liquid Web Managed WordPress platform."); //and prevent plugin from activating.
}

function lwmwptools_readlog($file){
  if(file_exists($file)){
    if(filesize($file) > '0'){
      if(is_readable($file)){
        return "Reading file: ".$file."<hr>".file_get_contents($file);
      }else{
        return "Error: The file ".$file." is not readable. Please report this <a href=\"https://wordpress.org/support/plugin/lw-mwp-tools\" target=\"_blank\">here</a>.";
      }
    }else{
      return "The file ".$file." is empty.";
    }
  }else{
    return "Error: The file ".$file." does not exist. Please report this <a href=\"https://wordpress.org/support/plugin/lw-mwp-tools\" target=\"_blank\">here</a>.";
  }
}

add_action('admin_menu', 'lw_mwp_tools_menu'); //hook into WP menu
add_action('wp_ajax_lwmwptools_monitorajax', 'lwmwptools_monitorajax'); //ajax request handler

function lw_mwp_tools_menu(){ //create the plugins menu
  add_menu_page('LW MWP Tools', 'LW MWP Tools', 'manage_options', 'lw-mwp-tools',  'lw_mwp_tools_monitor');
  add_submenu_page ('lw-mwp-tools', 'Server Resource Monitor', 'Resource Monitor', 'manage_options', 'lw-mwp-tools', 'lw_mwp_tools_monitor');
  add_submenu_page ('lw-mwp-tools', 'System Information', 'System Info', 'manage_options', 'lw-mwp-tools-info', 'lw_mwp_tools_info');
  add_submenu_page ('lw-mwp-tools', 'Clear cache', 'Clear cache', 'manage_options', 'lw-mwp-tools-cache', 'lw_mwp_tools_cache');
  add_submenu_page ('lw-mwp-tools', 'PHP error log', 'PHP error log', 'manage_options', 'lw-mwp-tools-php', 'lw_mwp_tools_php');
  add_submenu_page ('lw-mwp-tools', 'NGINX access log', 'NGINX access log', 'manage_options', 'lw-mwp-tools-nginx-access', 'lw_mwp_tools_nginx_access');
  add_submenu_page ('lw-mwp-tools', 'NGINX error log', 'NGINX error log', 'manage_options', 'lw-mwp-tools-nginx-error', 'lw_mwp_tools_nginx_error');

  add_action('admin_init', 'register_lwmwptools_settings');
}

function lw_mwp_tools_monitor(){ //generate the resource monitor page
  require 'monitor.php'; //in a separate file cause theres a bit to this page.
  wp_enqueue_style('lwmwptools-chartistcss', plugins_url('css/chartist.min.css', __FILE__) );
  wp_enqueue_style('lwmwtptools-monitorcss', plugins_url('css/monitor.css', __FILE__), array('lwmwptools-chartistcss') );
  wp_enqueue_script('lwmwptools-chartistjs', plugins_url('js/chartist.min.js', __FILE__) );
  wp_enqueue_script('lwmwptools-smoothiejs', plugins_url('js/smoothie.min.js', __FILE__) );
  wp_enqueue_script('lwmwptools-monitorjs', plugins_url('js/monitor.js', __FILE__), array('lwmwptools-chartistjs', 'jquery') );
}

function lw_mwp_tools_info(){ //generate the resource monitor page
  $lwmwptools_uptime = floatval(file_get_contents('/proc/uptime')); //read uptime
  $lwmwptools_uptime_secs = round(fmod($lwmwptools_uptime, 60), 0); $lwmwptools_uptime = (int)($lwmwptools_uptime / 60);
  $lwmwptools_uptime_mins = $lwmwptools_uptime % 60; $lwmwptools_uptime = (int)($lwmwptools_uptime / 60);
  $lwmwptools_uptime_hr = $lwmwptools_uptime % 24; $lwmwptools_uptime = (int)($lwmwptools_uptime / 24);
  $lwmwptools_uptime_days = $lwmwptools_uptime;

  echo "<div class=\"wrap\"><h1>System Information</h1>Hostname: ", gethostname(), "<br>Uptime: ", $lwmwptools_uptime_days, " days, ", $lwmwptools_uptime_hr, " hours, ", $lwmwptools_uptime_mins, " minutes, ", $lwmwptools_uptime_secs, " seconds.<br>Server IP: ", $_SERVER['SERVER_ADDR'], "<br>PHP version: ", phpversion(), "<br>Platform: ", PHP_OS,  "</div>";
}

function lw_mwp_tools_cache(){
  echo "<div class=\"wrap\"><h1>Clear cache</h1>If changes are not showing up, you may need to clear cache.<br><br><form method=\"post\" action=\"\">";

  if(isset($_POST['lwmwptools-static'])){ //delete static cache button was clicked
    function lwmwptools_filecache($dir){
      if(is_dir($dir)){ //if we are given a directory...
        $objects = scandir($dir); //list all files/dirs within it...
        foreach ($objects as $object){
          if ($object != "." && $object != "..") {
            if(is_dir($dir."/".$object)){ //if we come across a directory within it...
              lwmwptools_filecache($dir."/".$object); //will need to remove all files within that dir.
            }else{
              unlink($dir."/".$object); //else, delete the individual files.
              echo "Deleted file: ". $dir."/".$object."<br>";
            }
          }
        }
        rmdir($dir); //we are done with the foreach object loop and have an empty directory, delete it.
        echo "Removed directory: ".$dir."<br>";
      }
    }

    lwmwptools_filecache(WP_CONTENT_DIR."/cache");
    echo "Done clearing file cache!<br>";
  }

  if(isset($_POST['lwmwptools-opcache'])){ //flush opcache button was clicked
    wp_cache_flush();
    if(opcache_reset()){ //reqs php 5.5+
      echo "OpCache cleared!<br>";
    }else{
      echo "Error: opcode cache seems to be disabled.<br>";
    }
  }

  echo "<br><input type=\"submit\" name=\"lwmwptools-static\" value=\"Delete static file cache\" /> Delete contents of wp-content/cache/<br><br>";
  echo "<input type=\"submit\" name=\"lwmwptools-opcache\" value=\"Clear opcode cache\" /> Flush PHP OpCache<br><br></form></div>";
  echo "Your browser could be caching too: <a href=\"https://www.liquidweb.com/kb/clearing-your-browser-cache/\" target=\"_blank\">how to clear browser cache</a>";
}

function lw_mwp_tools_php(){ //generate the php error log page
  $lw_mwp_tools_log = lwmwptools_readlog('/var/log/' . get_current_user() . '-php-fpm-errors.log'); //try to get the php error log
  echo "<div class=\"wrap\"><h1>PHP Error Log viewer</h1>This page does not automatically update, you will need to refresh it. If you are troubleshooting WordPress code, have you turned on <a href=\"https://codex.wordpress.org/Debugging_in_WordPress\" target=\"_blank\">WP_DEBUG</a> in wp-config.php?</div><pre>" . $lw_mwp_tools_log . "</pre>";
}

function lw_mwp_tools_nginx_access(){ //generate the nginx access log page
  $lw_mwp_tools_log = lwmwptools_readlog('/var/log/nginx/' . get_current_user() . '.access.log');
  echo "<div class=\"wrap\"><h1>NGINX access Log viewer</h1>This page does not automatically update, you will need to refresh it.</div><pre>" . $lw_mwp_tools_log . "</pre>";
}

function lw_mwp_tools_nginx_error(){ //generate the nginx error log page
  $lw_mwp_tools_log = lwmwptools_readlog('/var/log/nginx/' . get_current_user() . '.error.log');
  echo "<div class=\"wrap\"><h1>NGINX Error Log viewer</h1>This page does not automatically update, you will need to refresh it.</dev><pre>" . $lw_mwp_tools_log . "</pre>";
}

function register_lwmwptools_settings(){ //register the plugins settings
  register_setting('lwmwptools-settings-group', 'lwmwptools_update_interval', 'absint');
}

function lwmwptools_monitorajax(){
  //global $wpdb; //provides access to db
  //$test = intval( $_POST['test'] );
  require 'api.php';
  wp_die(); //terminate immediately and return response
}
