<?php
defined('ABSPATH') or die('No!');

$loadavg = sys_getloadavg();
preg_match_all('/^processor/m', file_get_contents('/proc/cpuinfo'), $cores);
$meminfo = preg_split('/\ +|[\n]/', file_get_contents("/proc/meminfo")); //regex to split spaces and newline

for($i=0; $i<count($meminfo); $i++){
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

$ram_avail = $meminfo_memfree+$meminfo_buffers+$meminfo_cached;
$ram_used=$ram_total-($meminfo_memfree+$meminfo_buffers+$meminfo_cached);
$ram_percent=($ram_used/$ram_total)*100;
$swap_used=$meminfo_swaptotal-$meminfo_swapfree;

function lw_mwp_tools_ram_info($ram_total, $ram_used, $ram_avail, $meminfo_free, $meminfo_buffers, $meminfo_cached){
  return "Total: " . $ram_total . " MB<br>Used: " . $ram_used . " MB<br>Available: " . $ram_avail . " MB<br><br>Free: " . $meminfo_free . "MB<br>Buffers: " . $meminfo_buffers . " MB<br>Cached: " . $meminfo_cached . " MB";
}

//check if theres no swap, because in that case swap chart should show as full (100), and not empty (0):
if($meminfo_swaptotal == "0"){
  $swap_percent='100';
}else{
  $swap_percent=($swap_used/$meminfo_swaptotal)*100;
}

function lw_mwp_tools_swap_info($swap_total, $swap_used, $swap_free){
  return "Total: " . $swap_total . " MB<br>Used: " . $swap_used . " MB<br>Free: " . $swap_free . " MB";
}

##### DISK #####
$disk_total = round(((disk_total_space('/')/1024)/1024)/1024 ,1);
$disk_free  = round(((disk_free_space ('/')/1024)/1024)/1024 ,1);
$disk_used = $disk_total-$disk_free;
$disk_percent = round(($disk_used/$disk_total)*100, 1);

function lw_mwp_tools_disk_info($disk_total, $disk_used, $disk_free, $disk_percent){
  return "Capacity: " . $disk_total . " GB<br>Used: " . $disk_used . " GB (" . $disk_percent . "%)<br>Free: " . $disk_free . " GB";
}

##### CHART #####
function lw_mwp_tools_chart($percent){
  // create image
  $image = imagecreatetruecolor(150, 150);

  // allocate colors
  $clear    = imagecolorallocatealpha($image, 0, 0, 0, 127);
  $red      = imagecolorallocate($image, 0xFF, 0x00, 0x00);
  $green    = imagecolorallocate($image, 0, 255, 0);

  imagefill($image, 0, 0, $clear);
  imagesavealpha($image, TRUE);

  imagefilledarc($image, 75, 75, 150, 150, 0, 360, $red, IMG_ARC_PIE); //red background
  if($percent < '100'){//for proper handling of full (100), otherwise chart would show as empty. and if its over 100, somethings broken.
    imagefilledarc($image, 75, 75, 150, 150, ($percent/100)*360/*convert percent to degrees*/, 360 , $green, IMG_ARC_PIE); //green free
  }

  ob_start ();
    imagepng($image);
    $image_data = ob_get_contents();
  ob_end_clean ();
  return base64_encode($image_data);
}

##### NOW MAKE THE PAGE #####
echo "<style>table, th, td {border: 1px solid black;text-align:center;}</style>";

if ($meminfo_swaptotal == '0')
  echo "<h2><pre><mark>Issue detected: Contact support to add swap file.</mark></pre></h2>";

echo "<h2>Server Resource Monitor</h2>This page does not automatically update, you will need to reload it.<br><br>
Load average: " . number_format($loadavg[0], 2) . " " . number_format($loadavg[1], 2) . " " . number_format($loadavg[2], 2) . "<br>
Cores: " . count($cores[0]) . "<br><br>
<table>
  <tr>
    <th><img src=\"data:image/png;base64," . lw_mwp_tools_chart($ram_percent) . "\"></th>
    <th><img src=\"data:image/png;base64," . lw_mwp_tools_chart($swap_percent) . "\"></th>
    <th><img src=\"data:image/png;base64," . lw_mwp_tools_chart($disk_percent) . "\"></th>
  </tr>
  <tr>
    <td>RAM</td>
    <td>Swap</td>
    <td>Hard Disk</td>
  </tr>
  <tr>
    <td>" . lw_mwp_tools_ram_info($ram_total, $ram_used, $ram_avail, $meminfo_memfree, $meminfo_buffers, $meminfo_cached) . "</td>
    <td>" . lw_mwp_tools_swap_info($meminfo_swaptotal, $swap_used, $meminfo_swapfree) . "</td>
    <td>" . lw_mwp_tools_disk_info($disk_total, $disk_used, $disk_free, $disk_percent) . "<br></td>
  </tr>
</table><br>

<h2>Bug report or suggestion?</h2>
Let us know <a href=\"https://wordpress.org/support/plugin/lw-mwp-tools\" target=\"_blank\">here</a>.

<br>";

?>
