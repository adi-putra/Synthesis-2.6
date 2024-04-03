<?php
require_once "ZabbixApi.php";

use IntelliTrend\Zabbix\ZabbixApi;

date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

//store session's credentials
$zabUrl = 'http://localhost/synconfig';

if ($_GET["gen_report"] == 1 || $gen_report == 1) {
	$_SESSION['login_user'] = "Admin";
	$_SESSION['password_user'] = "SyntheNetwit14";
}

$zabUser = $_SESSION['login_user'];
$zabPassword = $_SESSION['password_user'];

$zbx = new ZabbixApi();

//if no session kept, head back to login page
if(!isset($_SESSION['login_user'])){
	header("location: login.php");
	exit;
}

//else determine and get the user type 
else {
	$zbx->login($zabUrl, $zabUser, $zabPassword);

	$params = array(
	"output" => array("userid", "username"),
	"search" => array("username" => $zabUser),
	"selectRole" => array("roleid", "type")
	);

	$result = $zbx->call('user.get',$params);
	foreach ($result as $user) {
		// foreach ($user["role"] as $role) {
		if ($user["username"] == $zabUser) {
			$zabUtype = $user["role"]["roleid"];
		}
		// }
	}

	//check for user's host group permission by hostgroup.get
	$access_windows = false;
	$access_linux = false;
	$access_firewall = false;
	$access_ups = false;
	$access_esx = false;
	$access_switches = false;
	$access_ilo = false;

	$params = array(
	"output" => array("name")
	);

	$result = $zbx->call('hostgroup.get',$params);

	foreach ($result as $hostgroup) {
		if (stripos($hostgroup["name"], 'Windows') !== false || stripos($hostgroup["name"], 'Windows') !== false) {
		 	$access_windows = true;
		}
		else if (stripos($hostgroup["name"], 'Linux') !== false) {
			$access_linux = true;
		}
		else if (stripos($hostgroup["name"], 'firewall') !== false || stripos($hostgroup["name"], 'fw') !== false) {
			$access_firewall = true;
		}
		else if (stripos($hostgroup["name"], 'ups') !== false) {
			$access_ups = true;
		}
		else if (stripos($hostgroup["name"], 'switches') !== false || stripos($hostgroup["name"], 'switch') !== false) {
			$access_switches = true;
		}
		else if (stripos($hostgroup["name"], 'esx') !== false || stripos($hostgroup["name"], 'esx') !== false) {
			$access_esx = true;
		}
		else if (stripos($hostgroup["name"], 'ilo') !== false) {
			$access_ilo = true;
		}
		else if (stripos($hostgroup["name"], 'imm') !== false) {
			$access_imm = true;
		}
	}
}

//LICENSING

//check license
//check file is encoded
// $encoded_check = json_encode(ioncube_file_is_encoded());

// //print $encoded_check;

// $license_check = json_encode(ioncube_license_has_expired());

// $license_prop = json_encode(ioncube_license_matches_server()); 

// if ($license_check == "true" || $encoded_check == "false" || $license_prop == "false") {
// 	header("location: license_check.php");
// 	//echo "<script>console.log('". $license_check ."' );</script>";
// }

include "function.php";
?>