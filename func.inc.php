<?
error_reporting('E_ALL');
ini_set('display_errors','On'); 

// Globals
$host_data = null;
$host_alive = false;
$privileged = false;
$data_totals = array('hosts'=>0,
                     'devs'=>0,
                     'activedevs'=>0,
                     'maxtemp'=>0, 
                     'desmhash'=>0,
                     'utility'=>0,
                     'fivesmhash'=>0,
                     'avemhash'=>0, 
                     'accepts'=>0, 
                     'rejects'=>0, 
                     'discards'=>0,
                     'stales'=>0, 
                     'getfails'=>0,
                     'remfails'=>0);

$API_version = 0;
$CGM_version = "0.0.0";
$pools_in_use = array();

/*****************************************************************************
/*  Function:    get_config_data()
/*  Description: Gets the config data
/*  Inputs:      none
/*  Outputs:     return - config object
*****************************************************************************/
function get_config_data()
{
  global $dbh;
  $config = null;

  $result = $dbh->query("SELECT * FROM configuration");
  if ($result)
    $config = $result->fetch(PDO::FETCH_OBJ);

  return $config;
}

/*****************************************************************************
/*  Function:    get_host_data()
/*  Description: Gets the host data given a host ID
/*  Inputs:      $host_id - the ID of the host
/*  Outputs:     return - data of host in array format
/*               'id', 'address', 'port', 'name', 'mhash_desired'
*****************************************************************************/
function get_host_data($host_id)
{
  global $dbh;
  $host_data = null;

  $result = $dbh->query("SELECT * FROM hosts WHERE id = $host_id");
  if ($result)
    $host_data = $result->fetch(PDO::FETCH_ASSOC);

  return $host_data;
}

/*****************************************************************************
/*  Function:    getsock()
/*  Description: Connects to a port on a remote system
/*  Inputs:      address - IP address to connect to
/*               port - Port to connect to
/*  Outputs:     return - socket
*****************************************************************************/
function getsock($addr, $port)
{
  $socket = null;
  $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
  if ($socket === false || $socket === null)
  {
    return null;
  }
  
  socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => '3', 'usec' => '3000'));
  $res = @socket_connect($socket, $addr, $port);
  if ($res === false)
  {
    socket_close($socket);
    return null;
  }
  return $socket;
}

/*****************************************************************************
/*  Function:    readsockline()
/*  Description: Reads data back from a socket
/*  Inputs:      socket - socket to read from
/*  Outputs:     return - data
*****************************************************************************/
function readsockline($socket)
{
  $line = '';
  while (true)
  {
    $byte = socket_read($socket, 1024);
    if ($byte == '')
       break;
    $line .= $byte;
  }

  return trim($line);
}

/*****************************************************************************
/*  Function:    send_request_to_host()
/*  Description: Sends / Receives data to from a specified host
/*  Inputs:      cmd_array - command, in array format, to send
/*               host_data - host data array from database
/*  Outputs:     return - data received from host in array format
*****************************************************************************/
function send_request_to_host($cmd_array, $host_data)
{
  $socket = getsock($host_data['address'], $host_data['port']);
  
  if ($socket != null)
  {
    $cmd = json_encode($cmd_array);

    socket_write($socket, $cmd, strlen($cmd));
    $line = readsockline($socket);
    socket_close($socket);

    if (strlen($line) == 0)
      return null;

    if (substr($line,0,1) == '{')
      $data = json_decode($line, true);
    else
      return null;
  }
  else
  {
    return null;
  }

  return $data;
}


/*****************************************************************************
/*  Function:    get_host_status()
/*  Description: returns the status of a specified host
/*  Inputs:      host_data - host data array from database
/*  Outputs:     return - true if host cgminer is talking, false if not
*****************************************************************************/
function get_host_status($host_data)
{
  global $API_version;
  global $CGM_version;

  $arr = array ('command'=>'version','parameter'=>'');
  $version_arr = send_request_to_host($arr, $host_data);

  if ($version_arr)
  {
    if ($version_arr['STATUS'][0]['STATUS'] == 'S')
    {
      $API_version = $version_arr['VERSION'][0]['API'];
      $CGM_version = $version_arr['VERSION'][0]['CGMiner'];
      
      if ($API_version >= 1.0)
        return true;
    }
  }
  return false;
}

