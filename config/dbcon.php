<?php
//connection to mysql database

$host = "sql100.infinityfree.com";  //database host
$username = "if0_39543951";  //database user
$password = "nHquHLJU41m6PHD";    //database password
$database = "if0_39543951_cashapp";  //database name

$con = mysqli_connect("$host","$username","$password","$database");

if(!$con)
{
    echo 'error in connection';
}


