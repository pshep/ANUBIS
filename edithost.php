<?php
require("config.inc.php");
require("func.inc.php");

$dbh = anubis_db_connect();
$config = get_config_data();

if (isset($_POST['delete']) && isset($_POST['savehostid']))
{
	$id = 0 + $_POST['savehostid'];
	$id_quote = $dbh->quote($id);
	$delq = "DELETE FROM hosts WHERE id = $id_quote";
	$delr = $dbh->exec($delq);
    db_error();
}

if (isset($_POST['savehostid']) && !isset($_POST['delete'])) 
{
  $id = 0 + $_POST['savehostid'];
  $id_quote = $dbh->quote($id);
  $newname = $dbh->quote($_POST['macname']);
  $address = $dbh->quote($_POST['ipaddress']);
  $port = $dbh->quote($_POST['port']);
  $mhash = $dbh->quote($_POST['mhash']);

  if ($newname && $newname !== "" && $address && $address !== "")
  {
    $updq = "UPDATE hosts SET name = $newname, address = $address, port = $port, mhash_desired = $mhash WHERE id = $id_quote";
    $dbh->exec($updq);
    db_error();
  }
}

if (!isset($id))
  $id = 0 + $_GET['id'];
if (!$id || $id == 0) 
{
	echo "Need a Host to deal with !";
	die;
}


if($host_data = get_host_data($id))
{
  if($host_alive = get_host_status($host_data))
  {
    /* Determine if we can change values on this host */
    $privileged = get_privileged_status($host_data);
  
    if ($privileged)
    {
      if (isset($_POST['startasc']))
      {
        $asc_id = filter_input(INPUT_POST, 'startasc', FILTER_SANITIZE_NUMBER_INT);
        $arr = array ('command'=>'ascenable','parameter'=>$asc_id);
        $dev_response = send_request_to_host($arr, $host_data);
        sleep(2);
      }

      if (isset($_POST['stopasc']))
      {
        $asc_id = filter_input(INPUT_POST, 'stopasc', FILTER_SANITIZE_NUMBER_INT);
        $arr = array ('command'=>'ascdisable','parameter'=>$asc_id);
        $dev_response = send_request_to_host($arr, $host_data);
        sleep(2);
      }
      
      if (isset($_POST['flashasc']))
      {
      	$asc_id = filter_input(INPUT_POST, 'flashasc', FILTER_SANITIZE_NUMBER_INT);
      	$arr = array ('command'=>'ascidentify','parameter'=>$asc_id);
      	$dev_response = send_request_to_host($arr, $host_data);
      	sleep(2);
      }

      if (isset($_POST['startpga']))
      {
        $pga_id = filter_input(INPUT_POST, 'startpga', FILTER_SANITIZE_NUMBER_INT);
        $arr = array ('command'=>'pgaenable','parameter'=>$pga_id);
        $dev_response = send_request_to_host($arr, $host_data);
        sleep(2);
      }

      if (isset($_POST['stoppga']))
      {
        $pga_id = filter_input(INPUT_POST, 'stoppga', FILTER_SANITIZE_NUMBER_INT);
        $arr = array ('command'=>'pgadisable','parameter'=>$pga_id);
        $dev_response = send_request_to_host($arr, $host_data);
        sleep(2);
      }
      
      if (isset($_POST['flashpga']))
      {
      	$pga_id = filter_input(INPUT_POST, 'flashpga', FILTER_SANITIZE_NUMBER_INT);
      	$arr = array ('command'=>'pgaidentify','parameter'=>$pga_id);
      	$dev_response = send_request_to_host($arr, $host_data);
      	sleep(2);
      }
      
      if (isset($_POST['toppool']))
      {
        $pool_id = filter_input(INPUT_POST, 'toppool', FILTER_SANITIZE_NUMBER_INT);
        $arr = array ('command'=>'switchpool','parameter'=>$pool_id);
        $pool_response = send_request_to_host($arr, $host_data);
        sleep(2);
      }

      if (isset($_POST['stoppool']))
      {
        $pool_id = filter_input(INPUT_POST, 'stoppool', FILTER_SANITIZE_NUMBER_INT);
        $arr = array ('command'=>'disablepool','parameter'=>$pool_id);
        $pool_response = send_request_to_host($arr, $host_data);
        sleep(2);
      }
  
      if (isset($_POST['startpool']))
      {
        $pool_id = filter_input(INPUT_POST, 'startpool', FILTER_SANITIZE_NUMBER_INT);
        $arr = array ('command'=>'enablepool','parameter'=>$pool_id);
        $pool_response = send_request_to_host($arr, $host_data);
        sleep(2);
      }

      if (isset($_POST['rempool']))
      {
        $pool_id = filter_input(INPUT_POST, 'rempool', FILTER_SANITIZE_NUMBER_INT);
        $arr = array ('command'=>'removepool','parameter'=>$pool_id);
        $pool_response = send_request_to_host($arr, $host_data);
        sleep(2);
      }

      if (isset($_POST['addpool']))
      {
        $pool_url = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);
        $pool_user = filter_input(INPUT_POST, 'user', FILTER_SANITIZE_STRING);
        $pool_pass = filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_STRING);

        $arr = array ('command'=>'addpool','parameter'=>$pool_url.','.$pool_user.','.$pool_pass);
        $pool_response = send_request_to_host($arr, $host_data);
        sleep(2);
      }
      
      if (isset($_POST['saveconf']))
      {
        $conf_path = filter_input(INPUT_POST, 'confpath', FILTER_SANITIZE_STRING);
        
        // add configuration file path to db table. It'll just fail if it's already there.
        $alter = "ALTER TABLE `hosts` ADD `conf_file_path` varchar(255) NULL";
        $dbh->exec($alter);

        $updq = "UPDATE hosts SET conf_file_path = '$conf_path' WHERE id = $id";
        $dbh->exec($updq);
        db_error();

        $arr = array ('command'=>'save','parameter'=>$conf_path);
        $pool_response = send_request_to_host($arr, $host_data);
        sleep(2);
        
        // as host data is updated, re-load it.
        $host_data = get_host_data($id);
      }
      
      if (isset($_POST['restartbut']) && isset($_POST['restartchk']))
      {
        $arr = array ('command'=>'restart','parameter'=>'');
        send_request_to_host($arr, $host_data);
        $host_alive = FALSE;
        sleep(2);
      }
      
      if (isset($_POST['quitbut']) && isset($_POST['quitchk']))
      {
        $arr = array ('command'=>'quit','parameter'=>'');
        send_request_to_host($arr, $host_data);
        $host_alive = FALSE;
        sleep(2);
      }
    }
  }
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Anubis - a cgminer web frontend</title>

