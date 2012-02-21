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

if (isset($_POST['saveconf'])) {
	$updstring = "";

	if (isset($_POST['yellowtemp'])) {
		$yellowtemp = mysql_real_escape_string($_POST['yellowtemp']);
		$updstring = $updstring . " yellowtemp = '$yellowtemp', ";
	}

	if (isset($_POST['maxtemp'])) {
		$maxtemp = mysql_real_escape_string($_POST['maxtemp']);
		$updstring = $updstring . " maxtemp = '$maxtemp', ";
	}

	if (isset($_POST['yellowrejects'])) {
		$yellowrejects = mysql_real_escape_string($_POST['yellowrejects']);
		$updstring = $updstring . " yellowrejects = '$yellowrejects', ";
	}

	if (isset($_POST['maxrejects'])) {
		$maxrejects = mysql_real_escape_string($_POST['maxrejects']);
		$updstring = $updstring . " maxrejects = '$maxrejects', ";
	}

	if (isset($_POST['yellowdiscards'])) {
		$yellowdiscards = mysql_real_escape_string($_POST['yellowdiscards']);
		$updstring = $updstring . " yellowdiscards = '$yellowdiscards', ";
	}

	if (isset($_POST['maxdiscards'])) {
		$maxdiscards = mysql_real_escape_string($_POST['maxdiscards']);
		$updstring = $updstring . " maxdiscards = '$maxdiscards', ";
	}

	if (isset($_POST['yellowstales'])) {
		$yellowstales = mysql_real_escape_string($_POST['yellowstales']);
		$updstring = $updstring . " yellowstales = '$yellowstales', ";
	}
	
	if (isset($_POST['maxstales'])) {
		$maxstales = mysql_real_escape_string($_POST['maxstales']);
		$updstring = $updstring . " maxstales = '$maxstales', ";
	}

	if (isset($_POST['yellowgetfails'])) {
		$yellowgetfails = mysql_real_escape_string($_POST['yellowgetfails']);
		$updstring = $updstring . " yellowgetfails = '$yellowgetfails', ";
	}

	if (isset($_POST['maxgetfails'])) {
		$maxgetfails = mysql_real_escape_string($_POST['maxgetfails']);
		$updstring = $updstring . " maxgetfails = '$maxgetfails', ";
	}

	if (isset($_POST['yellowremfails'])) {
		$yellowremfails = mysql_real_escape_string($_POST['yellowremfails']);
		$updstring = $updstring . " yellowremfails = '$yellowremfails', ";
	}

	if (isset($_POST['maxremfails'])) {
		$maxremfails = mysql_real_escape_string($_POST['maxremfails']);
		$updstring = $updstring . " maxremfails = '$maxremfails', ";
	}

	if (isset($_POST['yellowfan'])) {
		$yellowfan = mysql_real_escape_string($_POST['yellowfan']);
		$updstring = $updstring . " yellowfan = '$yellowfan', ";
	}	

	if (isset($_POST['maxfan'])) {
		$maxfan = mysql_real_escape_string($_POST['maxfan']);
		$updstring = $updstring . " maxfan = '$maxfan', ";
	}

	if (isset($_POST['yellowgessper'])) {
		$yellowgessper = mysql_real_escape_string($_POST['yellowgessper']);
		$updstring = $updstring . " yellowgessper = '$yellowgessper', ";
	}

	if (isset($_POST['maxgessper'])) {
		$maxgessper = mysql_real_escape_string($_POST['maxgessper']);
		$updstring = $updstring . " maxgessper = '$maxgessper', ";
	}

	if (isset($_POST['yellowavgmhper'])) {
		$yellowavgmhper = mysql_real_escape_string($_POST['yellowavgmhper']);
		$updstring = $updstring . " yellowavgmhper = '$yellowavgmhper', ";
	}

	if (isset($_POST['email'])) {
		$email = mysql_real_escape_string($_POST['email']);
		$updstring = $updstring . " email = '$email', ";
	}
		
	$updstring = substr($updstring,0,-2);
	
	$updstring = "UPDATE configuration SET ".$updstring."";
	$updcr = mysql_query($updstring);
	if (!$updcr) {
    	die('FATAL: MySQL-Error: ' . mysql_error());
	} else {
		$updated = 1;
	}	
	
	//echo "Final Updstring: $updstring !";

}

