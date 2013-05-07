<?php
require("config.inc.php");
require("acc.inc.php");

$dbh = anubis_db_connect();

$result = $dbh->query($show_tables);
db_error();

while ($row = $result->fetch(PDO::FETCH_NUM))
{
  if ($row[0] == "accounts")
    $gotaccountstbl = 1;
  if ($row[0] == "accgroups")
    $gotgroupstbl = 1;
}

if (!isset($gotaccountstbl))
  create_accounts_table();

if (!isset($gotgroupstbl))
  create_accgroups_table();

db_error();


if (isset($_POST['addgroup']))
{
  $grp_name = $dbh->quote(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
  $grp_curr = $dbh->quote(filter_input(INPUT_POST, 'currency', FILTER_SANITIZE_STRING));

  $updq = "INSERT INTO accgroups (name, currency) VALUES ($grp_name, $grp_curr)";
  $updr = $dbh->exec($updq);
  db_error();
}

if (isset($_POST['addacc']))
{
    $acc_name = $dbh->quote(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
    $acc_addr = $dbh->quote(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING));

    $updq = "INSERT INTO accounts (name, address, `group`) VALUES (".$acc_name.", ".$acc_addr.", ".$_POST['groupid'].");";
    $updr = $dbh->exec($updq);
    db_error();
}

if (isset($_POST['delete']))
{
    foreach ($_POST['del_acc'] as $acc_id)
    {
      $updq = "DELETE FROM accounts WHERE id = ".$acc_id.";";
      $updr = $dbh->exec($updq);
      db_error();
    }
    
    if(isset($_POST['deletegrp']))
    {
      $updq = "DELETE FROM accounts WHERE `group` = ".$_POST['deletegrp'].";";
      $updr = $dbh->exec($updq);
      db_error();
      
      $updq = "DELETE FROM accgroups WHERE id = ".$_POST['deletegrp'].";";
      $updr = $dbh->exec($updq);
      db_error();    
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
            	<h2>Accounts</h2>
                <div class="cleaner h20"></div>

<?php


$grp_result = $dbh->query("SELECT * FROM accgroups ORDER BY name ASC");
  db_error();

if ($grp_result)
{

	while ($group_data = $grp_result->fetch(PDO::FETCH_ASSOC))
	{
        $group_id = $group_data['id'];
        echo "<form name=add action='accounts.php' method='post'>";
        echo "<table id='rounded-corner' summary='GroupSummary'>";
        echo create_group_header($group_data);
        
        $acc_result = $dbh->query("SELECT * FROM accounts WHERE `group` = '".$group_id."' ORDER BY name ASC");
        db_error();        
        if ($acc_result)
        {
          while ($acc_data = $acc_result->fetch(PDO::FETCH_ASSOC))
          {
            echo get_acc_summary($acc_data, $group_data);
          }
        }
        
        echo create_group_totals();
	    echo "</table>";
	    echo "</form>";
    }

}

$currency_list = "";
foreach($mtgox_currencys as $symbol)
  $currency_list .= "<option>" . $symbol . "</option>";

?>

<form name=save action="accounts.php" method="post">
<table id="savetable" align=center>
    <thead>
    	<tr>
        	<th scope="col" class="rounded-company">Name</th>
            <th scope="col" class="rounded-company">Currency</th>
            <th>&nbsp;</th>
        </tr>
        <tr>
          <td align=center><input type="text" name="name" value=""></td>
          <td align=center><select name="currency"><?php echo $currency_list; ?><select></td>
          <td colspan=2 align=center><input type="submit" value="Add new group" name="addgroup"></td>
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
        <?php include("footer.inc.php"); ?>
        <div class="cleaner"></div>
    </div>
</div> 
  
</body>
</html>
