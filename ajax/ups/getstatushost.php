<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <title>Windows Overview</title>
</head>
<body>
<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

$hostid = $_GET["hostid"];

foreach ($hostid as $hostID) {
	$params = array(
	"output" => array("name", "status"),
	"hostids" => $hostID
	);

	//call api
	$result = $zbx->call('host.get',$params);
	foreach ($result as $host) {
		$hostname = $host["name"];
		if ($host["status"] == 0) {
			$hoststatus = "UP";
			print '<div class="col-lg-3 col-xs-6">
	                        <!-- small box -->
	                        <div class="small-box bg-green">
	                          <div class="inner">
	                            <h3 align="center">'.
	                             $hoststatus.'
	                           </h3>
	                            <p align="center">'.$hostname.'</p>
	                          </div>
	                        </div>
	                      </div><!-- ./col -->';
		}
		else {
			$hoststatus = "DOWN";
			print '<div class="col-lg-3 col-xs-6">
	                        <!-- small box -->
	                        <div class="small-box bg-red">
	                          <div class="inner">
	                            <h3>'.
	                             $hoststatus.'
	                           </h3>
	                            <p>'.$hostname.'</p>
	                          </div>
	                        </div>
	                      </div><!-- ./col -->';
		}
	}
}

?>
</body>
</html>
					