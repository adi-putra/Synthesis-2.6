<?php
include($_SERVER['DOCUMENT_ROOT'] . "/synthesis/session.php");
include($_SERVER['DOCUMENT_ROOT'] . "/synthesis/db/db_conn.php");

//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);	

$hostid = $_GET["hostid"];
$groupid = $_GET["groupid"];

//time variables
$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? time();

//display time format
$diff = $timetill - $timefrom;

function secondsToTime($seconds)
{
  $dtF = new \DateTime('@0');
  $dtT = new \DateTime("@$seconds");
  //return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
  if ($seconds < 86400) {
	return $dtF->diff($dtT)->format('%h hours %i minutes');
  }
  else if ($seconds >= 86400) {
	return $dtF->diff($dtT)->format('%a days');
  }
}

function hostToLink($h_id, $g_id, $zbx) {
    $params = array(
        "output" => array("name"),
        "hostids" => $h_id,
        "groupids" => $g_id,
        "selectGroups" => array("name"),
    );
    //call api problem.get only to get eventid
    $hosttolink = $zbx->call('host.get',$params);
    foreach ($hosttolink as $htl) {
        $h_name = $htl["name"];
        foreach ($htl["groups"] as $htl_g) {
            if (stripos($htl_g["name"], 'linux') !== false || stripos($htl_g["name"], 'zabbix') !== false || stripos($htl_g["name"], 'sql') !== false || stripos($htl_g["name"], 'windows') !== false
            || stripos($htl_g["name"], 'esx') !== false || stripos($htl_g["name"], 'ilo') !== false || stripos($htl_g["name"], 'imm') !== false
            || stripos($htl_g["name"], 'switch') !== false || stripos($htl_g["name"], 'vm') !== false || stripos($htl_g["name"], '192.168') !== false
            || stripos($htl_g["name"], 'templates') !== false) {
                $g_name = $htl_g["name"];
            }
        }

        if (stripos($h_name, 'linux') !== false || stripos($g_name, 'linux') !== false || stripos($h_name, 'zabbix') !== false || stripos($g_name, 'zabbix') !== false) {
            $link = "hostdetails_linux.php?hostid=$h_id";
        }
        else if (stripos($h_name, 'firewall') !== false || stripos($g_name, 'firewall') !== false || stripos($h_name, 'fw') !== false || stripos($g_name, 'fw') !== false) {
            $link = "hostdetails_firewall.php?hostid=$h_id";
        }
        else if (stripos($h_name, 'esx') !== false || stripos($g_name, 'esx') !== false) {
            $link = "hostdetails_esx.php?hostid=$h_id";
        }
        else if (stripos($h_name, 'ups') !== false || stripos($g_name, 'ups') !== false) {
            $link = "hostdetails_ups.php?hostid=$h_id";
        }
        else if (stripos($h_name, 'switches') !== false || stripos($g_name, 'switches') !== false) {
            $link = "hostdetails_switches.php?hostid=$h_id";
        }
        else if (stripos($h_name, 'ilo') !== false || stripos($g_name, 'ilo') !== false) {
            $link = "hostdetails_ilo.php?hostid=$h_id";
        }
        else if (stripos($h_name, 'imm') !== false || stripos($g_name, 'imm') !== false) {
            $link = "hostdetails_imm.php?hostid=$h_id";
        }
        else if (stripos($h_name, 'vcenter') !== false || stripos($g_name, '192.168') !== false || stripos($h_name, 'vm') !== false || stripos($g_name, 'templates') !== false) {
            $link = "hostdetails_vm.php?hostid=$h_id";
        }
        else if (stripos($h_name, 'windows') !== false || stripos($g_name, 'windows') !== false || stripos($h_name, 'sql') !== false || stripos($g_name, 'sql') !== false){
            $link = "hostdetails_windows.php?hostid=$h_id";
        }
        else {
            $link = "#$g_name";
        }

        return $link;
    }
}

$hostArr = "";
$groupArr = "";

//if both arrays empty, let it empty
if (!empty($hostid)) {
    foreach ($hostid as $hostID) {
        $hostArr .= "hostid[]=".$hostID."&";
    }
}

if (!empty($groupid)) {
    foreach ($groupid as $groupID) {
        $groupArr .= "groupid[]=".$groupID."&";
    }
}

$timerange = "&timefrom=".$timefrom."&timetill=".$timetill;

