<?php
include ('config.php');
//-----------------------------------------------------------------
$connect->query("CREATE TABLE user (
id varchar(30) PRIMARY KEY,
limit_usertest int(100) NOT NULL ,
step varchar(5000) NOT NULL)");
//-----------------------------------------------------------------
$connect->query("CREATE TABLE admin (
id_admin int(100) NOT NULL )");
//-----------------------------------------------------------------
$connect->query("CREATE TABLE channels (
Channel_lock varchar(200) NOT NULL,
link varchar(200) NOT NULL )");
//-----------------------------------------------------------------
$connect->query("INSERT INTO admin (id_admin) VALUES ('$adminnumber')");
$connect->query("INSERT INTO channels (link,
Channel_lock) VALUES ('test','off')");

echo "<b>Table Created!</b>";
