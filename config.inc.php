<?php

$dbdatabase = "anubis_db";

/* MYSQL specific defines */
$dbusername = "anubis";
$dbpassword = "h3rakles";
$dbhost = "localhost";
/* End MYSQL specific defines */

$version = "2.3";

/* Set desired database interface to 1, others 0 */
$db_mysql = "1";
$db_sqlite = "";


/* Sockets time out, set shorter if you have mutiple rigs specified in anubis
   but turned off / disabled and page refreshes take a long time */

$socket_timeout = 3;






/*****************************************************************************/
$auto_inc = "";
$table_props = "";

function anubis_db_connect()
{
  global $db_mysql, $db_sqlite, $dbhost, $dbusername, $dbpassword, $dbdatabase;
  global $primary_key, $table_props, $show_tables;

  if ($db_mysql)
  {
    try
    {
      /*** connect to MySQL database ***/
      $dbh = new PDO("mysql:host=".$dbhost.";dbname=".$dbdatabase, $dbusername, $dbpassword);
    }
    catch(PDOException $e)
    {
      die ('FATAL: Cannot use Anubis_db !  ' . $e->getMessage());
    }

    $primary_key = "int(3) NOT NULL AUTO_INCREMENT PRIMARY KEY";
    $table_props = " ENGINE=MyISAM  DEFAULT CHARSET=latin1";
    $show_tables = 'SHOW TABLES';

  }
  else if ($db_sqlite)
  {
    try
    {
        /*** connect to SQLite database ***/
        $dbh = new PDO("sqlite:".$dbdatabase);
    }
    catch(PDOException $e)
    {
        die ('FATAL: Cannot use Anubis_db !  ' . $e->getMessage());
    }

    $primary_key = "INTEGER PRIMARY KEY";
    $show_tables = 'SELECT name FROM sqlite_master WHERE type = "table"';
  }


  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

  return $dbh;
}

function db_error()
{
  global $dbh;

  if ($dbh->errorCode() !== '00000')
  {
    $err_array = $dbh->errorInfo();
    die('FATAL: DB-Error: ' . $err_array[2]);

    return true;
  }
  return false;
}

?>
