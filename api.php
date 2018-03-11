<?php defined('ABSPATH') or die('No!');

//header('Cache-Control: no-cache'); //TODO: test this with varnish.

switch($_POST['action']){ //get the WP ajax action to call the appropriate function
  case "lwmwptools_monitorajax":
    resource_monitor();
    break;
  default:
    header("HTTP/1.0 404 Not Found"); //invalid action
    break;
}

function resource_monitor(){
  ##### LOAD AVERAGES #####
  $loadavg = sys_getloadavg();
  $load_1 = number_format($loadavg[0], 2);
  $load_5 = number_format($loadavg[1], 2);
  $load_15 = number_format($loadavg[2], 2);
  #########################

  ##### NUMBER OF CORES #####
  preg_match_all('/^processor/m', file_get_contents('/proc/cpuinfo'), $cores);
  $cores = count($cores[0]);
  ###########################

  ##### RAM/SWAP INFO #####
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
  $swap_used=$meminfo_swaptotal-$meminfo_swapfree; //how much swap is used is simpler to calculate.
  #########################

  ##### DISK SPACE #####
  $disk_total = round(((disk_total_space('/')/1024)/1024)/1024 ,1); //convert bytes to GB with 1 decimal place.
  $disk_free  = round(((disk_free_space ('/')/1024)/1024)/1024 ,1);
  $disk_used = round($disk_total-$disk_free, 1);
  ######################

  ##### CPU INFO (Coming soon!) #####
  /*$proc_stat = file('/proc/stat'); //read file into array, split by lines
  $proc_stat_cpu = preg_split('/\ +/', $proc_stat[0]); //read 1st line of file, and split into array by spaces
  $proc_stat_cpu['total'] = $proc_stat_cpu[1]+$proc_stat_cpu[2]+$proc_stat_cpu[3]+$proc_stat_cpu[4]+$proc_stat_cpu[5]+$proc_stat_cpu[6]+$proc_stat_cpu[7]; //100% of the cpu time

  for($i=0;$i<count($proc_stat)-6; $i++){ //for each line. -6 cause we will be adding 6 items to the array.
    $tmp = preg_split('/\ +/', $proc_stat[$i]); //split that line by spaces into array

    if($tmp[0] === "intr"){
      $proc_stat['intr']=$tmp;
    }else if($tmp[0] === "ctxt"){
      $proc_stat['ctxt']=$tmp[1]; //total number of context switches across all CPUs
    }else if($tmp[0] === "btime"){
      $proc_stat['btime'] = $tmp[1]; //time (in seconds since epoch) system has been booted. TODO: uptime
    }else if($tmp[0] === "processes"){
      $proc_stat['processes'] = $tmp[1]; //total number of processes and threads created.
    }else if($tmp[0] === "procs_running"){
      $proc_stat['procs_running'] = $tmp[1]; //number of processes currently running
    }else if($tmp[0] === "procs_blocked"){
      $proc_stat['procs_blocked'] =$tmp[1]; //number of processes blocked (waiting for I/O to complete)
    }
  }*/

  //$returned = array('proc_stat_cpu_user' => $proc_stat_cpu[1]/*normal processes executing in user mode*/, 'proc_stat_cpu_nice' => $proc_stat_cpu[2]/*niced processes executing in user mode*/, 'proc_stat_cpu_system' => $proc_stat_cpu[3]/*processes executing in kernel mode*/, 'proc_stat_cpu_idle' => $proc_stat_cpu[4]/*twiddling thumbs*/, 'proc_stat_cpu_iowait' => $proc_stat_cpu[5]/*waiting for I/O to complete*/, 'proc_stat_cpu_irq' => $proc_stat_cpu[6]/*servicing interrupts*/, 'proc_stat_cpu_softirq' => $proc_stat_cpu[7]/*servicing softirqs*/, 'proc_stat_cpu_total' => $proc_stat_cpu['total'], 'proc_stat_intr' => $proc_stat['intr'], 'proc_stat_ctxt' => $proc_stat['ctxt'], 'proc_stat_btime' => $proc_stat['btime'], 'proc_stat_processes' => $proc_stat['processes'], 'proc_stat_procs_running' => $proc_stat['procs_running'], 'proc_stat_procs_blocked' => $proc_stat['procs_blocked'] );
  ####################

  $monitor = array('ram_total' => $ram_total, 'ram_used' => $ram_used, 'ram_avail' => $ram_avail, /*'ram_free' => $meminfo_memfree, 'ram_buffers' => $meminfo_buffers, 'ram_cached' => $meminfo_cached,*/ 'swap_total' => $meminfo_swaptotal, 'swap_used' => $swap_used, 'swap_free' => $meminfo_swapfree, 'disk_total' => $disk_total, 'disk_used' => $disk_used, 'disk_free' => $disk_free, 'load_1' => $load_1, 'load_5' => $load_5, 'load_15' => $load_15, 'cores' => $cores );

  echo json_encode($monitor); //the output
}

?>