/*****************************************************************************
/*  Function:    get_privileged_status()
/*  Description: returns the privilege status of a specified host
/*  Inputs:      host_data - host data array from database
/*  Outputs:     return - true if we can change values, false if not
*****************************************************************************/
function get_privileged_status($host_data)
{
  global $API_version;
  global $CGM_version;

  if ($API_version >= 1.2 )
  {
    $arr = array ('command'=>'privileged','parameter'=>'');
    $response = send_request_to_host($arr, $host_data);

    if ($response['STATUS'][0]['STATUS'] == 'S')
      return true;
  }
  else 
    return true;

  return false;
}

/*****************************************************************************
/*  Function:    set_color_high()
/*  Description: Sets the color (Red/yellow/green) according to high number
/*               is red, lowest is green
/*  Inputs:      value - value to be tested
/*               yellow_limit - location of yellow/green border
/*               red_limit - location of yellow/red boarder
/*  Ouputs:      return - color class
*****************************************************************************/
function set_color_high($value, $yellow_limit, $red_limit)
{
    settype ($value , "float");
    settype ($yellow_limit , "float");
    settype ($red_limit , "float");

	if ($value == -1)
	  return null;
	if ($value < $yellow_limit)
		return "class=green";
	if ($value < $red_limit)
		return "class=yellow";
	else
		return "class=red";
}

/*****************************************************************************
/*  Function:    set_color_low()
/*  Description: Sets the color (Red/yellow/green) according to high number
/*               is green, lowest is red
/*  Inputs:      value - value to be tested
/*               yellow_limit - location of yellow/green border
/*               red_limit - location of yellow/red boarder
/*  Ouputs:      return - color class
*****************************************************************************/
function set_color_low($value, $yellow_limit, $red_limit)
{
    settype ($value , "float");
    settype ($yellow_limit , "float");
    settype ($red_limit , "float");

	if ($value == -1)
	  return null;
	if ($value <= $red_limit)
		return "class=red";
	if ($value <= $yellow_limit)
		return "class=yellow";
	else
		return "class=green";
}

/*****************************************************************************
/*  Function:    set_share_colour()
/*  Description: processes the summary array of a host for html display
/*  Inputs:      shares_array - array containing the shares data.
/*                              This could be summary or pool arrays, as
/*                              they have the same element names
/*  Outputs:     return - an array with colours set
*****************************************************************************/
/*
function set_share_colour($shares_array)
{
  global $config;
  $share_types = array('Accepted', 'Rejected', 'Discarded', 'Stale', 'Get Failures', 'Remote Failures');
  $shares = array('absolute' => $share_types, 'percentage' => $share_types, 'color' => $share_types);

  $accepted =   $shares_array['Accepted'];
  $rejected =   $shares_array['Rejected'];
  $discarded =  $shares_array['Discarded'];
  $stale =      $shares_array['Stale'];
  $getfail =    $shares_array['Get Failures'];
  $remfail =    $shares_array['Remote Failures'];

  if (isset($accepted) && $accepted !== 0)
  {
    $rejects = round(100 / $accepted * $rejected, 2);
    $discards = round(100 / $accepted * $discarded,2);
    $stales = round(100 / $accepted * $stale, 2);
    $getfails = round(100 / $accepted * $getfail, 2);
    $remfails = round(100 / $accepted * $remfail, 2);
  }

  $rejectscol = set_color_high($rejects, $config->yellowrejects, $config->maxrejects);      // Rejects
  $discardscol = set_color_high($discards, $config->yellowdiscards, $config->maxdiscards);  // Discards
  $stalescol = set_color_high($stales, $config->yellowstales, $config->maxstales);          // Stales
  $getfailscol = set_color_high($getfails, $config->yellowgetfails, $config->maxgetfails);  // Get fails
  $remfailscol = set_color_high($remfails, $config->yellowremfails, $config->maxremfails);  // Rem fails

  return $shares;

}
*/

