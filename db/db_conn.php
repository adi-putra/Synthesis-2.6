<?php
$db_servername = "localhost";
$db_username = "zabbix";
$db_password = "P@55w0rd";
$db_name = "zabbix";

// Create connection
$conn = new mysqli($db_servername, $db_username, $db_password, $db_name);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
