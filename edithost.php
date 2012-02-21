<?
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
		$updr = $dbh->exec($updq);
		if (!$updr) 
    {
      die('FATAL: DB-Error: ' . db_error());
		}
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
  if (isset($_POST['top']))
  {
    $arr = array ('command'=>'switchpool','parameter'=>$_POST['top']);
    $temp = send_request_to_host($arr, $host_data);
    sleep(2);
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
            	<h2>Host detail</h2>				 
                <div class="cleaner h20"></div>
<?
if ($host_data)
{
  echo "<table id='rounded-corner' summary='HostSummary' align='center'>";
  echo create_host_header();
  echo get_host_status($host_data);
  echo "</table>";

  echo "<form name=priority action='edithost.php?id=".$id."' method='post'>";
  echo "<table id='rounded-corner' summary='PoolSummary' align='center'>";
  echo create_pool_header();
  echo process_pools_disp($host_data, true);
  echo "</table>";
  echo "</form>";

  echo "<table id='rounded-corner' summary='DevsSummary' align='center'>";
  echo create_devs_header();
  echo process_devs_disp($host_data);
  echo "</table>";
?>

<form name=save action="edithost.php?id=<?=$id?>" method="post">

<table id="savetable" align=center>
    <thead>
    	<tr>
        	<th scope="col" class="rounded-company">Name</th>
            <th scope="col" class="rounded-q1">IP / Hostname</th>
            <th scope="col" class="rounded-q1">Port</th>
            <th scope="col" class="rounded-q1">MH/s desired</th>
        </tr>
        <tr>
        <td align=center><input type="text" name="macname" value="<?=$host_data['name']?>"></td>
        <td align=center><input type="text" name="ipaddress" value="<?=$host_data['address']?>"></td>
        <td align=center><input type="text" name="port" value="<?=$host_data['port']?>"></td>
        <td align=center><input type="text" name="mhash" value="<?=$host_data['mhash_desired']?>"></td>
        </tr>
        <tr>
        <td colspan=4 align=center><input type=hidden name="savehostid" value="<?=$id?>"><input type="submit" value="Save" name="save"><input type="submit" value="Delete this host" name="delete"></td>
        </tr>
    </thead>
</table>

</form>

<?	
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
        <? include("footer.inc.php"); ?>
        <div class="cleaner"></div>
    </div>
</div> 
  
</body>
</html>