<link href="templatemo_style.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="css/ddsmoothmenu.css" />

<script type="text/javascript" src="scripts/jquery.min.js"></script>
<script type="text/javascript" src="scripts/ddsmoothmenu.js">


/***********************************************
* Smooth Navigational Menu- (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
***********************************************/

</script>


<script type="text/javascript">

ddsmoothmenu.init({
	mainmenuid: "templatemo_menu", //menu DIV id
	orientation: 'h', //Horizontal or vertical menu: Set to "h" or "v"
	classname: 'ddsmoothmenu', //class added to menu's outer DIV
	//customtheme: ["#1c5a80", "#18374a"],
	contentsource: "markup" //"markup" or ["container_id", "path_to_menu_file"]
})

</script>


</head>
<body>

<div id="templatemo_wrapper">

<?php include ('header.inc.php'); ?>

    <div id="templatemo_main">
    	<div class="col_fw">
        	<div class="templatemo_megacontent">
            	<h2>Host detail</h2>
<?php
				 if ($host_alive)
                   echo "<a href='hoststat.php?id=".$id."'>View host stats</a>";
?>
                <div class="cleaner h20"></div>
<?php
if ($host_data)
{  
  echo "<table id='rounded-corner' summary='HostSummary' align='center'>";
  echo create_host_header();
  echo get_host_summary($host_data);
  echo "</table>";

  if ($host_alive)
  {
    echo "<form name=pool action='edithost.php?id=".$id."' method='post'>";
    echo "<table id='rounded-corner' summary='DevsSummary' align='center'>";
    echo create_devs_header();
    echo process_devs_disp($host_data, FALSE);

    if (isset($dev_response))
    {
      if ($dev_response['STATUS'][0]['STATUS'] == 'S')
        $dev_message = "Action successful: ";
      else if ($dev_response['STATUS'][0]['STATUS'] == 'I')
         $dev_message = "Action info: ";
      else if ($dev_response['STATUS'][0]['STATUS'] == 'W')
         $dev_message = "Action warning: ";
      else
         $dev_message = "Action error: ";

      echo "<thead><tr>
              <th colspan='16'  scope='col' class='rounded-company'>"
                . $dev_message . $dev_response['STATUS'][0]['Msg'].
             "</th>
            </tr></thead>";
    }
    echo "</table>";

    echo "<table id='rounded-corner' summary='PoolSummary' align='center'>";
    echo create_pool_header();
    echo process_pools_disp($host_data, $privileged);
        
    if ((version_compare($API_version, 1.11, '>=')) && $privileged)
    {
?>
      <thead>
      	<tr>
      	  <th colspan="13">
            Pool URL: <input type="text" name="url">&nbsp;
            Username: <input type="text" name="user">&nbsp;
            Password: <input type="text" name="pass">&nbsp;&nbsp;
            <input type="submit" value="Add Pool" name="addpool">
            </th>
          </tr>
          <tr>
            <th colspan="13">
            Configuration file path (blank for default):
            <input type="text" name="confpath" value="<?php echo $host_data['conf_file_path']; ?>">
            <input type="submit" value="Save Configuration" name="saveconf">
          </th>
        </tr>
<?php
      if (isset($pool_response))
      {
        if ($pool_response['STATUS'][0]['STATUS'] == 'S')
          $pool_message = "Action successful: ";
        else if ($pool_response['STATUS'][0]['STATUS'] == 'I')
           $pool_message = "Action info: ";
        else if ($pool_response['STATUS'][0]['STATUS'] == 'W')
           $pool_message = "Action warning: ";
        else
           $pool_message = "Action error: ";

        echo "<tr>
                <th colspan='13'  scope='col' class='rounded-company'>"
                  . $pool_message . $pool_response['STATUS'][0]['Msg'].
               "</th>
              </tr>";
      }
      echo "</thead>";
    }
    echo "</table>";
    
    if ((version_compare($API_version, 1.7, '>=')) && $privileged)
    {
?>
      <table id='rounded-corner' summary='cgminerreset' align='center'>
        <tr>
      	  <th colspan="2"  scope="col" class="rounded-company">
      	    To restart or quit CGminer, click the checkbox, then press the button.
      	  </th>
      	</tr>
      	<tr>
      	   <th>
             <input type="checkbox" value="Reset" name="restartchk">&nbsp;
             <input type="submit" value="Reset" name="restartbut">&nbsp;
      	   </th>
      	   <th>
             <input type="checkbox" value="Quit" name="quitchk">&nbsp;
             <input type="submit" value="Quit" name="quitbut">
           </th>
        </tr>      
      </table>
<?php
    }
    
    echo "</form>";
    
  }
?>

<form name=save action="edithost.php?id=<?php echo $id; ?>" method="post">
<table id="savetable" align=center>
    <thead>
    	<tr>
        	<th scope="col" class="rounded-company">Name</th>
            <th scope="col" class="rounded-q1">IP / Hostname</th>
            <th scope="col" class="rounded-q1">Port</th>
            <th scope="col" class="rounded-q1"><?php if($Hash_Method=='scrypt'){echo "KH/s";}else{ echo "MH/s";}?> desired</th>
        </tr>
        <tr>
          <td align=center><input type="text" name="macname" value="<?php echo $host_data['name']; ?>"></td>
          <td align=center><input type="text" name="ipaddress" value="<?php echo $host_data['address']; ?>"></td>
          <td align=center><input type="text" name="port" value="<?php echo $host_data['port']; ?>"></td>
          <td align=center><input type="text" name="mhash" value="<?php echo $host_data['mhash_desired']; ?>"></td>
        </tr>
        <tr>
        <td colspan=4 align=center><input type=hidden name="savehostid" value="<?php echo $id; ?>"><input type="submit" value="Save" name="save"><input type="submit" value="Delete this host" name="delete"></td>
        </tr>
    </thead>
</table>

</form>

<?php	
}
else {
	echo "Host not found or you just deleted the host !<BR>";
}
?>
                <div class="cleaner h20"></div>
<!--                 <a href="#" class="more float_r"></a> -->
            </div>

            <div class="cleaner"></div>
		</div>

        <div class="cleaner"></div>
        </div>
    </div>
    
    <div class="cleaner"></div>

<div id="templatemo_footer_wrapper">
    <div id="templatemo_footer">
        <?php include("footer.inc.php"); ?>
        <div class="cleaner"></div>
    </div>
</div> 
  
</body>
</html>
