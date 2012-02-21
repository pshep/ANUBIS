<?
require("config.inc.php");

$link = mysql_connect($dbhost, $dbusername, $dbpassword);
if (!$link) {
    die('FATAL: MySQL Connection failed ! ' . mysql_error());
}
$db_selected = mysql_select_db($dbdatabase, $link);
if (!$db_selected) {
    die ('FATAL: Cannot use anubis_db !  ' . mysql_error());
}

$configq = mysql_query('SELECT * FROM configuration');
if (!$configq) {
    die('FATAL: MySQL-Error: ' . mysql_error());
}
$config = mysql_fetch_object($configq);

if (isset($_POST['savehostid'])) {
	$id = 0 + $_POST['savehostid'];
	$newname = mysql_real_escape_string($_POST['macname']);
	$address = mysql_real_escape_string($_POST['ipaddress']);
	$port = mysql_real_escape_string($_POST['port']);
	if (!isset($port) || !is_numeric($port)) 
	$port = '';
	$mhash = mysql_real_escape_string($_POST['mhash']);

	//echo "Fields: $id / $newname / $address / $mhash <BR>";

	
	if ($newname && $newname !== "" && $address && $address !== "") {
		$updq = "INSERT INTO hosts (name, address, port, mhash_desired) VALUES ('$newname', '$address', '$port', '$mhash')";
		//echo "UPDQ: $updq <BR>";
		$updr = mysql_query($updq);
		if (!$updr) {
    		die('FATAL: MySQL-Error: ' . mysql_error());
		}
		if (mysql_affected_rows() > 0)
			$askq = "SELECT id FROM hosts WHERE address = '$address' AND name = '$newname'";
			$askr = mysql_query($askq);
			if (!$askr) {
    			die('FATAL: MySQL-Error: ' . mysql_error());
			}
			$idr = mysql_fetch_assoc($askr);
			$id = $idr['id'];
	
	}



$id = mysql_real_escape_string($id);

$result = mysql_query("SELECT * FROM hosts WHERE id = $id");
if (!$result) {
    die('FATAL: MySQL-Error: ' . mysql_error());
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

	<div id="templatemo_header">
    
    	<div id="site_title"><h1><a href="index.php">Main</a></h1></div>
        
        <div id="templatemo_menu" class="ddsmoothmenu">
            <ul>
              	<li><a href="index.php" class="selected">Home</a></li>

              	</li>
          		<li><a href="config.php">Configuration</a>

              	</li>
              	<li><a href="faq.php">FAQ</a>

                </li>
              	<li><a href="contact.php">Contact/Donate</a></li>
            </ul>
            <br style="clear: left" />
        </div> <!-- end of templatemo_menu -->
        
    </div> <!-- end of header -->
    
    
    <div id="templatemo_main">
    	<div class="col_fw">
        	<div class="templatemo_megacontent">
            	<h2>Add host</h2>
				 
                <div class="cleaner h20"></div>

<?
#
# Sample Socket I/O to CGMiner API
#
function getsock($addr, $port)
{
 $socket = null;
 $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
 if ($socket === false || $socket === null)
 {
	$error = socket_strerror(socket_last_error());
	$msg = "socket create(TCP) failed";
	echo "ERR: $msg '$error'\n";
	return null;
 }

 $res = socket_connect($socket, $addr, $port);
 if ($res === false)
 {
	$error = socket_strerror(socket_last_error());
	$msg = "socket connect($addr,$port) failed";
	echo "ERR: $msg '$error'\n";
	socket_close($socket);
	return null;
 }
 return $socket;
}
#
# Slow ...
function readsockline($socket)
{
 $line = '';
 while (true)
 {
	$byte = socket_read($socket, 1);
	if ($byte === false || $byte === '')
		break;
	if ($byte === "\0")
		break;
	$line .= $byte;
 }
 return $line;
}
#
function request($cmd,$host,$name,$hostid,$mhash,$hostport) {
global $config;
global $gpucol;

 $socket = getsock($host, $hostport);
 if ($socket != null)
 {
	socket_write($socket, $cmd, strlen($cmd));
	$line = readsockline($socket);
	socket_close($socket);

	if (strlen($line) == 0)
	{
		echo "WARN: '$cmd' returned nothing\n";
		return $line;
	}

	//print "$cmd returned '$line'\n";

	if (substr($line,0,1) == '{')
		$data = json_decode($line, true);
	
	$thisstatus = $data['STATUS'][0]['STATUS'];
	$avgmhash =   $data['SUMMARY'][0]['MHS av'];
	$accepted =   $data['SUMMARY'][0]['Accepted'];
	$rejected =   $data['SUMMARY'][0]['Rejected'];
	$discarded =  $data['SUMMARY'][0]['Discarded'];
	$stale =      $data['SUMMARY'][0]['Stale'];	
	$getfail =    $data['SUMMARY'][0]['Get Failures'];
	$remfail =    $data['SUMMARY'][0]['Remote Failures'];
		
	$rejects = 100 / $accepted * $rejected;
	$rejects = round($rejects,2);

	$discards = 100 / $accepted * $discarded;
	$discards = round($discards,2);	

	$stales = 100 / $accepted * $stale;
	$stales = round($stales,2);
	
	$getfails = 100 / $accepted * $getfail;
	$getfails = round($getfails,2);
	
	$remfails = 100 / $accepted * $remfail;
	$remfails = round($remfails,2);
	
	
	if ($thisstatus && $thisstatus !== "") {
			$gpus = 0;
			$cpus = 0;
			
			$arr = array ('command'=>'devs','parameter'=>'');
			$subreq = json_encode($arr);

 			$socket2 = getsock($host, 4028);
 			if ($socket2 != null) {
				socket_write($socket2, $subreq, strlen($subreq));
				$line2 = readsockline($socket2);
				socket_close($socket2);

			if (strlen($line2) == 0) {
				echo "WARN: '$subreq' returned nothing\n";
				return $line2;
			}

			//print "$cmd returned '$line'\n";

			if (substr($line2,0,1) == '{')
				$data2 = json_decode($line2, true);
			
			$thismaxgpus = trim($data2['STATUS'][0]['Msg']);
			$newcu = preg_match("/(?P<gpus>\d+) GPU\(s\) - (?P<cpus>\d+) CPU/",$thismaxgpus, $matches);
			
			$gpus = $matches['gpus'];
			$cpus = $matches['cpus'];
			$i = 0;
			$geshash = 0;
			$activegpus = 0;
			$cftemp = 0;
			
			
			while($i < $gpus) {

				$thisgpumhash = $data2['DEVS'][$i]['MHS 5s'];
				$geshash = $geshash + $thisgpumhash;
				
				if ($data2['DEVS'][$i]['Status'] == "Alive" && $data2['DEVS'][$i]['Enabled'] == "Y") {
					$activegpus++;
					$cftemp = $cftemp + $data2['DEVS'][$i]['Temperature'];
				}
				
				// Temperature

				if ($data2['DEVS'][$i]['Temperature'] < $config->yellowtemp)
					$tmpcol = "class=green";		
				if ($data2['DEVS'][$i]['Temperature'] >= $config->yellowtemp)
					$tmpcol = "class=yellow";
				if ($data2['DEVS'][$i]['Temperature'] >= $config->maxtemp)
					$tmpcol = "class=red";

				// Fans

				if ($data2['DEVS'][$i]['Fan Percent'] < $config->yellowfan)
					$fancol = "class=green";		
				if ($data2['DEVS'][$i]['Fan Percent'] >= $config->yellowfan)
					$fancol = "class=yellow";
				if ($data2['DEVS'][$i]['Fan Percent'] >= $config->maxfan)
					$fancol = "class=red";				

				// Enabled

				if ($data2['DEVS'][$i]['Enabled'] == "Y")
					$encol = "class=green";
				else
					$encol = "class=red";

				// Alive

				if ($data2['DEVS'][$i]['Status'] == "Alive")
					$alcol = "class=green";
				else
					$alcol = "class=red";

				$gpucol .= " <tr>
							<td>".$data2['DEVS'][$i]['GPU']."</td>
							<td $encol>".$data2['DEVS'][$i]['Enabled']."</td>
						   	<td $alcol>".$data2['DEVS'][$i]['Status']."</td>						   	
							<td $tmpcol>".$data2['DEVS'][$i]['Temperature']."</td>
							<td $fancol>".$data2['DEVS'][$i]['Fan Speed']."</td>
						   	<td $fancol>".$data2['DEVS'][$i]['Fan Percent']."</td>
							<td>".$data2['DEVS'][$i]['GPU Clock']."</td>
							<td>".$data2['DEVS'][$i]['Memory Clock']."</td>
						   	<td>".$data2['DEVS'][$i]['GPU Voltage']."</td>						   	
							<td>".$data2['DEVS'][$i]['GPU Activity']."</td>
						   	<td>".$data2['DEVS'][$i]['MHS av']."</td>
							<td>".$data2['DEVS'][$i]['MHS 5s']."</td>
							<td>".$data2['DEVS'][$i]['Accepted']."</td>
						   	<td>".$data2['DEVS'][$i]['Rejected']."</td>
							<td>".$data2['DEVS'][$i]['Hardware Errors']."</td>
							<td>".$data2['DEVS'][$i]['Utility']."</td>
						   	<td>".$data2['DEVS'][$i]['Intensity']."</td>
						    </tr>";
						   
				//echo "TGM: $thisgpumhash <BR>";

			$i++;
			}
			
			$cftemp = $cftemp / $activegpus;
			$cftemp = round($cftemp,2);
					
			}
					
			
		
	}
	

// Color-Stuff comes here -->	
	
	if ($thisstatus == "S")
		$thisstatuscol = "class=green";
	else
		$thisstatuscol = "class=yellow";
		
	if ($activegpus == $gpus)
		$thisgpucol = "class=green";
	else
		$thisgpucol = "class=red";

// Temperature

	if ($cftemp < $config->yellowtemp)
		$cfcol = "class=green";		
	if ($cftemp >= $config->yellowtemp)
		$cfcol = "class=yellow";
	if ($cftemp >= $config->maxtemp)
		$cfcol = "class=red";	

// Rejects

	if ($rejects < $config->yellowrejects)
		$rejectscol = "class=green";		
	if ($rejects >= $config->yellowrejects)
		$rejectscol = "class=yellow";
	if ($rejects >= $config->maxrejects)
		$rejectscol = "class=red";	
				
// Discards

	if ($discards < $config->yellowdiscards)
		$discardscol = "class=green";		
	if ($discards >= $config->yellowdiscards)
		$discardscol = "class=yellow";
	if ($discards >= $config->maxdiscards)
		$discardscol = "class=red";	

// Stales

	if ($stales < $config->yellowstales)
		$stalescol = "class=green";		
	if ($stales >= $config->yellowstales)
		$stalescol = "class=yellow";
	if ($stales >= $config->maxstales)
		$stalescol = "class=red";	

// Get fails

	if ($getfails < $config->yellowgetfails)
		$getfailscol = "class=green";		
	if ($getfails >= $config->yellowgetfails)
		$getfailscol = "class=yellow";
	if ($getfails >= $config->maxgetfails)
		$getfailscol = "class=red";
						
// Rem fails

	if ($remfails < $config->yellowremfails)
		$remfailscol = "class=green";		
	if ($remfails >= $config->yellowremfails)
		$remfailscol = "class=yellow";
	if ($remfails >= $config->maxremfails)
		$remfailscol = "class=red";

if ($mhash > 0) {
// Desired Mhash vs. total mhash

	$gessper = 100 / $mhash * $geshash;
	$gessper = round($gessper,2);

	if ($gessper > $config->yellowgessper)
		$gesspercol = "class=green";		
	if ($gessper <= $config->yellowgessper)
		$gesspercol = "class=yellow";
	if ($gessper <= $config->maxgessper)
		$gesspercol = "class=red";					

// Desired Mhash vs. avg mhash

	$avgmhper = 100 / $mhash * $avgmhash;
	$avgmhper = round($avgmhper,2);

	if ($avgmhper > $config->yellowavgmhper)
		$avgmhpercol = "class=green";		
	if ($avgmhper <= $config->yellowavgmhper)
		$avgmhpercol = "class=yellow";
	if ($avgmhper <= $config->maxavgmhper)
		$avgmhpercol = "class=red";
}
				
// <-- Color Stuff

	
	echo "<tbody><tr><td><a href=\"edithost.php?id=$hostid\">$host ($name)</a></td><td $thisstatuscol>$thisstatus</td>
	<td $thisgpucol>$activegpus/$gpus</td><td $cfcol>$cftemp </td><td>$mhash</td><td $gesspercol>$geshash <BR>($gessper %)</td><td $avgmhpercol>$avgmhash <BR> ($avgmhper %)</td><td $rejectscol>$rejects %</td>
	<td $discardscol>$discards %</td><td $stalescol>$stales %</td><td $getfailscol>$getfails %</td><td $remfailscol>$remfails %</td></tr></tbody>";


	return $data;
 }

 return null;
}

//// -->> Main 

if (isset($id)) {
$num_rows = mysql_num_rows($result);
if ($num_rows > 0) {

echo "<b>Host has been added !</b><BR>";

?>
<table id="rounded-corner" summary="Hostsummary" align="center">
    <thead>
    	<tr>
        	<th scope="col" class="rounded-company">Address</th>
            <th scope="col" class="rounded-q1">Status</th>
            <th scope="col" class="rounded-q2">GPU's</th>
            <th scope="col" class="rounded-q4">Temp avg</th>
            <th scope="col" class="rounded-q1">MHash/s des</th>
            <th scope="col" class="rounded-q2">MHash/s 5s</th>
            <th scope="col" class="rounded-q3">MHash/s avg</th>
            <th scope="col" class="rounded-q4">Rejects</th>
            <th scope="col" class="rounded-q2">Discards</th>
            <th scope="col" class="rounded-q3">Stales</th>
            <th scope="col" class="rounded-q4">Get Fails</th>
            <th scope="col" class="rounded-q4">Rem Fails</th>
        </tr>
    </thead>

<?

	$arr = array ('command'=>'summary','parameter'=>'');
	$reqcmd = json_encode($arr);


	while ($row = mysql_fetch_assoc($result)) {
    
    	$host = $row['address'];
    	$hostport = $row['port'];
    	$name = $row['name'];
    	$hostid = $row['id'];
    	$mhash = $row['mhash_desired'];
    	
		$r = request($reqcmd,$host,$name,$hostid,$mhash,$hostport);

	
	}
	
	echo "</table>";

?>

<table id="rounded-corner" summary="Hostsummary" align="center">
    <thead>
    	<tr>
        	<th scope="col" class="rounded-company">GPU #</th>
            <th scope="col" class="rounded-q1">En</th>
            <th scope="col" class="rounded-q2">Status</th>
            <th scope="col" class="rounded-q4">Temp</th>
            <th scope="col" class="rounded-q1">Fan Speed</th>
            <th scope="col" class="rounded-q2">Fan %</th>
            <th scope="col" class="rounded-q3">GPU Clk</th>
            <th scope="col" class="rounded-q4">Mem Clk</th>
            <th scope="col" class="rounded-q2">Volt</th>
            <th scope="col" class="rounded-q3">Actv</th>
            <th scope="col" class="rounded-q4">MH/s avg</th>
            <th scope="col" class="rounded-q4">MH/s 5s</th>
            <th scope="col" class="rounded-q3">Acc</th>
            <th scope="col" class="rounded-q4">Rej</th>
            <th scope="col" class="rounded-q4">Harderr</th>
            <th scope="col" class="rounded-q4">Util</th>
            <th scope="col" class="rounded-q4">Intens</th>
        </tr>
        <? echo "$gpucol"; ?>
    </thead>
</table>

<?
} }
?>

<form name=save action="addhost.php" method="post">

<table id="savetable" align=center>
    <thead>
    	<tr>
        	<th scope="col" class="rounded-company">Name</th>
            <th scope="col" class="rounded-q1">IP / Hostname</th>
            <th scope="col" class="rounded-q1">Port</th>
            <th scope="col" class="rounded-q1">MH/s desired</th>
        </tr>
        <tr>
        <td align=center><input type="text" name="macname" value=""></td>
        <td align=center><input type="text" name="ipaddress" value=""></td>
        <td align=center><input type="text" name="port" value="4028"></td>
        <td align=center><input type="text" name="mhash" value=""></td>
        </tr>
        <tr>
        <td colspan=4 align=center><input type=hidden name="savehostid" value="<?=$hostid?>"><input type="submit" value="Save"></td>
        </tr>
    </thead>
</table>

</form>

<p align=center>
<b>Name:</b> You can enter any name you like.<BR>
<b>IP/Hostname:</b> Enter the IP or Hostname of your cgminer cgapi enabled host. I.E. 10.10.1.10 or 192.168.1.10. You can also use FQDN so miner1.mynet.com i.e.<BR>
<b>Port:</b> The port CGMINER is listening on (default 4028)<BR>
<b>MH/s desired:</b> If you already now how much MH/s your host will/should make, enter it here.<BR>
<BR>
You can change any value afterwards.<BR>
</p>
<?	

/*
}
else {
	echo "No Hosts found !<BR>";
}
*/
//// <<--- Main


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
        <? include("footer.inc.php"); ?>
        <div class="cleaner"></div>
    </div>
</div> 
  
</body>
</html>