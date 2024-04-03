<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

$hostid = $_GET["hostid"];
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title>Windows Overview</title>
</head>

<body>
	<div class="row">
		<div class="col-md-12">
			<!-- CPU Util table -->
			<table id="nodestable" class="table table-bordered table-striped">
				<caption><i>Updated since: <?php echo date("d/m/y h:i A", time()); ?></i></caption>
				<thead>
					<tr>
						<th>Node Name</th>
						<th>Cannot Take Over Cause</th>
                        <th>HA Interconnect Status</th>
                        <th>HA Partner Name</th>
						<th>HA Partner Status</th>
                        <th>HA Settings</th>
                        <th>HA State</th>
					</tr>
				</thead>
				<tbody>
					<?php

                    $params = array(
                        "output" => array("name"),
                        "hostids" => $hostid
                    );
                    //call api
                    $result = $zbx->call('host.get', $params);
                    foreach ($result as $row) {
                        $gethostname = $row["name"];
                    }

                    // Get Nodes Name
                    $node_name = [];
                    $params = array(
                        "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay"),
                        "hostids" => $hostid,
                        "search" => array("key_" => array("haState")),
                        "searchByAny" => true
                    );
                    //call api problem.get only to get eventid
                    $result = $zbx->call('item.get',$params);
                    foreach ($result as $item) {
                        $item_key = substr($item["key_"], 8);
                        $node_name[] = substr($item_key, 0, -1);
                    }

                    // Loop through all nodes
                    for ($i=0; $i < count($node_name); $i++) { 

                        // take over cause
                        $params = array(
                            "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay"),
                            "hostids" => $hostid,
                            "search" => array("key_" => array("haCannotTakeoverCause[".$node_name[$i]."]")),
                            "searchByAny" => true
                        );
                        //call api problem.get only to get eventid
                        $result = $zbx->call('item.get',$params);
                        if (!empty($result)) {
                            foreach ($result as $item) {
                                $takeover_value = $item["lastvalue"];
                            }
                        }
                        else {
                            $takeover_value = "No data";
                        }

                        // interconnect status
                        $params = array(
                            "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay"),
                            "hostids" => $hostid,
                            "search" => array("key_" => array("haInterconnectStatus[".$node_name[$i]."]")),
                            "searchByAny" => true
                        );
                        //call api problem.get only to get eventid
                        $result = $zbx->call('item.get',$params);
                        if (!empty($result)) {
                            foreach ($result as $item) {
                                $interconnstats_value = $item["lastvalue"];
                            }
                        }
                        else {
                            $interconnstats_value = "No data";
                        }

                        // partner name
                        $params = array(
                            "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay"),
                            "hostids" => $hostid,
                            "search" => array("key_" => array("haPartnerName[".$node_name[$i]."]")),
                            "searchByAny" => true
                        );
                        //call api problem.get only to get eventid
                        $result = $zbx->call('item.get',$params);
                        if (!empty($result)) {
                            foreach ($result as $item) {
                                $partnername_value = $item["lastvalue"];
                            }
                        }
                        else {
                            $partnername_value = "No data";
                        }

                        // partner status
                        $params = array(
                            "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay"),
                            "hostids" => $hostid,
                            "search" => array("key_" => array("haPartnerStatus[".$node_name[$i]."]")),
                            "searchByAny" => true
                        );
                        //call api problem.get only to get eventid
                        $result = $zbx->call('item.get',$params);
                        if (!empty($result)) {
                            foreach ($result as $item) {
                                $partnerstats_value = $item["lastvalue"];
                            }
                        }
                        else {
                            $partnerstats_value = "No data";
                        }

                        // ha settings
                        $params = array(
                            "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay"),
                            "hostids" => $hostid,
                            "search" => array("key_" => array("haSettings[".$node_name[$i]."]")),
                            "searchByAny" => true
                        );
                        //call api problem.get only to get eventid
                        $result = $zbx->call('item.get',$params);
                        if (!empty($result)) {
                            foreach ($result as $item) {
                                $setting_value = $item["lastvalue"];
                            }
                        }
                        else {
                            $setting_value = "No data";
                        }

                        // ha state
                        $params = array(
                            "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay"),
                            "hostids" => $hostid,
                            "search" => array("key_" => array("haState[".$node_name[$i]."]")),
                            "searchByAny" => true
                        );
                        //call api problem.get only to get eventid
                        $result = $zbx->call('item.get',$params);
                        if (!empty($result)) {
                            foreach ($result as $item) {
                                $state_value = $item["lastvalue"];
                            }
                        }
                        else {
                            $state_value = "No data";
                        }

                        //mapping

                        //ha takeover
                        if ($takeover_value == 1) {
                            $takeover_value = '<button class="btn btn-block btn-success">OK</button>';
                        }
                        else if ($takeover_value == 2) {
                            $takeover_value = '<button class="btn btn-block btn-default">Unknown</button>';
                        }
                        else if ($takeover_value == 3) {
                            $takeover_value = '<button class="btn btn-block btn-danger">Disabled By Operator</button>';
                        }
                        else if ($takeover_value == 4) {
                            $takeover_value = '<button class="btn btn-block btn-danger">Interconnect Offline</button>';
                        }
                        else if ($takeover_value == 5) {
                            $takeover_value = '<button class="btn btn-block btn-danger">Disabled By Partner</button>';
                        }
                        else if ($takeover_value == 6) {
                            $takeover_value = '<button class="btn btn-block btn-danger">Take Over Failed</button>';
                        }

                        // interconnect status
                        if ($interconnstats_value == 1) {
                            $interconnstats_value = '<button class="btn btn-block btn-danger">Not Present</button>';
                        }
                        else if ($interconnstats_value == 2) {
                            $interconnstats_value = '<button class="btn btn-block btn-danger">Down</button>';
                        }
                        else if ($interconnstats_value == 3) {
                            $interconnstats_value = '<button class="btn btn-block btn-warning">Partial Failure</button>';
                        }
                        else if ($interconnstats_value == 4) {
                            $interconnstats_value = '<button class="btn btn-block btn-success">Up</button>';
                        }

                        // partner status
                        if ($partnerstats_value == 1) {
                            $partnerstats_value = '<button class="btn btn-block btn-warning">Maybe Down</button>';
                        }
                        else if ($partnerstats_value == 2) {
                            $partnerstats_value = '<button class="btn btn-block btn-success">OK</button>';
                        }
                        else if ($partnerstats_value == 3) {
                            $partnerstats_value = '<button class="btn btn-block btn-danger">Dead</button>';
                        }
                        
                        // settings 
                        if ($setting_value == 1) {
                            $setting_value = '<button class="btn btn-block btn-danger">Not Configured</button>';
                        }
                        else if ($setting_value == 2) {
                            $setting_value = '<button class="btn btn-block btn-success">Enabled</button>';
                        }
                        else if ($setting_value == 3) {
                            $setting_value = '<button class="btn btn-block btn-danger">Disabled</button>';
                        }
                        else if ($setting_value == 4) {
                            $setting_value = '<button class="btn btn-block btn-danger">Take Over By Partner Disabled</button>';
                        }
                        else if ($setting_value == 5) {
                            $setting_value = '<button class="btn btn-block btn-danger">Node is Dead</button>';
                        }

                        // state 
                        if ($state_value == 1) {
                            $state_value = '<button class="btn btn-block btn-danger">Dead</button>';
                        }
                        else if ($state_value == 2) {
                            $state_value = '<button class="btn btn-block btn-success">Can Takeover</button>';
                        }
                        else if ($state_value == 3) {
                            $state_value = '<button class="btn btn-block btn-danger">Cannot Takeover</button>';
                        }
                        else if ($state_value == 4) {
                            $state_value = '<button class="btn btn-block btn-success">Takeover</button>';
                        }
                        else if ($state_value == 5) {
                            $state_value = '<button class="btn btn-block btn-warning">Partial Giveback</button>';
                        }
                        

                        print "<tr>";
                        print "<td>".$node_name[$i]."</td>";
                        print "<td>".$takeover_value."</td>";
                        print "<td>".$interconnstats_value."</td>";
                        print "<td>".$partnername_value."</td>";
                        print "<td>".$partnerstats_value."</td>";
                        print "<td>".$setting_value."</td>";
                        print "<td>".$state_value."</td>";
                        print "</tr>";
                    }
					?>
				</tbody>
			</table>
		</div>
	</div>
</body>

</html>

<script>
	$(function() {
		$('#nodestable').DataTable({
			// "order": [
			// 	[2, "desc"]
			// ],
			"scrollY": "400px",
			scrollCollapse: true,
			"paging": true
		});
	});
</script>