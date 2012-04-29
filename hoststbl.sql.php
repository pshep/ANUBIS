<?

$tblstr = "
CREATE TABLE IF NOT EXISTS `hosts` (
  `id` ".$primary_key.",
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `port` mediumint(6) NOT NULL DEFAULT '4028',
  `mhash_desired` decimal(6,2) NOT NULL
)".$table_props.";
";

$crr = $dbh->query($tblstr);

if (!$crr) {
    die('FATAL: create hosts error: ' . db_error());
}

?>