/*****************************************************************************
/*  Function:    create_host_header()
/*  Description: Creates the header bar for host information
/*  Inputs:      none
/*  Outputs:     return - host header in html
*****************************************************************************/
function create_host_header()
{
  $header =
    "<thead>
    	<tr>
        	<th scope='col' class='rounded-company'>Address</th>
            <th scope='col' class='rounded-q1'>Status</th>
            <th scope='col' class='rounded-q1'>GPUs</th>
            <th scope='col' class='rounded-q1'>Temp max</th>
            <th scope='col' class='rounded-q1'>MH/s des</th>
            <th scope='col' class='rounded-q1'>Util</th>
            <th scope='col' class='rounded-q1'>MH/s 5s</th>
            <th scope='col' class='rounded-q1'>MH/s avg</th>
            <th scope='col' class='rounded-q1'>Rejects</th>
            <th scope='col' class='rounded-q1'>Discards</th>
            <th scope='col' class='rounded-q1'>Stales</th>
            <th scope='col' class='rounded-q1'>Get Fails</th>
            <th scope='col' class='rounded-q1'>Rem Fails</th>
        </tr>
    </thead>";
    
    return $header;
}

/*****************************************************************************
/*  Function:    process_host_devs()
/*  Description: processes the array of devices from a host
/*               Retreives the number of devices, total 5s hash rate and max 
/*               temperature of the devices attached to the host
/*  Inputs:      dev_data_array - the array of devices
/*  Outputs:     return - number of devices
/*               activedevs - number of actively mining devices
/*               host5shash - total host 5s hash rate
/*               maxtemp - temperature of hotest device
*****************************************************************************/
function process_host_devs($dev_data_array, &$activedevs, &$host5shash, &$maxtemp)
{
  global $pools_in_use;

  $devs = 0;
  $activedevs = 0;
  $host5shash = 0;
  $maxtemp = 0;
  $pools_in_use = array();

  while(isset($dev_data_array['DEVS'][$devs]))
  {
    /* Get 5 second has rate */
    $dev5shash = $dev_data_array['DEVS'][$devs]['MHS 5s'];
    $host5shash += $dev5shash;

    /* Is device operating */
    if ($dev_data_array['DEVS'][$devs]['Status'] == "Alive" && $dev_data_array['DEVS'][$devs]['Enabled'] == "Y")
      $activedevs++;

    /* Find higest temp */
    $temp = $dev_data_array['DEVS'][$devs]['Temperature'];
    if ($maxtemp < $temp)
      $maxtemp = $temp;
    
    /* Find which pools are in use */
    $pools_in_use[$dev_data_array['DEVS'][$devs]['Last Share Pool']] = true;
    
    $devs++;
  }

  return $devs;
}


/*****************************************************************************
/*  Function:    process_host_info()
/*  Description: processes the host information such as uptime, version etc.
/*  Inputs:      host_data - the host data array
/*  Outputs:     return - the table of info
*****************************************************************************/
function process_host_info($host_data)
{
  global $API_version;
  global $CGM_version;

  $arr = array ('command'=>'config','parameter'=>'');
  $config_arr = send_request_to_host($arr, $host_data);
  
  $arr = array ('command'=>'summary','parameter'=>'');
  $summary_arr = send_request_to_host($arr, $host_data);
  
  $up_time = $summary_arr['SUMMARY']['0']['Elapsed'];
  $days = floor($up_time / 86400);
  $up_time -= $days * 86400;
  $hours = floor($up_time / 3600);
  $up_time -= $hours * 3600;
  $mins = floor($up_time / 60);
  $seconds = $up_time - ($mins * 60);
  
  $output = "
      <tr>
        <th>CGminer version</th>
        <th>API version</th>
        <th>Up time</th>
        <th>Found H/W</th>
        <th>Using ADL</th>
        <th>Pools and Strategy</th>
      </tr>
      <tr>
        <td>".$CGM_version."</td>
        <td>".$API_version."</td>
        <td>".$days."d ".$hours."h ".$mins."m ".$seconds."s</td>
        <td>".$config_arr['CONFIG']['0']['CPU Count']." CPUs, ".$config_arr['CONFIG']['0']['GPU Count']." GPUs, ".$config_arr['CONFIG']['0']['BFL Count']." BFLs</td>
        <td>".$config_arr['CONFIG']['0']['ADL in use']."</td>
        <td>".$config_arr['CONFIG']['0']['Pool Count']." pools, using ".$config_arr['CONFIG']['0']['Strategy']."</td>
      </tr>";

  return $output;
}

