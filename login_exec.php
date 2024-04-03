<?php
require_once("ZabbixApi.php");
use IntelliTrend\Zabbix\ZabbixApi;
session_start();

$zabUrl = 'http://localhost/synconfig';
$zabUser = $_GET['username'];
$zabPassword = $_GET['password'];
$gen_report = $_GET["gen_report"] ?? 0;
$timefrom = $_GET['timefrom'];
$timetill = $_GET["timetill"];

$zbx = new ZabbixApi();
try {
	//login credentials
	$zbx->login($zabUrl, $zabUser, $zabPassword);
	$_SESSION['login_user'] = $zabUser;
	$_SESSION['password_user'] = $zabPassword;

	if ($gen_report == 1) {
		header("location: report_generate.php");
	}
	else {
		header("location: dashboard.php");
	}

} catch (Exception $e) {
	print "==== Exception ===\n";
	print 'Errorcode: '.$e->getCode()."\n";
	print 'ErrorMessage: '.$e->getMessage()."\n";
	exit;
}
?>