$configq = mysql_query('SELECT * FROM configuration');
if (!$configq) {
    die('FATAL: MySQL-Error: ' . mysql_error());
}
$config = mysql_fetch_object($configq);



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
          		<li><a href="config.php" class="selected">Configuration</a>

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
            	<h2>Configuration</h2>
				 
                <div class="cleaner h20"></div>

<?
if (isset($updated) && $updated == 1)
echo "<b>Configuration updated !</b>";

?>

<form name=save action="config.php" method="post">


<table id="rounded-corner" summary="Hostsummary" align="center">
    <thead>
    	<tr>
    		<th scope="col" class="rounded-company">Value</th>
        	<th scope="col" class="rounded-company">Yellow</th>
            <th scope="col" class="rounded-q1">Red</th>

        </tr>
        <tr>
        <td class="blue">GPU Temperature</td>
        <td><input type=text name="yellowtemp" value="<?=$config->yellowtemp?>"></td>
        <td><input type=text name="maxtemp" value="<?=$config->maxtemp?>"></td>
        </tr>
        <tr>
        <td class="blue">Rejects</td>
        <td><input type=text name="yellowrejects" value="<?=$config->yellowrejects?>"></td>
        <td><input type=text name="maxrejects" value="<?=$config->maxrejects?>"></td>        
        </tr>
        <tr>
        <td class="blue">Discards</td>
        <td><input type=text name="yellowdiscards" value="<?=$config->yellowdiscards?>"></td>
        <td><input type=text name="maxdiscards" value="<?=$config->maxdiscards?>"></td>        
        </tr>
        <tr>
        <td class="blue">Stales</td>
        <td><input type=text name="yellowstales" value="<?=$config->yellowstales?>"></td>
        <td><input type=text name="maxstales" value="<?=$config->maxstales?>"></td>        
        </tr>
        <tr>
        <td class="blue">Get Fails</td>
        <td><input type=text name="yellowgetfails" value="<?=$config->yellowgetfails?>"></td>
        <td><input type=text name="maxgetfails" value="<?=$config->maxgetfails?>"></td>        
        </tr>    
        <tr>  
        <td class="blue">Rem Fails</td>
        <td><input type=text name="yellowremfails" value="<?=$config->yellowremfails?>"></td>
        <td><input type=text name="maxremfails" value="<?=$config->maxremfails?>"></td>        
        </tr> 
        <tr>
        <td class="blue">Fan Percent</td>
        <td><input type=text name="yellowfan" value="<?=$config->yellowfan?>"></td>
        <td><input type=text name="maxfan" value="<?=$config->maxfan?>"></td>        
        </tr>
        <tr>
        <td class="blue">min. % of desired 5s MH/s</td>
        <td><input type=text name="yellowgessper" value="<?=$config->yellowgessper?>"></td>
        <td><input type=text name="maxgessper" value="<?=$config->maxgessper?>"></td>        
        </tr>
        <tr>
        <td class="blue">min. % of desired average MH/s</td>
        <td><input type=text name="yellowavgmhper" value="<?=$config->yellowavgmhper?>"></td>
        <td><input type=text name="maxavgmhper" value="<?=$config->maxavgmhper?>"></td>        
        </tr>
        <tr>
        <td class="blue">E-Mail Address for Notifications</td>
        <td colspan=2><input type=text name="email" value="<?=$config->email?>"></td>
        </tr>        
        <tr>
        <td colspan="3" class="blue"><input type=submit name=saveconf value="Save"></td>
        </tr> 
    </thead>
</table>

</form>                
                
                
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