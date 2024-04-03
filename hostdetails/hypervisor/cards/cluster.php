<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//$hostid = $_GET["hostid"] ?? array("10361", "10324");
$groupid = $_GET["groupid"];
$hostid = $_GET["hostid"];
$ip = $_GET["ip"];

$params = array(
    "output" => array("name","lastvalue"),
    "hostids" => $hostid,
    "filter" => array("name"=>"VMware: Cluster name"),
    // "searchByAny" => true
);

$result = $zbx->call('item.get',$params);

foreach ($result as $cluster) {
    
    $value = $cluster['lastvalue'];
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title></title>
</head>
<body>
	<?= $value; ?>
</body>
</html>
