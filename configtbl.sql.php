<?php

$tblstr = "
CREATE TABLE IF NOT EXISTS `configuration` (
  `yellowtemp` int(11) NOT NULL,
  `yellowrejects` int(11) NOT NULL,
  `yellowdiscards` int(11) NOT NULL,
  `yellowstales` int(11) NOT NULL,
  `yellowgetfails` int(11) NOT NULL,
  `yellowremfails` int(11) NOT NULL,
  `maxtemp` int(11) NOT NULL,
  `maxrejects` int(11) NOT NULL,
  `maxdiscards` int(11) NOT NULL,
  `maxstales` int(11) NOT NULL,
  `maxgetfails` int(11) NOT NULL,
  `maxremfails` int(11) NOT NULL,
  `email` varchar(200) NOT NULL,
  `yellowfan` int(11) NOT NULL,
  `maxfan` int(11) NOT NULL,
  `yellowgessper` int(11) NOT NULL,
  `maxgessper` int(11) NOT NULL,
  `yellowavgmhper` int(11) NOT NULL,
  `maxavgmhper` int(11) NOT NULL
)".$table_props.";
";

$crr = $dbh->query($tblstr);
db_error();

$instblstr = "INSERT INTO `configuration` (`yellowtemp`, `yellowrejects`, `yellowdiscards`, `yellowstales`, `yellowgetfails`, `yellowremfails`, `maxtemp`, `maxrejects`, `maxdiscards`, `maxstales`, `maxgetfails`, `maxremfails`, `email`, `yellowfan`, `maxfan`, `yellowgessper`, `maxgessper`, `yellowavgmhper`, `maxavgmhper`) VALUES
(80, 1, 30, 7, 1, 1, 84, 2, 40, 10, 2, 2, 'change@me.com', 85, 90, 95, 90, 95, 90);";

$cri = $dbh->exec($instblstr);
db_error();

?>
