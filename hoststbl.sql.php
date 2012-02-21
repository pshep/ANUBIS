<?

$tblstr = "
CREATE TABLE IF NOT EXISTS `hosts` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `port` smallint(6) NOT NULL DEFAULT '4028',
  `mhash_desired` decimal(6,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
";

$crr = mysql_query($tblstr);

if (!$crr) {
    die('FATAL: MySQL-Error: ' . mysql_error());
}

?>
