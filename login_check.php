<?php
//Author : Adiputra

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

require_once "ZabbixApi.php";

use IntelliTrend\Zabbix\ZabbixApi;

$username = $_POST["username"];
$password = $_POST["password"];

$zbx = new ZabbixApi();
try {
	$zbx->login('http://localhost/synconfig', $username, $password);
	$result = $zbx->call('host.get', array("countOutput" => true));
	print "Successfully login";
} catch (Exception $e) {
	print "==== Exception ===\n";
	print 'Errorcode: '.$e->getCode()."\n";
	print 'ErrorMessage: '.$e->getMessage()."\n";
	exit;
}
?>