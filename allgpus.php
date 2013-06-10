<?php
require("config.inc.php");
require("func.inc.php");

$dbh = anubis_db_connect();
$config = get_config_data();

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
            	<h2>Hosts</h2>
				 <a href="index.php">Back to normal view</a>				 
                <div class="cleaner h20"></div>

<?php

$result = $dbh->query("SELECT * FROM hosts ORDER BY name ASC");
if ($result)
{
  echo "<table id='rounded-corner' summary='Hostsummary'>";
  echo create_host_header();

	while ($host_data = $result->fetch(PDO::FETCH_ASSOC))
	{
      $host_alive = get_host_status($host_data);

      echo get_host_summary($host_data);
      if ($host_alive)
      {
        $privileged = get_privileged_status($host_data);
        echo "<tr><td colspan='16'>";
          echo "<table id='rounded-corner' summary='PoolSummary' align='center'>";
          echo create_pool_header();
          echo process_pools_disp($host_data);
          echo "</table>";
        
          echo "<table id='rounded-corner' summary='DevsSummary' align='center'>";
          echo create_devs_header();
          echo process_devs_disp($host_data);
          echo "</table>";
        echo "</td></tr>";
      }
    }

    echo create_totals();
	echo "</table>";
}
else 
{
	echo "No Hosts found, you might like to <a href=\"addhost.php\">add a host</a> ?<BR>";
}

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
        <?php include("footer.inc.php"); ?>
        <div class="cleaner"></div>
    </div>
</div> 
  
</body>
</html>
