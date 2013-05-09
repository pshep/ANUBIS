<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

require("config.inc.php");
require("func.inc.php");

$dbh = anubis_db_connect();

$result = $dbh->query($show_tables);
db_error();

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

$result = $dbh->query("SELECT * FROM hosts ORDER BY name ASC");
if ($result)
{
    echo create_host_header();
	while ($host_data = $result->fetch(PDO::FETCH_ASSOC))
        echo get_host_summary($host_data);
    echo create_totals();
}
?>