//link for report problems page
$report_link = "report_problems.php?".$groupArr.$hostArr.$timerange;
?>
<html>
    <head>
        <style>
			.popover{
				width: auto;
				height:auto;   
				max-width:none; 
				max-height:none; 
			}
			.popover-content {
				font-size: 11px;
			}
			.example-modal .modal {
				position: relative;
				top: 250px;
				bottom: auto;
				right: auto;
				left: 150px;
				display: block;
				z-index: 1;
			}
			.example-modal .modal {
				background: transparent!important;
			}
		</style>
    </head>
    <body>
        <a href='<?php echo $report_link; ?>' target="_blank"><button class="btn btn-default" style="float: right;"><i class='fa fa-mail-forward'></i>&nbsp;&nbsp;View Report</button></a>
        <br><br><br>
        <table id="problemstable" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
            <th></th>
            <th>Details</th>
            <th>Problem Time</th>
            <th>Host</th>
            <th>Problem</th>
            <th>Severity</th>
            <th>Severity Tag</th>
            <th>Acknow.</th>
            <th>Status</th>
            <th class="none">Duration</th>
            <th class="none">Message</th>
            <th class="none">Description</th>
            <th class="none">Action</th>
            </tr>
        </thead>
        <tbody>
    <?php

    //$sql = "SELECT * FROM problem WHERE r_eventid IS NULL ORDER BY clock DESC LIMIT 100"; 
    $sql = "SELECT * FROM problem WHERE clock >= $timefrom AND clock <= $timetill AND r_eventid IS NULL";
    //$sql = "SELECT * FROM events WHERE eventid=49259456";
    //$sql = "SELECT * FROM problem LIMIT 600";

    $problem_result = $conn->query($sql);

    // problem output
    $problem_arr = array();

    //count id
    $id = 0;

    //count severity
    $count_all_sev = 0;
    $count_unclass_sev = 0;
    $count_info_sev = 0;
    $count_warning_sev = 0;
    $count_average_sev = 0;
    $count_high_sev = 0;
    $count_disaster_sev = 0;

    while($problem = $problem_result->fetch_assoc()) {

        //problems
        $name = $problem['name'];
        $acknowledged = $problem['acknowledged'];
        $eventid = $problem['eventid'];
        $severity = $problem['severity'];
        $objectid = $problem['objectid'];
        $clock = $problem['clock'];

        //acknowledges
        $sql = "SELECT * FROM acknowledges WHERE eventid='$eventid'";
        $ack_results = $conn->query($sql);
        $ack_total = mysqli_num_rows($ack_results);

        //triggers
        $sql = "SELECT comments FROM triggers WHERE triggerid='$objectid'";
        $trigger_results = $conn->query($sql);

        while($trigger = $trigger_results->fetch_assoc()) {
            $description = $trigger['comments'];
        }

        //alerts
        $sql = "SELECT message FROM alerts WHERE eventid='$eventid'";
        $alert_results = $conn->query($sql);
        if (empty($alert_results)) {
            $message = "";
        }
        else {
            while($alert = $alert_results->fetch_assoc()) {
                $message_str = $alert['message'];
                $startpoint_str = stripos($message_str, "operational data");
                $endpoint_str = stripos($message_str, "original problem");
                $message = substr($message_str, $startpoint_str, ($endpoint_str - $startpoint_str));
            }
        }

        //functions
        $sql = "SELECT functionid, itemid, triggerid FROM functions WHERE triggerid='$objectid'";
        $func_results = $conn->query($sql);

        while($func = $func_results->fetch_assoc()) {
            $itemid = $func['itemid'];
        }

        //hosts
        $sql = "SELECT hostid FROM items WHERE itemid='$itemid'";
        $item_results = $conn->query($sql);

        while($item = $item_results->fetch_assoc()) {
            if (!empty($hostid)) {
                if (in_array($item["hostid"], $hostid)) {
                    $gethostid = $item['hostid'];
                }
                else {
                    continue 2;
                }
            }
            else {
                $gethostid = $item['hostid'];
            }
        }

        //print $gethostid;


        //hosts
        $sql = "SELECT name FROM hosts WHERE hostid='$gethostid'";
        $host_results = $conn->query($sql);

        while($host = $host_results->fetch_assoc()) {
            $gethostname = $host['name'];
        }

        //hosts_groups
        if (!empty($groupid)) {
            $sql = "SELECT groupid FROM hosts_groups WHERE hostid='$gethostid'";
            $group_results = $conn->query($sql);
            while($group = $group_results->fetch_assoc()) {
                $getgroupid_arr[] = $group["groupid"];
            } 

            $count_intersect = count(array_intersect($getgroupid_arr, $groupid));
            if ($count_intersect == 0) {
                continue;
            }
            else {
                //print $count_intersect."<br>";
                $groupid_intersect = array_intersect($getgroupid_arr, $groupid);
                $getgroupid = $groupid_intersect[0];
                $getgroupid_arr = array();
            }
        }
        else {
            $sql = "SELECT groupid FROM hosts_groups WHERE hostid='$gethostid'";
            $group_results = $conn->query($sql);
            while($group = $group_results->fetch_assoc()) {
                $getgroupid = $group['groupid'];
            }
        }

        //time format
        $clock1 = date("Y-m-d\ H:i:s A\ ", $clock);

        //print info button
        print "<tr>

        <td></td>

        <td><button class='btn bg-default' style='width: 100%;'><i class='fa fa-info-circle'></i></button></td>";

        print "<td>$clock1</td>";

        //print hostname
        $hostlink = hostToLink($gethostid, $getgroupid, $zbx);
        print "<td><a href='".$hostlink."'>$gethostname</a></td>";

        //print json_encode($problem_arr);

        print "<td>$name</td>";

        //severity
        $severity_tag = "";
        print "<td style='text-align: center; padding: 0;'>";
        if ($severity == 1) {
            //count severity
            $count_info_sev++;
            $count_all_sev++;
            print "<button class='btn btn-info margin' style='width: 100px; margin: 0px;'>Info</button>";
            $severity_tag = "%Info%";
        }
        else if ($severity == 2) {
            //count severity
            $count_warning_sev++;
            $count_all_sev++;
            print "<button class='btn bg-yellow margin' style='width: 100px; margin: 0px;'>Warning</button>";
            $severity_tag = "%Warning%";
        }
        else if ($severity == 3) {
            //count severity
            $count_average_sev++;
            $count_all_sev++;
            print "<button class='btn bg-orange margin' style='width: 100px; margin: 0px;'>Average</button>";
            $severity_tag = "%Average%";
        }
        else if ($severity == 4) {
            //count severity
            $count_high_sev++;
            $count_all_sev++;
            print "<button class='btn bg-red margin' style='width: 100px; margin: 0px;'>High</button>";
            $severity_tag = "%High%";
        }
        else if ($severity == 5) {
            //count severity
            $count_disaster_sev++;
            $count_all_sev++;
            print "<button class='btn bg-red margin' style='width: 100px; margin: 0px;'>Danger</button>";
            $severity_tag = "%Danger%";
        }
        else {
            //count severity
            $count_unclass_sev++;
            $count_all_sev++;
            print "<button class='btn bg-gray margin' style='width: 100px; margin: 0px;'>Unclassified</button>";
            $severity_tag = "%Unclassified%";
        }
        print "</td>";

        //severity tag
        print "<td>$severity_tag</td>";

        //acknowledge
        $sql = "SELECT * FROM acknowledges WHERE eventid='$eventid'";
        $alert_results = $conn->query($sql);
        if (empty($alert_results)) {
            $ack_data = "";
			$count_ack= 0;
        }
        else {
            //start count_ack
            $count_ack = 0;
            //declare ack_data
            $ack_data = "";
            while($ack = $ack_results->fetch_assoc()) {
                //start with tr
                $ack_data .= "<tr>";

                //get ack values
                $ack_user = $ack["userid"];
                $ack_clock = $ack["clock"];
                $ack_message = $ack["message"];

                $sql = "SELECT alias FROM users WHERE userid='$ack_user'";
                $user_results = $conn->query($sql);
                if (empty($user_results)) {
                    $user_alias = "Inaccessible User";
                }
                else {
                    while($user = $user_results->fetch_assoc()) {
                        $user_alias = $user["alias"];
                    }
                }

                //get time
                $ack_clock = date("Y-m-d\ H:i:s A\ ", $ack_clock);

                //insert ack data
                $ack_data .= "<td>$ack_clock</td>
                            <td>$user_alias</td>
                            <td>$ack_message</td>";
                
                //ends with /tr
                $ack_data .= "</tr>";

                //add +1 count_ack
                $count_ack++;
            }
        }

			//declare table popover with ack data
			$tableTooltip = "<table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                $ack_data
            </tbody>
        </table>";

        print "<td style='text-align: center; padding: 0;'>";

        //determine acknowledge or not, and place ack data for popover
        if ($acknowledged == 1) {
            print "<button type='button' class='btn btn-success margin' data-placement='left' data-toggle='popover' data-content='".$tableTooltip."' style='margin: 0px;'><i class='fas fa-thumbs-up'></i> &nbsp;<span class='label label-success'>".$ack_total."</span></button>";
        }
        else {
            print "<button type='button' class='btn btn-danger margin' data-placement='left' data-toggle='popover' data-content='".$tableTooltip."' style='margin: 0px;'><i class='fas fa-thumbs-down'></i> &nbsp;<span class='label label-danger'>".$ack_total."</span></button>";
        }

        print "</td>";

        //for hidden column
        if ($acknowledged == 1) {
            print "<td>green</td>";
        }
        else {
            print "<td>red</td>";
        }

        //problem duration
        $time_diff = time() - $clock;
        $duration = secondsToTime($time_diff);
        print "<td>$duration</td>";

        //problem message
        print "<td>$message</td>";

        //problem description
        print "<td>$description</td>";

        //ack button (Save for later)
        //print "<td><!-- Trigger the modal with a button -->
        //<button type='button' onclick='openForm($eventid)' class='btn bg-yellow margin' style='margin: 0px;' data-toggle='tooltip' title='Acknowledge Problem'><i class='fa fa-comment'></i></button></td>";

        //ack 2 button
        //button to open form for unacknowledge problems

        print "<td>";

        if ($acknowledged > 1) {
            print '<button onclick="loadModalAckForm('.$eventid.",".$gethostid.')" type="button" class="btn btn-warning margin" data-toggle="modal" data-target="#ackModal" data-toggle="tooltip" data-placement="right" title="Acknowledge Problem"><i class="fa fa-comment"></i></button></button>';
        }
        else {
            print '<button onclick="loadModalAckForm('.$eventid.",".$gethostid.')" type="button" class="btn btn-warning margin" data-toggle="modal" data-target="#ackModal" data-toggle="tooltip" data-placement="right" title="Acknowledge Problem"><i class="fa fa-comment"></i></button></button>';
        }
        
        print '<button onclick="loadTimelineChart('.$objectid.', '.$gethostid.')" type="button" class="btn bg-green margin" data-toggle="modal" data-target="#timelineModal" data-toggle="tooltip" data-placement="right" title="View Timeline"><i class="fas fa-history"></i></button></button>';

        print "</td>";
        //end table
        print "</tr>";

        $id = $id + 1;
        
    } 
    ?>
            </tbody>
        </table>
    </body>