/*****************************************************************************
/*  Function:    process_host_disp()
/*  Description: processes the summary array of a host for html display
/*  Inputs:      desmhash - desires hash rate
/*               summary_data_array - the summary in array form
/*               dev_data_array - the devs list in array form
/*  Outputs:     return - the rows of devices in html
*****************************************************************************/
function process_host_disp($desmhash, $summary_data_array, $dev_data_array)
{
  global $data_totals;
  global $config;

  $devs = $activedevs = $max_temp = $fivesmhash = $fivesmhashper = $avgmhper = 0;
  $fivesmhashcol = $avgmhpercol = $rejectscol = $discardscol = $stalescol = $getfailscol = $remfailscol = "";
  $rejects = $discards = $stales = $getfails = $remfails = '---';
  $row = "";

  if ($summary_data_array != null)
  {
    if ($dev_data_array != null)
      $devs = process_host_devs($dev_data_array, $activedevs, $fivesmhash, $max_temp);

    $thisstatus = $summary_data_array['STATUS'][0]['STATUS'];
    $avgmhash =   $summary_data_array['SUMMARY'][0]['MHS av'];
    $accepted =   $summary_data_array['SUMMARY'][0]['Accepted'];
    $rejected =   $summary_data_array['SUMMARY'][0]['Rejected'];
    $discarded =  $summary_data_array['SUMMARY'][0]['Discarded'];
    $stale =      $summary_data_array['SUMMARY'][0]['Stale'];
    $getfail =    $summary_data_array['SUMMARY'][0]['Get Failures'];
    $remfail =    $summary_data_array['SUMMARY'][0]['Remote Failures'];
    $utility =    $summary_data_array['SUMMARY'][0]['Utility'];

    if (isset($accepted) && $accepted !== 0)
    {
      $rejects = round(100 / $accepted * $rejected, 1) . " %";
      $discards = round(100 / $accepted * $discarded,1) . " %";
      $stales = round(100 / $accepted * $stale, 1) . " %";
      $getfails = round(100 / $accepted * $getfail, 1) . " %";
      $remfails = round(100 / $accepted * $remfail, 1) . " %";
      
      $rejectscol = set_color_high($rejects, $config->yellowrejects, $config->maxrejects);     // Rejects
      $discardscol = set_color_high($discards, $config->yellowdiscards, $config->maxdiscards); // Discards
      $stalescol = set_color_high($stales, $config->yellowstales, $config->maxstales);         // Stales
      $getfailscol = set_color_high($getfails, $config->yellowgetfails, $config->maxgetfails); // Get fails
      $remfailscol = set_color_high($remfails, $config->yellowremfails, $config->maxremfails); // Rem fails
    }

    if ($desmhash > 0)
    {
      // Desired Mhash vs. 5s mhash
      $fivesmhashper = round(100 / $desmhash * $fivesmhash, 1);
      $fivesmhashcol = set_color_low($fivesmhashper, $config->yellowgessper, $config->maxgessper);

      // Desired Mhash vs. avg mhash
      $avgmhper = round(100 / $desmhash * $avgmhash, 1);
      $avgmhpercol = set_color_low($avgmhper, $config->yellowavgmhper, $config->maxavgmhper);
    }

    $tempcol = set_color_high($max_temp, $config->yellowtemp, $config->maxtemp);             // Temperature
    $thisstatuscol = ($thisstatus == "S") ? "class=green" : "class=yellow";                  // host status
    $thisdevcol = ($activedevs == $devs) ? "class=green" : "class=red";                      // active devs

	$row = "
      <td $thisstatuscol>$thisstatus</td>
      <td $thisdevcol>$activedevs/$devs</td>
      <td $tempcol>$max_temp</td>
      <td>$desmhash</td>
      <td>$utility</td>
      <td $fivesmhashcol>$fivesmhash<BR>$fivesmhashper %</td>
      <td $avgmhpercol>$avgmhash<BR>$avgmhper %</td>
      <td $rejectscol>$rejected<BR>$rejects</td>
      <td $discardscol>$discarded<BR>$discards</td>
      <td $stalescol>$stale<BR>$stales</td>
      <td $getfailscol>$getfail<BR>$getfails</td>
      <td $remfailscol>$remfail<BR>$remfails</td>";

    // Sum Stuff
    $data_totals['hosts']++;
    $data_totals['devs'] += $devs;
    $data_totals['activedevs'] += $activedevs;
    $data_totals['maxtemp'] = ($data_totals['maxtemp'] > $max_temp) ? $data_totals['maxtemp'] : $max_temp;
    $data_totals['desmhash'] += $desmhash;
    $data_totals['utility'] += $utility;
    $data_totals['fivesmhash'] += $fivesmhash;
    $data_totals['avemhash'] += $avgmhash;
    $data_totals['accepts'] += $accepted;
    $data_totals['rejects'] += $rejects;
    $data_totals['discards'] += $discards;
    $data_totals['stales'] += $stales;
    $data_totals['getfails'] += $getfails;
    $data_totals['remfails'] += $remfails;
  }

  return $row;
}

