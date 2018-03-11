function updateMonitor(){
  jQuery.post(
    ajaxurl, //ajaxurl reqs WP 2.8+
    {
      'action': 'lwmwptools_monitorajax'
    },
    function(response) {
      var myjson = JSON.parse(response); //turning that json into an array
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
    }
  );
}

if(document.getElementById('update_interval').value < 1){ //make sure the interval is not 0 or negative
  var update_interval = 5;
  document.getElementById('update_interval').value = "5";
}else{
  var update_interval = document.getElementById('update_interval').value;
}

setTimeout(updateMonitor, 0); //let other stuff finish loading before showing initial data
setInterval(updateMonitor, update_interval*1000); //then refresh data every update_interval seconds (default of 5 seconds will use about 500 KB bandwidth per hour)

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