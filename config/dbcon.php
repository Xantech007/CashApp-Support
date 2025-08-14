<?php
//connection to mysql database

$host = "sql100.infinityfree.com";  //database host
$username = "if0_39709868";  //database user
$password = "yrMzeEbDs0n0dJu";    //database password
$database = "if0_39709868_pay2";  //database name

$con = mysqli_connect("$host","$username","$password","$database");

if(!$con)
{
    echo 'error in connection';
}