</html>

<!-- Modal to load form -->
<div class="modal fade" id="ackModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="example-modal">
        <div class="modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" id="closeackModal" class="close" onclick="startIntProblemsTable()" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Acknowledge Problem&nbsp;&nbsp;<i class="far fa-clipboard"></i></h4>
                    </div>
                    <div id="ack_form">
                        <!-- Loading Gif -->
                        <div class="overlay">
                            <i class="fa fa-refresh fa-spin"></i>
                        </div>
                    </div>
                    <script>
                    //function to load form w param eventid n hostid
                    function loadModalAckForm(eventid, hostid) {
                        //stopIntProblemsTable();
                        $("#ack_form").load("/synthesis/problems/ack_form.php?eventid=" + eventid + "&hostid=" + hostid);
                    }
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Timeline Modal -->
<div class="modal fade" id="timelineModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="example-modal">
        <div class="modal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" id="closeackModal" class="close" onclick="startIntProblemsTable()" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Problem's Timeline&nbsp;&nbsp;
                            <i class="fas fa-history"></i> 
                        </h4>
                    </div>
                    <div id="timeline_chart">
                        <!-- Loading Gif -->
                        <div class="overlay">
                            <i class="fa fa-refresh fa-spin"></i>
                        </div>
                    </div>
                    <script>
                    //function to load form w param eventid n hostid
                    function loadTimelineChart(objectid, hostid) {
                        //stopIntProblemsTable();
                        $("#timeline_chart").load("/synthesis/problems/timeline_chart.php?objectid=" + objectid + "&hostid=" + hostid);
                    }
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
//declare count sevs
var count_all_sev = '<?php echo $count_all_sev;?>';
var count_unclass_sev = '<?php echo $count_unclass_sev;?>';
var count_info_sev = '<?php echo $count_info_sev;?>';
var count_warning_sev = '<?php echo $count_warning_sev;?>';
var count_average_sev = '<?php echo $count_average_sev;?>';
var count_high_sev = '<?php echo $count_high_sev;?>';
var count_disaster_sev = '<?php echo $count_disaster_sev;?>';

