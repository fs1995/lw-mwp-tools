<?php defined('ABSPATH') or die('No!'); ?>

<div class="wrap">
<h1>Server Resource Monitor</h1>

Load average: <span id="load_1"></span> <span id="load_5"></span> <span id="load_15"></span><br>
Cores: <span id="cores"></span><br><br>

<table border="1" style="text-align:center">
  <tr>
    <td><div class="ct-chart ct-square" id="chart_ram" style="height:150px;width:150px;"></div></td>
    <td><div class="ct-chart ct-square" id="chart_swap" style="height:150px;width:150px;"></div></td>
    <td><div class="ct-chart ct-square" id="chart_disk" style="height:150px;width:150px;"></div></td>
  </tr>
  <tr>
    <td>RAM (<span id="ram_pct"></span> %)</td>
    <td>Swap (<span id="swap_pct"></span> %)</td>
    <td>Hard Disk (<span id="disk_pct"></span> %)</td>
  </tr>
  <tr>
    <td>Total: <span id="ram_total"></span> MB<br>
      Used: <span id="ram_used"></span> MB<br>
      Available: <span id="ram_avail"></span> MB<!--<br><br>
      Free: <span id="ram_free"></span> MB<br>
      Buffers: <span id="ram_buffers"></span> MB<br>
      Cached: <span id="ram_cached"></span> MB--></td>
    <td>Total: <span id="swap_total"></span> MB<br>
      Used: <span id="swap_used"></span> MB<br>
      Free: <span id="swap_free"></span> MB</td>
    <td>Total: <span id="disk_total"></span> GB<br>
      Used: <span id="disk_used"></span> GB<br>
      Free: <span id="disk_free"></span> GB</td>
  </tr>
</table><br><br>

<form method="post" action="options.php"> <!--the update interval setting, with a default of 2 seconds-->
  <?php settings_fields('lwmwptools-settings-group'); ?>
  <?php do_settings_sections('lwmwptools-settings-group'); ?>
  Update interval (seconds): <input type="text" name="lwmwptools_update_interval" id="update_interval" value="<?php echo esc_attr(get_option('lwmwptools_update_interval', "5") ); ?>" maxlength="4" size="3" />
  <?php submit_button("Set", '', '', false); ?>
</form>

<br><h2>Bug report or suggestion?</h2>
Let us know <a href="https://wordpress.org/support/plugin/lw-mwp-tools" target="_blank">here</a>.
</div>
