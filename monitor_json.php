<?php

if (!isset( $_GET['lw-mwp-tools'] )) { //to protect access to this file
    header('HTTP/1.0 401 Unauthorized');
    exit;
}else if( $_GET['lw-mwp-tools'] !== gethostname() . get_current_user() ){ //so the GET parameter is set, now to check what it's set to... not super secure, but this isint terribly sensitive info we are protecting...
  header('HTTP/1.0 401 Unauthorized');
  exit;
}

header('Cache-Control: no-cache'); //TODO: test this with varnish.

$loadavg = sys_getloadavg(); //create array of the load averages
$load_1 = number_format($loadavg[0], 2);
$load_5 = number_format($loadavg[1], 2);
$load_15 = number_format($loadavg[2], 2);

preg_match_all('/^processor/m', file_get_contents('/proc/cpuinfo'), $cores); //to get the number of cpu cores
$cores = count($cores[0]);

$meminfo = preg_split('/\ +|[\n]/', file_get_contents("/proc/meminfo")); //get ram and swap info, some regex to split spaces and newline to store in an array

for($i=0; $i<count($meminfo); $i++){ //get ram and swap info from the above array, and convert it from kb to mb, with no decimal places:
  if($meminfo[$i] === "MemTotal:")
    $ram_total=round(($meminfo[$i+1])/1024, 0);
  if($meminfo[$i] === "MemFree:")
    $meminfo_memfree=round(($meminfo[$i+1])/1024, 0);
  if($meminfo[$i] === "Buffers:")
    $meminfo_buffers=round(($meminfo[$i+1])/1024, 0);
  if($meminfo[$i] === "Cached:")
    $meminfo_cached=round(($meminfo[$i+1])/1024, 0);
  if($meminfo[$i] === "SwapTotal:")
    $meminfo_swaptotal=round(($meminfo[$i+1])/1024, 0);
  if($meminfo[$i] === "SwapFree:")
    $meminfo_swapfree=round(($meminfo[$i+1])/1024, 0);
}

$ram_avail = $meminfo_memfree+$meminfo_buffers+$meminfo_cached; //seems the older format of the meminfo file on ubuntu 14 does not have a "MemAvailable:" value, so will add free + buffers + cached
$ram_used=$ram_total-($meminfo_memfree+$meminfo_buffers+$meminfo_cached); //so how much ram is actually used would be the total minus free, buffers, and cached.
$ram_pct=round(($ram_used/$ram_total)*100, 1); //used ram as a percent, this will also be used for the chart
$swap_used=$meminfo_swaptotal-$meminfo_swapfree; //how much swap is used is simpler to caclulate.

//check if theres no swap, because in that case swap chart should show as full (100), and not empty (0):
if($meminfo_swaptotal == "0"){
  $swap_pct='100';
}else{
  $swap_pct=round(($swap_used/$meminfo_swaptotal)*100, 1);
}

##### DISK #####
$disk_total = round(((disk_total_space('/')/1024)/1024)/1024 ,1); //convert bytes to GB with 1 decimal place.
$disk_free  = round(((disk_free_space ('/')/1024)/1024)/1024 ,1);
$disk_used = round($disk_total-$disk_free, 1);
$disk_pct = round(($disk_used/$disk_total)*100, 1); //disk used as a percent, this will also be used for the chart.

$monitor = array('ram_total' => $ram_total, 'ram_used' => $ram_used, 'ram_avail' => $ram_avail, 'ram_free' => $meminfo_memfree, 'ram_buffers' => $meminfo_buffers, 'ram_cached' => $meminfo_cached, 'ram_pct' => $ram_pct, 'swap_total' => $meminfo_swaptotal, 'swap_used' => $swap_used, 'swap_free' => $meminfo_swapfree, 'swap_pct' => $swap_pct, 'disk_total' => $disk_total, 'disk_used' => $disk_used, 'disk_free' => $disk_free, 'disk_pct' => $disk_pct, 'load_1' => $load_1, 'load_5' => $load_5, 'load_15' => $load_15, 'cores' => $cores, 'hostname' => gethostname(), 'phpversion' => phpversion() );

echo json_encode($monitor); //the output, to be processed by monitor.php

?>
