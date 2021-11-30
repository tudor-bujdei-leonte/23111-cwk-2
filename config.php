<?php

require_once('config.inc.php');

// define('DB_SERVER', 'localhost');
// define('DB_USERNAME', 'root');
// define('DB_PASSWORD', '');
// define('DB_NAME', 'n98211tb');
 
/* Attempt to connect to MySQL database */
// $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
$link = mysqli_connect($database_host, $database_user, $database_pass, 'n98211tb');

// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

echo $link->error;
?>