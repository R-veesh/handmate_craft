<?php
$host ="localhost";
$user ="root";
$password= "";
$dbname = "test";

$con =mysqli_connect($host,$user,$password,$dbname);

if(!$con){

	echo mysqli_connect_error($con);
}
?> 