/*****************************************************************************
/*  Function:    get_host_summary()
/*  Description: gets the summary of a host
/*  Inputs:      host_data - the host data array.
/*  Outputs:     return - Host summary in html
*****************************************************************************/
function get_host_summary($host_data)
{
  $hostid = $host_data['id'];
  $name = $host_data['name'];
  $host = $host_data['address'];
  $hostport = $host_data['port'];
  $desmhash = $host_data['mhash_desired'];
  $host_row = "";

  $arr = array ('command'=>'summary','parameter'=>'');
  $summary_arr = send_request_to_host($arr, $host_data);

  if ($summary_arr != null)
  {
    $arr = array ('command'=>'devs','parameter'=>'');
    $dev_arr = send_request_to_host($arr, $host_data);

    $host_row = process_host_disp($desmhash, $summary_arr,  $dev_arr);
  }
  else
  {
    // No data from host
    $error = socket_strerror(socket_last_error());
    $msg = "Connection to $host:$hostport failed: ";
    $host_row = "<td colspan='12'>$msg '$error'</td>";
  }

  $host_row = "<tbody><tr>
    <td><table border=0><tr>
      <td><a href=\"edithost.php?id=$hostid\"><img src=\"images/edit.png\" border=0></a></td>
      <td><a href=\"edithost.php?id=$hostid\">$name</a></td></td>
    </tr></table></td>"
    . $host_row .
    "</tr></tbody>";

  return $host_row;
}

/*****************************************************************************
/*  Function:    create_devs_header()
/*  Description: Creates the header bar for devices information
/*  Inputs:      none
/*  Outputs:     return - device header in html
*****************************************************************************/
function create_devs_header()
{
$header =
    "<thead>
    	<tr>
        	<th scope='col' class='rounded-company'>GPU #</th>
            <th scope='col' class='rounded-q1'>En</th>
            <th scope='col' class='rounded-q1'>Status</th>
            <th scope='col' class='rounded-q1'>Temp</th>
            <th scope='col' class='rounded-q1'>Fan Speed</th>
            <th scope='col' class='rounded-q1'>GPU Clk</th>
            <th scope='col' class='rounded-q1'>Mem Clk</th>
            <th scope='col' class='rounded-q1'>Volt</th>
            <th scope='col' class='rounded-q1'>Active</th>
            <th scope='col' class='rounded-q1'>MH/s 5s</th>
            <th scope='col' class='rounded-q1'>MH/s avg</th>
            <th scope='col' class='rounded-q1'>Acc</th>
            <th scope='col' class='rounded-q1'>Rej</th>
            <th scope='col' class='rounded-q1'>H/W Err</th>
            <th scope='col' class='rounded-q1'>Util</th>
            <th scope='col' class='rounded-q1'>Intens</th>
        </tr>
    </thead>";
    
    return $header;
}

