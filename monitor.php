<?php defined('ABSPATH') or die('No!');
$jsonpath = plugins_url( 'api.php', __FILE__ ) . "?lw-mwp-tools=" . gethostname() . get_current_user() . "&page=monitor";?>

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
  Update interval (seconds): <input type="text" name="lwmwptools_update_interval" id="update_interval" value="<?php echo esc_attr(get_option('lwmwptools_update_interval', "2") ); ?>" maxlength="4" size="4" />
  <?php submit_button("Set", '', '', false); ?>
</form>

<br><h2>Bug report or suggestion?</h2>
Let us know <a href="https://wordpress.org/support/plugin/lw-mwp-tools" target="_blank">here</a>.
</div>


<link rel="stylesheet" href="//cdn.jsdelivr.net/chartist.js/latest/chartist.min.css"> <!-- for the pie charts -->
<script src="//cdn.jsdelivr.net/chartist.js/latest/chartist.min.js"></script>
<style>/*set the pie chart colors*/
.ct-series-a .ct-slice-pie {
  fill: red;
  stroke: white;
}
.ct-series-b .ct-slice-pie {
  fill: green;
  stroke: white;
}
</style>

<script type="text/javascript">

function updateChart(){
  var xhr = new XMLHttpRequest(); //ie7+
  xhr.open("GET", <?php echo "\"" . $jsonpath . "\""; ?>, true); //little bit of mixing php here to get the path of monitor_json.php to get the json with all the system resource info
  xhr.onload = function (e) {
    if (xhr.readyState === 4){
      if(xhr.status === 200){ //response is ready
        var myjson = JSON.parse(xhr.responseText); //turning that json into an array
        document.getElementById("ram_total").innerHTML = myjson['ram_total']; //and updating the page
        document.getElementById("ram_used").innerHTML = myjson['ram_used'];
        document.getElementById("ram_avail").innerHTML = myjson['ram_avail'];
        //document.getElementById("ram_free").innerHTML = myjson['ram_free'];
        //document.getElementById("ram_buffers").innerHTML = myjson['ram_buffers'];
        //document.getElementById("ram_cached").innerHTML = myjson['ram_cached'];
        document.getElementById("ram_pct").innerHTML = ((myjson['ram_used'] / myjson['ram_total']) * 100).toFixed(1);

        document.getElementById("swap_total").innerHTML = myjson['swap_total'];
        document.getElementById("swap_used").innerHTML = myjson['swap_used'];
        document.getElementById("swap_free").innerHTML = myjson['swap_free'];
        document.getElementById("swap_pct").innerHTML = ((myjson['swap_used'] / myjson['swap_total']) * 100).toFixed(1);

        document.getElementById("disk_total").innerHTML = myjson['disk_total'];
        document.getElementById("disk_used").innerHTML = myjson['disk_used'];
        document.getElementById("disk_free").innerHTML = myjson['disk_free'];
        document.getElementById("disk_pct").innerHTML = ((myjson['disk_used'] / myjson['disk_total']) *100).toFixed(1);

        document.getElementById("load_1").innerHTML = myjson['load_1'];
        document.getElementById("load_5").innerHTML = myjson['load_5'];
        document.getElementById("load_15").innerHTML = myjson['load_15'];
        document.getElementById("cores").innerHTML = myjson['cores'];

        chart_ram.update({ series: [myjson['ram_used'], myjson['ram_avail']], labels: [" ", " "] }); //and updating the charts
        chart_swap.update({ series: [myjson['swap_used'], myjson['swap_free']], labels: [" ", " "] });
        chart_disk.update({ series: [myjson['disk_used'], myjson['disk_free']], labels: [" ", " "] });
      }else{
        console.error(xhr.statusText);
      }
    }
  };
  xhr.onerror = function(e){
    console.error(xhr.statusText);
  };
  xhr.timeout = 600; //600ms should work on most connections
  xhr.send(null);
};

if(document.getElementById('update_interval').value < 1){ //make sure the interval is not 0 or negative
  var update_interval = 2;
  document.getElementById('update_interval').value = "2";
}else{
  var update_interval = document.getElementById('update_interval').value;
}

setTimeout(updateChart, 0); //let other stuff finish loading before showing initial data
setInterval(updateChart, update_interval*1000); //then refresh data every update_interval seconds (default of 2 seconds will use about 2MB bandwidth per hour)

chart_ram = new Chartist.Pie('#chart_ram', { //create the ram chart
  series: [0],
}, {
  width:150,
  height: 150
});

chart_swap = new Chartist.Pie('#chart_swap', {
  series: [0],
}, {
  width:150,
  height:150
});

chart_disk = new Chartist.Pie('#chart_disk', {
  series: [0],
}, {
  width:150,
  height:150
});

</script>