//write to div
$('#count_all').html(count_all_sev);
$('#count_unclass').html(count_unclass_sev);
$('#count_info').html(count_info_sev);
$('#count_warning').html(count_warning_sev);
$('#count_average').html(count_average_sev);
$('#count_high').html(count_high_sev);
$('#count_disaster').html(count_disaster_sev);


//table settings
//table settings
var table = $('#problemstable').DataTable({
    responsive: {
        details: {
            type: 'column',
            target: 1
        }
    },
    columnDefs: [ {
        targets: 0,
        checkboxes: {
          selectRow: true
      }
    }, {
        targets: [ 6, 8 ],
        visible: false
    } ],
    select: {
        style: 'multi'
    },
    order: [ 1, 'desc' ]
} );

//popover enabled
table.$('[data-toggle="popover"]').popover({
        trigger: 'hover',
        html: true
    })  

//for prob count buttons
function searchTableUnclassified() {
    table.search("%Unclassified%").draw();
}
function searchTableInfo() {
    table.search("%Info%").draw();
}
function searchTableWarning() {
    table.search("%Warning%").draw();
}
function searchTableAverage() {
    table.search("%Average%").draw();
}
function searchTableHigh() {
    table.search("%High%").draw();
}
function searchTableDisaster() {
    table.search("%Disaster%").draw();
}

</script>