/*****************************************************************************
/*  Function:    process_dev_disp()
/*  Description: Processes a single device data for html display
/*  Inputs:      gpu_data_array - the device array data
/*               edit - flag to show start/stop buttons
/*  Outputs:     return - html formatted table row
*****************************************************************************/
function process_dev_disp($gpu_data_array, $edit=false)
{
  global $config;
  global $id;
  global $privileged;

  /* show buttons if selected */
  $button = $gpu_data_array['Enabled'];
  if($edit && $privileged)
  {
    if(($gpu_data_array['Enabled'] == "Y"))
    {
      $stop_disable = "";
      $start_disable = "disabled='disabled'";
    }
    else
    {
      $stop_disable = "disabled='disabled'";
      $start_disable = "";
    }
    $button =
      "<input type='submit' value='Start' name='start' ".$start_disable."><br>
       <input type='submit' value='Stop' name='stop' ".$stop_disable."><br>
       <input type='submit' value='Restart' name='restart' ".$stop_disable.">";
  }

  /* set colors */
  $encol = ($gpu_data_array['Enabled'] == "Y") ? "class=green" : "class=red";                      // Enabled
  $alcol = ($gpu_data_array['Status'] == "Alive") ? "class=green" : "class=red";                   // Alive
  $tmpcol = set_color_high($gpu_data_array['Temperature'], $config->yellowtemp, $config->maxtemp); // Temperature
  $fancol = set_color_high($gpu_data_array['Fan Percent'], $config->yellowfan, $config->maxfan);   // Fans
  
  /* format fan speeds */
  $fanspeed = ($gpu_data_array['Fan Speed'] == '-1') ? '---' : $gpu_data_array['Fan Speed']; 
  $fanpercent = ($gpu_data_array['Fan Percent'] == '-1') ? '---' : $gpu_data_array['Fan Percent']. " %"; 

  /* format GPU number */
  if ($privileged)
  {
    $GPU_cell =
    "<table border=0><tr>
      <td><a href='editdev.php?id=".$id."&dev=".$gpu_data_array['GPU']."'><img src=\"images/edit.png\" border=0></a></td>
      <td><a href='editdev.php?id=".$id."&dev=".$gpu_data_array['GPU']."'>".$gpu_data_array['GPU']."</a></td></td>
    </tr></table>";
  }
  else
  {
    $GPU_cell = $gpu_data_array['GPU'];
  }

  /* form row */
  $row = " <tr>
  <td>".$GPU_cell."</td>
  <td $encol>".$button."</td>
  <td $alcol>".$gpu_data_array['Status']."</td>
  <td $tmpcol>".$gpu_data_array['Temperature']."</td>
  <td $fancol>".$fanspeed."<BR>".$fanpercent."</td>
  <td>".$gpu_data_array['GPU Clock']."</td>
  <td>".$gpu_data_array['Memory Clock']."</td>
  <td>".$gpu_data_array['GPU Voltage']."</td>
  <td>".$gpu_data_array['GPU Activity']." %</td>
  <td>".$gpu_data_array['MHS 5s']."</td>
  <td>".$gpu_data_array['MHS av']."</td>
  <td>".$gpu_data_array['Accepted']."</td>
  <td>".$gpu_data_array['Rejected']."</td>
  <td>".$gpu_data_array['Hardware Errors']."</td>
  <td>".$gpu_data_array['Utility']."</td>
  <td>".$gpu_data_array['Intensity']."</td>
  </tr>";

  return $row;
}

/*****************************************************************************
/*  Function:    process_devs_disp()
/*  Description: processes the devs of a host for html display
/*  Inputs:      host_data - the host data array.
/*  Outputs:     return - Devs table in html
*****************************************************************************/
function process_devs_disp($host_data)
{
  global $id;

  $i = 0;
  $table = "";

  $arr = array ('command'=>'devs','parameter'=>'');
  $devs_arr = send_request_to_host($arr, $host_data);

  if ($devs_arr != null)
  {
    $id = $host_data['id'];
    while (isset($devs_arr['DEVS'][$i]))
    {
      $table .= process_dev_disp($devs_arr['DEVS'][$i]);
      $i++;
    }
  }

  return $table;
}

