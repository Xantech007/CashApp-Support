<?php
//connection to mysql database

$host = "localhost";  //database host
$username = "dolltkvu_user1";  //database user
$password = "Xander24427279";    //database password
$database = "dolltkvu_db";  //database name

$con = mysqli_connect("$host","$username","$password","$database");

if(!$con)
{
    echo 'error in connection';
}


