<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

$groupid = $_GET["groupid"];

$params = array(
"output" => array("name"),
"groupids" => $groupid
);
//call api method
$result = $zbx->call('hostgroup.get', $params);

foreach ($result as $group) {
    echo $group["name"];
}
?>