/*****************************************************************************
/*  Function:    get_dev_data()
/*  Description: retrives a single dev from a host
/*  Inputs:      host_data - the host data array.
/*               devid - the the device ID.
/*  Outputs:     return - the device data array
*****************************************************************************/
function get_dev_data($host_data, $devid)
{

  $arr = array ('command'=>'gpu','parameter'=>$devid);
  $dev_arr = send_request_to_host($arr, $host_data);
  
  return $dev_arr['GPU']['0'];
}

/*****************************************************************************
/*  Function:    create_pool_header()
/*  Description: Creates the header bar for pool information
/*  Inputs:      none
/*  Outputs:     return - pool header in html
*****************************************************************************/
function create_pool_header()
{
  $header =
    "<thead>
    <tr>
      <th scope='col' class='rounded-company'>Pool</th>
      <th scope='col' class='rounded-q1'>Priority</th>
      <th scope='col' class='rounded-q1' colspan='2'>URL</th>
      <th scope='col' class='rounded-q1'>Gets</th>
      <th scope='col' class='rounded-q1'>Accepts</th>
      <th scope='col' class='rounded-q1'>Rejects</th>
      <th scope='col' class='rounded-q1'>Discards</th>
      <th scope='col' class='rounded-q1'>Stales</th>
      <th scope='col' class='rounded-q1'>Get Fails</th>
      <th scope='col' class='rounded-q1'>Rem fails</th>
    </tr>
    </thead>";

  return $header;
}

/*****************************************************************************
/*  Function:    process_pool_disp()
/*  Description: processes a single item of the pool array of a host for html display
/*  Inputs:      pool_data_array - the pool array data.
/*               edit - flag to show priority buttons
/*  Outputs:     return - the row the pool in html
*****************************************************************************/
function process_pool_disp($pool_data_array, $edit=false)
{
  global $config;
  global $pools_in_use;

  $fivesmhashcol = $avgmhpercol = $rejectscol = $discardscol = $stalescol = $getfailscol = $remfailscol = "";
  $rejects = $discards = $stales = $getfails = $remfails = '---';

  $accepted =   $pool_data_array['Accepted'];
  $rejected =   $pool_data_array['Rejected'];
  $discarded =  $pool_data_array['Discarded'];
  $stale =      $pool_data_array['Stale'];
  $getfail =    $pool_data_array['Get Failures'];
  $remfail =    $pool_data_array['Remote Failures'];

  /* set shares colours */
  if (isset($accepted) && $accepted !== 0)
  {
    $rejects = round(100 / $accepted * $rejected, 1) . " %";
    $discards = round(100 / $accepted * $discarded,1) . " %";
    $stales = round(100 / $accepted * $stale, 1) . " %";
    $getfails = round(100 / $accepted * $getfail, 1) . " %";
    $remfails = round(100 / $accepted * $remfail, 1) . " %";
    
    $rejectscol = set_color_high($rejects, $config->yellowrejects, $config->maxrejects);      // Rejects
    $discardscol = set_color_high($discards, $config->yellowdiscards, $config->maxdiscards);  // Discards
    $stalescol = set_color_high($stales, $config->yellowstales, $config->maxstales);          // Stales
    $getfailscol = set_color_high($getfails, $config->yellowgetfails, $config->maxgetfails);  // Get fails
    $remfailscol = set_color_high($remfails, $config->yellowremfails, $config->maxremfails);  // Rem fails
  }
  
  /*Set in-use colour */
  $poolcol = "";
  if ($pools_in_use[$pool_data_array['POOL']] == true)
    $poolcol = "class=green";

  /* set pool colour */
  if ($pool_data_array['Status'] == "Alive")
    $alcol = "class=green";
  else if ($pool_data_array['Status'] == "Disabled")
    $alcol = "class=yellow";
  else
    $alcol = "class=red";

  /* format buttons */
  $top_button = "";
  $start_stop_button = "";
  if($edit)
  {
    $disable_button = ($pool_data_array['Priority'] == '0') ? " disabled='disabled'" : "";
    $top_button = " <button type='submit' name='top' value='".$pool_data_array['POOL']. "' " . $disable_button.">Top</button>";
    
    if($pool_data_array['Status'] == "Alive")
      $start_stop_button = " <button type='submit' name='stop' value='".$pool_data_array['POOL']."'>Stop</button>";
    else if ($pool_data_array['Status'] == "Disabled")
      $start_stop_button = " <button type='submit' name='start' value='".$pool_data_array['POOL']."'>Start</button>";
    else
      $start_stop_button = " <button disabled='disabled'>Start</button>";
  }
  
  $row = "<tr>
  <td $poolcol>".$pool_data_array['POOL']."</td>
  <td>".$pool_data_array['Priority'].$top_button."</td>
  <td $alcol>".$pool_data_array['URL']."</td>
  <td $alcol>".$start_stop_button ."</td>
  <td>".$pool_data_array['Getworks']."</td>
  <td>".$pool_data_array['Accepted']."</td>
  <td $rejectscol>".$pool_data_array['Rejected']."<BR>".$rejects."</td>
  <td $discardscol>".$pool_data_array['Discarded']."<BR>".$discards."</td>
  <td $stalescol>".$pool_data_array['Stale']."<BR>".$stales."</td>
  <td $getfailscol>".$pool_data_array['Get Failures']."<BR>".$getfails."</td>
  <td $remfailscol>".$pool_data_array['Remote Failures']."<BR>".$remfails."</td>
  </tr>";

  return $row;
}

