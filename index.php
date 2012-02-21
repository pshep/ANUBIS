<?

require("config.inc.php");

$link = mysql_connect($dbhost, $dbusername, $dbpassword);
if (!$link) {
    die('FATAL: MySQL Connection failed ! ' . mysql_error());
}
$db_selected = mysql_select_db($dbdatabase, $link);
if (!$db_selected) {
    die ('FATAL: Cannot use Anubis_db !  ' . mysql_error());
}


// Checking for Tables -->

$sql = "SHOW TABLES";
$result = mysql_query($sql);

if (!$result) {
    echo "DB Fehler, konnte Tabellen nicht auflisten\n";
    echo 'MySQL Fehler: ' . mysql_error();
    exit;
}

while ($row = mysql_fetch_row($result)) {
    if ($row[0] == "configuration")
    	$gotconfigtbl = 1;
    if ($row[0] == "hosts")
    	$gothoststbl = 1;    	
}

if (isset($gothoststbl) && $gothoststbl == 1) {

$result = mysql_query("SHOW COLUMNS FROM hosts");
if (!$result) {
    echo 'Konnte Abfrage nicht ausführen: ' . mysql_error();
    exit;
}
if (mysql_num_rows($result) > 0) {
    while ($row = mysql_fetch_assoc($result)) {
        if ($row['Field'] == "port")
        	$gotport = 1;
    }
}

if (!isset($gotport)) {
	$alterstrg = "ALTER TABLE  `hosts` ADD  `port` SMALLINT NOT NULL DEFAULT  '4028'";
	$alterres = mysql_query($alterstrg);
	if (!$alterres) {
    	echo 'There was an error updating the database ! : ' . mysql_error();
	}
}
	
}

$sumactivegpus = 0;
$sumgpus = 0;
$sumhosts = 0;
$sumtemp = 0;
$sumdesmhs= 0;
$summhs5s = 0;
$summhsavg = 0;
$sumrejects = 0;
$sumdiscards = 0;
$sumstales = 0;
$sumgetfails = 0;
$sumremfails = 0;

//echo "c: $gotconfigtbl h: $gothoststbl";

if (!isset($gotconfigtbl)) {
	//echo "creating config-table";
	include("configtbl.sql.php");
}

if (!isset($gothoststbl)) {
	//echo "creating hosts-table";
	include("hoststbl.sql.php");
}

// <-- checking for tables

$configq = mysql_query('SELECT * FROM configuration');
if (!$configq) {
    die('FATAL: MySQL-Error: ' . mysql_error());
}
$config = mysql_fetch_object($configq);

$result = mysql_query('SELECT name,address,id AS hostid,mhash_desired,port FROM hosts');
if (!$result) {
    die('FATAL: MySQL-Error: ' . mysql_error());
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
            	<h2>Hosts</h2>
				 <a href="allgpus.php">Expand all Hosts</a>
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



// ---> Timeout
/*

	$timeout = 3;

    @socket_set_nonblock($socket);
      //or die("Unable to set nonblock on socket\n");

    $time = time();
    while (!@socket_connect($socket, $addr, $port))
    {
      $err = socket_last_error($socket);
      if ($err == 115 || $err == 114)
      {
        if ((time() - $time) >= $timeout)
        {
          socket_close($socket);
          print("Connection timed out.\n");
          break;
        }
        sleep(1);
        continue;
      }
      socket_close($socket);
      //die(socket_strerror($err) . "\n");
    }

    @socket_set_block($socket);
      //or print("Unable to set block on socket\n");
      
*/

// <<--- TImeout


socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => '3', 'usec' => '3000'));
$res = @socket_connect($socket, $addr, $port);
 if ($res === false)
 {
	$error = socket_strerror(socket_last_error());
	$msg = "socket connect($addr,$port) failed (timeout)";
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
global $sumactivegpus;
global $sumgpus;
global $sumhosts;
global $sumtemp;
global $sumdesmhs;
global $summhs5s;
global $summhsavg;
global $sumrejects;
global $sumdiscards;
global $sumstales;
global $sumgetfails;
global $sumremfails;

$sumhosts++;

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
	
	if (isset($accepted) && $accepted !== 0) {	
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
	}
	
	// Sum Stuff
	
	$sumrejects = $sumrejects + $rejects;
	$sumdiscards = $sumdiscards + $discards;
	$sumstales = $sumstales + $stales;
	$sumgetfails = $sumgetfails + $getfails;
	$sumremfails = $sumremfails + $remfails;
	
	
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
			if (!$newcu)
			$newcu = preg_match("/(?P<gpus>\d+) GPU\(s\)/",$thismaxgpus, $matches);
			
			$gpus = 0 + $matches['gpus'];
			if (isset($matches['cpus']))
			$cpus = 0 + $matches['cpus'];
			
			$i = 0;
			$geshash = 0;
			$activegpus = 0;
			$cftemp = 0;
			
			while($i < $gpus) {
				//echo "$i <> !";
/*
				echo "<pre>";
				print_r($data2);
				echo "</pre>";
*/
				$thisgpumhash = $data2['DEVS'][$i]['MHS 5s'];
				$geshash = $geshash + $thisgpumhash;
				
				if ($data2['DEVS'][$i]['Status'] == "Alive" && $data2['DEVS'][$i]['Enabled'] == "Y") {
					$activegpus++;
					$sumactivegpus++;
					$cftemp = $cftemp + $data2['DEVS'][$i]['Temperature'];
				}				

				$sumgpus++;
				//echo "TGM: $thisgpumhash <BR>";

			$i++;
			}
			
			$cftemp = $cftemp / $activegpus;
			$cftemp = round($cftemp,2);
			
			// Total Temp: 
			$sumtemp = $sumtemp + $cftemp;
					
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
// <-- Color Stuff
}

