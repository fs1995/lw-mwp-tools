<?php defined('ABSPATH') or die('No!'); ?>

<div class="wrap">
<h1>Server Resource Monitor</h1>

Load average: <span id="load_1"></span> <span id="load_5"></span> <span id="load_15"></span><br>
Cores: <span id="cores"></span><br><br>

<table border="1" style="text-align:left; border: 1px solid black; border-collapse: collapse;">
  <tr>
    <td colspan="2" style="padding: 5px;">
      <h3 style="margin-top: 1px;">Memory and Swap History</h3>
      <img src="<?php echo plugins_url('images/pct.png', __FILE__); ?>" height="75px" width="36px"><img src="<?php echo plugins_url('images/pixel.png', __FILE__); ?>"><canvas id="chart_ramswaphistory" width="500" height="75"></canvas><br>
      <img src="<?php echo plugins_url('images/pixel.png', __FILE__); ?>" width="8px"><img src="<?php echo plugins_url('images/sec.png', __FILE__); ?>"><br>
    </td>
  </tr>
  <tr>
    <td>
      <table>
        <tr>
          <td>
            <div class="ct-chart ct-square" id="chart_ram" style="height:100px;width:100px;"></div>
          </td>
          <td>
            RAM (<span id="ram_pct"></span> %)<br>
            Total: <span id="ram_total"></span> MB<br>
            Used: <span id="ram_used"></span> MB<br>
            Available: <span id="ram_avail"></span> MB<!--<br><br>
            Free: <span id="ram_free"></span> MB<br>
            Buffers: <span id="ram_buffers"></span> MB<br>
            Cached: <span id="ram_cached"></span> MB-->
          </td>
        </tr>
      </table>
    </td>
    <td>
      <table>
        <tr>
          <td>
            <div class="ct-chart ct-square" id="chart_swap" style="height:100px;width:100px;"></div>
          </td>
          <td>
            Swap (<span id="swap_pct"></span> %)<br>
            Total: <span id="swap_total"></span> MB<br>
            Used: <span id="swap_used"></span> MB<br>
            Free: <span id="swap_free"></span> MB
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table><br><br>

<table border="1" style="text-align:center">
  <tr>
    <td><div class="ct-chart ct-square" id="chart_disk" style="height:150px;width:150px;"></div></td>
  </tr>
  <tr>
    <td>Hard Disk (<span id="disk_pct"></span> %)</td>
  </tr>
  <tr>
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