/*****************************************************************************
/*  Function:    process_pools_disp()
/*  Description: processes the pools of a host for html display
/*  Inputs:      host_data - the host data array.
/*  Outputs:     return - Pool table in html
*****************************************************************************/
function process_pools_disp($host_data, $edit=false)
{
  $i = 0;
  $table = "";


  $arr = array ('command'=>'pools','parameter'=>'');
  $pool_arr = send_request_to_host($arr, $host_data);

  if ($pool_arr != null)
  {
    while (isset($pool_arr['POOLS'][$i]))
    {
      $table .= process_pool_disp($pool_arr['POOLS'][$i], $edit);
      $i++;
    }
  }
  return $table;
}

/*****************************************************************************
/*  Function:    create_totals()
/*  Description: forms the totals row
/*  Inputs:      none
/*  Outputs:     return - the html formatted totals
*****************************************************************************/
function create_totals()
{
    global $data_totals;

    $sumrejects = round($data_totals['rejects'] / $data_totals['hosts'],1);
    $sumdiscards = round($data_totals['discards'] / $data_totals['hosts'],1);
    $sumstales = round($data_totals['stales'] / $data_totals['hosts'],1);
    $sumgetfails = round($data_totals['getfails'] / $data_totals['hosts'],1);
    $sumremfails = round($data_totals['remfails'] / $data_totals['hosts'],1);

    $totals =
    "<thead>
    	<tr>
        	<th scope='col' class='rounded-company'>".$data_totals['hosts']." Hosts</th>
            <th scope='col' class='rounded-q1'></th>
            <th scope='col' class='rounded-q1'>".$data_totals['devs']."/".$data_totals['activedevs']."</th>
            <th scope='col' class='rounded-q1'>".$data_totals['maxtemp']."</th>
            <th scope='col' class='rounded-q1'>".$data_totals['desmhash']."</th>
            <th scope='col' class='rounded-q1'>".$data_totals['utility']."</th>
            <th scope='col' class='rounded-q1'>".$data_totals['fivesmhash']."</th>
            <th scope='col' class='rounded-q1'>".$data_totals['avemhash']."</th>
            <th scope='col' class='rounded-q1'>".$sumrejects." %</th>
            <th scope='col' class='rounded-q1'>".$sumdiscards." %</th>
            <th scope='col' class='rounded-q1'>".$sumstales." %</th>
            <th scope='col' class='rounded-q1'>".$sumgetfails." %</th>
            <th scope='col' class='rounded-q1'>".$sumremfails." %</th>
        </tr>
    </thead>";
    return $totals;
}

?>
