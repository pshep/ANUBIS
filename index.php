<?
require("config.inc.php");
require("func.inc.php");

$dbh = anubis_db_connect();

$result = $dbh->query($show_tables);

if (!$result) 
{
    echo "DB error getting table list: " . db_error();
    exit;
}

while ($row = $result->fetch(PDO::FETCH_NUM)) 
{
    if ($row[0] == "configuration")
    	$gotconfigtbl = 1;
    if ($row[0] == "hosts")
    	$gothoststbl = 1;    	
}

if (!isset($gotconfigtbl))
	include("configtbl.sql.php");

if (!isset($gothoststbl))
	include("hoststbl.sql.php");


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


$result = $dbh->query("SELECT * FROM hosts ORDER BY name ASC");
if ($result)
{
    echo "<table id='rounded-corner' summary='Hostsummary'>";

    echo create_host_header();
	while ($host_data = $result->fetch(PDO::FETCH_ASSOC))
        echo get_host_summary($host_data);
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
        <? include("footer.inc.php"); ?>
        <div class="cleaner"></div>
    </div>
</div> 
  
</body>
</html>