if (!isset($gesspercol))
$gesspercol = "";
if (!isset($gessper))
$gessper = 0;
if (!isset($avgmhpercol))
$avgmhpercol = "";
if (!isset($avgmhper))
$avgmhper = 0;

	
	echo "<tbody>
	<tr><td>
	
	    <table border=0><tr><td> <a href=\"edithost.php?id=$hostid\"><img src=\"images/edit.png\" border=0></a></td><td><a href=\"edithost.php?id=$hostid\">$host ($name)</a></td></td></tr></table>
	
	</td><td $thisstatuscol>$thisstatus</td> 
	<td $thisgpucol>$activegpus/$gpus</td><td $cfcol>$cftemp </td><td>$mhash</td><td $gesspercol>$geshash <BR>($gessper %)</td><td $avgmhpercol>$avgmhash <BR> ($avgmhper %)</td><td $rejectscol>$rejects %</td>
	<td $discardscol>$discards %</td><td $stalescol>$stales % </td><td $getfailscol>$getfails %</td><td $remfailscol>$remfails %</td></tr></tbody>";

	$sumdesmhs = $sumdesmhs + $mhash;
	$summhs5s = $summhs5s + $geshash;
	$summhsavg = $summhsavg + $avgmhash;

	return $data;
 }

 return null;
}

//// -->> Main 

$num_rows = mysql_num_rows($result);
if ($num_rows > 0) {

?>
<table id="rounded-corner" summary="Hostsummary">
    <thead>
    	<tr>
        	<th scope="col" class="rounded-company">Address</th>
            <th scope="col" class="rounded-q1">Status</th>
            <th scope="col" class="rounded-q2">GPU's</th>
            <th scope="col" class="rounded-q4">Temp avg</th>
            <th scope="col" class="rounded-q1">MH/s des</th>
            <th scope="col" class="rounded-q2">MH/s 5s</th>
            <th scope="col" class="rounded-q3">MH/s avg</th>
            <th scope="col" class="rounded-q4">Rejects</th>
            <th scope="col" class="rounded-q2">Discards</th>
            <th scope="col" class="rounded-q3">Stales</th>
            <th scope="col" class="rounded-q4">Get Fails</th>
            <th scope="col" class="rounded-q4">Rem Fails</th>
        </tr>
    </thead>
<?

	//echo "<table align=center><tr><td>Address</td><td>Status</td><td>GPU's</td><td>Active</td><td>Temp avg</td><td>MHash/s des</td><td>MHash/s 5s</td><td>MHash avg</td><td>Rejects</td><td>Discards</td><td>Stales</td><td>Get Fails</td><td>Rem Fails</td></tr>";

	$arr = array ('command'=>'summary','parameter'=>'');
	$reqcmd = json_encode($arr);


	while ($row = mysql_fetch_assoc($result)) {
    
    	$host = $row['address'];
    	$hostport = $row['port'];
    	$name = $row['name'];
    	$hostid = $row['hostid'];
    	$mhash = $row['mhash_desired'];
    	    	
		$r = request($reqcmd,$host,$name,$hostid,$mhash,$hostport);

/*
		echo "<pre>HOST: $host <BR>";
		echo print_r($r, true)."\n";
		echo "</pre>";
*/
	
	}

?>
    <thead>
    	<tr>
        	<th scope="col" class="rounded-company"> <?=$sumhosts?> Hosts</th>
            <th scope="col" class="rounded-q1"></th>
            <th scope="col" class="rounded-q2"><? echo "$sumgpus/$sumactivegpus"; ?></th>
            <th scope="col" class="rounded-q4"><? $sumtemp = round($sumtemp / $sumhosts,2); echo "$sumtemp";?></th>
            <th scope="col" class="rounded-q1"><?=$sumdesmhs?></th>
            <th scope="col" class="rounded-q2"><?=$summhs5s?></th>
            <th scope="col" class="rounded-q3"><?=$summhsavg?></th>
            <th scope="col" class="rounded-q4"><? $sumrejects = round($sumrejects / $sumhosts,2); echo "$sumrejects %";?></th>
            <th scope="col" class="rounded-q2"><? $sumdiscards = round($sumdiscards / $sumhosts,2); echo "$sumdiscards %";?></th>
            <th scope="col" class="rounded-q3"><? $sumstales = round($sumstales / $sumhosts,2); echo "$sumstales %";?></th>
            <th scope="col" class="rounded-q4"><? $sumgetfails = round($sumgetfails / $sumhosts,2); echo "$sumgetfails %";?></th>
            <th scope="col" class="rounded-q4"><? $sumremfails = round($sumremfails / $sumhosts,2); echo "$sumremfails %";?></th>
        </tr>
    </thead>
<?	
	echo "</table>";
	
}
else {
	echo "No Hosts found, you might like to <a href=\"addhost.php\">add a host</a> ?<BR>";
}
//// <<--- Main


?>

                <table align=center><tr><td align=center><a href="addhost.php"><img src="images/add.png" border=0></a></td><td>Add host</td></tr></table>
                
                
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