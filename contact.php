<?
require("config.inc.php");

$dbh = anubis_db_connect();

$configq = $dbh->query('SELECT * FROM configuration');
if (!$configq) {
    die('FATAL: DB-Error: ' . db_error());
}
$config = $configq->fetch(PDO::FETCH_OBJ);

$result = $dbh->query('SELECT name,address,id AS hostid FROM hosts');
if (!$result) {
    die('FATAL: DB-Error: ' . db_error());
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
              	<li><a href="index.php" >Home</a></li>

              	</li>
          		<li><a href="config.php">Configuration</a>

              	</li>
              	<li><a href="faq.php">FAQ</a>

                </li>
              	<li><a href="contact.php" class="selected">Contact/Donate</a></li>
            </ul>
            <br style="clear: left" />
        </div> <!-- end of templatemo_menu -->
        
    </div> <!-- end of header -->
    
    
    <div id="templatemo_main">
    	<div class="col_fw">
        	<div class="templatemo_megacontent">
            	<h2>Hosts</h2>
				 
                <div class="cleaner h20"></div>

<table id="rounded-corner" summary="Hostsummary">
    <thead>
    	<tr>
			<th>Contact</th></tr><tr>
			<td> You can msg me (ocminer) or have a look here: <a href="https://bitcointalk.org/index.php?board=42.0">https://bitcointalk.org/index.php?board=42.0</a> 
			If that does not work, drop me a mail at m@rcel.to </td>
        </tr>
    	<tr>
			<th>Donations</th></tr><tr>
			<td>If you like Anubis I would really appreciate every donation to 1KgAKimMrbR6h4R6JBff9s3JqQCS53y149 </a></td>
        </tr>
        
    </thead>
</table>

                
                
                
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