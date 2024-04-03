<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
include($_SERVER['DOCUMENT_ROOT'] . "/synthesis/db/db_conn.php");

$hostid = $_GET["hostid"];
$groupid = $_GET["groupid"];

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

//RESOLVED PROBLEMS PIVOT
$resolved_data = array(); //store pivot JSON
$resolved_count = 0;

$params = array(
    "output" => array("eventid", "r_eventid", "severity", "acknowledged", "name", "clock", "value", "userid"),
    "hostids" => $hostid,
    "groupids" => $groupid,
    "select_acknowledges" => array("userid", "action", "clock", "message", "old_severity", "new_severity"),
    "time_from" => $timefrom,
    "time_till" => $timetill,
    "selectHosts" => array("hostid", "name")
);
//call api problem.get only to get eventid
$result = $zbx->call('event.get',$params);
//count id
$id = 1;


foreach ($result as $v) {

    //get values
    $name = $v['name'];
    $severity = $v['severity'];
    $acknowledged = $v['acknowledged'];
    $eventid = $v['eventid'];
    $r_eventid = $v["r_eventid"];
    $status_value = $v["value"];
    $userid = $v["userid"];

    //declare indicator for closed problem
    $problem_isClosed = "";

    //get hostid and hostname for the event
    foreach ($v["hosts"] as $host) {
        $gethostid = $host["hostid"];
        $gethostname = $host["name"];
    }

    //time config
    $datetime = date("Y-m-d", $v["clock"]);
    $date = date("Y-m-d", $v["clock"]);
    $time = date("H:i:s", $v["clock"]);
    $time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $time);
    sscanf($time, "%d:%d:%d", $hours, $minutes, $seconds);
    $time = $hours * 3600 + $minutes * 60 + $seconds;
    $v["Hour"] = date("H", $v["clock"]);
    $hour = $v["Hour"];
    $v["Minute"] = date("i", $v["clock"]);
    $minute = $v["Minute"];
    $v["Second"] = date("s", $v["clock"]);
    $second = $v["Second"];

    //call db to check ack records
    $ack = array();
    $sql = "SELECT * FROM ack WHERE eventid='$eventid' ORDER BY ack_date DESC";
    $db_result = $conn->query($sql);
    $ack_data = "";
    $count_ack = 0;
    if ($db_result->num_rows > 0) {
        while($db_row = $db_result->fetch_assoc()) {
            if ($count_ack == 0) {
                if ($db_row["ack_close"] == 1) {
                    continue 2;
                }
                $acknowledged = $db_row["ack_status"];
            }
            
            //check if db message is too long
            if (strlen($db_row["ack_message"]) > 50) {
                $db_row["ack_message"] = substr($db_row["ack_message"], 0, 50)."...";
            }
            $count_ack++;
        }
    }
    else {
        //if no ack records, set all by default
        $acknowledged = 0;
        $ack_data = "";
        $count_ack = 0;
    }

    //continue if problem is closed by user
    if ($problem_isClosed == 1) {
        continue;
    }

    //time format
    $clock = $v["clock"];
    $clock1 = date("Y-m-d\ H:i:s A\ ", $clock);

    //get recovery time by r_eventid
    //determine status_value
    if ($r_eventid != 0) {
        $params = array(
            "output" => array("clock"),
            "eventids" => $r_eventid
        );
        $recovery_result = $zbx->call('event.get', $params);
        foreach ($recovery_result as $recovery) {
            //$recovery['r_clock'] = $recovery['r_clock'] * 1000;
            $recovery_time = date("Y-m-d\ H:i:s A\ ", $recovery['clock']);
            $status_value = 0;
            //echo $recovery_time;
        }
    }
    else {
        //check if the problem is closed by user
        if ($userid != 0) {
            //get user name
            $params = array(
                "output" => array("alias"),
                "userids" => $userid
            );
            $user_result = $zbx->call('user.get', $params);
            foreach ($user_result as $user) {
                $userAlias = $user["alias"];
                $recovery_time = "";
                $status_value = 2;
            }
        }
        else {
            continue;
        }
    }	

    //determine status
    if ($status_value == 0) {
        $status_value = "RESOLVED";
    }
    else if ($status_value == 2) {
        continue;
    }

    //get description of problem
    $params = array(
        "output" => array("comments"),
        "search" => array("description" => $name)
    );
    //call api problem.get only to get eventid
    $trigger_result = $zbx->call('trigger.get',$params);
    foreach ($trigger_result as $trigger) {
        $description = $trigger["comments"];
    }

    //get alert message for the problem
    $params = array(
        "output" => array("message"),
        "eventids" => $eventid
    );
    //call api problem.get only to get eventid
    $result = $zbx->call('alert.get',$params);

    foreach ($result as $alert) {
        $message_str = $alert["message"];
        //capture operational data value only
        $startpoint_str = stripos($message_str, "operational data");
        $endpoint_str = stripos($message_str, "original problem");
        $message = substr($message_str, $startpoint_str, ($endpoint_str - $startpoint_str));
        //$message = substr($message_str, 0, -$endpoint_str);
        //$message = rtrim($message, "Original");
    }

    //severity
    if ($severity == 1) {
        //count severity
        $count_info_sev++;
        $severity = "Info";
    }
    else if ($severity == 2) {
        //count severity
        $count_warning_sev++;
        $severity = "Warning";
    }
    else if ($severity == 3) {
        //count severity
        $count_average_sev++;
        $severity = "Average";
    }
    else if ($severity == 4) {
        //count severity
        $count_high_sev++;
        $severity = "High";
    }
    else if ($severity == 5) {
        //count severity
        $count_disaster_sev++;
        $severity = "Disaster";
    }
    else {
        //count severity
        $count_unclass_sev++;
        $severity = "Unclassified";
    }

    //determine acknowledge or not, and place ack data for popover
    if ($acknowledged == 1) {
        $acknowledged = "Yes";
    }
    else {
        $acknowledged = "No";
    }

    //problem duration
    $time_diff = time() - $clock;
    $duration = secondsToTime($time_diff);

    //ack button (Save for later)
    //print "<td><!-- Trigger the modal with a button -->
    //<button type='button' onclick='openForm($eventid)' class='btn bg-yellow margin' style='margin: 0px;' data-toggle='tooltip' title='Acknowledge Problem'><i class='fa fa-comment'></i></button></td>";

    $resolved_data[$resolved_count] = array(
        "Event_ID" => "$eventid",
        "Problem_Date" => "$datetime",
        "Date" => $date,
        "Time" => $time,
        "Hour" => $hour,
        "Minute" => $minute,
        "Second" => $second,
        "Time" => "$time",
        "Recovery_Time" => "$recovery_time",
        "Host" => "$gethostname",
        "Problem" => "$name",
        "Severity" => "$severity",
        "Status" => "$status_value",
        "Acknowledged" => "$acknowledged"
    );

    $resolved_count++;
}	

//convert to json and trim first and last character
$json_resolved = json_encode($resolved_data);
$json_resolved = substr($json_resolved, 0, -1);
$json_resolved = substr($json_resolved, 1);

//echo $json_resolved;
?>
<html>
	<head>
    </head>
    <body>
        <div class="row">
            <div class="col-xs-12">
                <div id="resolved-report"></div>
            </div>
        </div>
    </body>
</html>

<!-- Pivot script -->
<script type="text/javascript">
    var timefrom = '<?php echo date('d M Y H:i', $timefrom) ?>';
    var timetill = '<?php echo date('d M Y H:i', $timetill) ?>';
    var timerange = timefrom + " - " + timetill;
    //var pivotTitle1 = "Problems Report " + "(" + timerange + ")";
    var pivotTitle = "Resolved Problems Report " + "(" + timerange + ")";

    var reportpivot = $("#resolved-report").flexmonster({
        componentFolder: "/synthesis/plugins/flexmonster/",
        licenseKey: "Z7XF-XHJB30-3F3313-0L6T1D-343J5E-6G5L4B-362H3M-2U364P-362P37",
        toolbar: true,
        beforetoolbarcreated: customizePivotToolbar,
        global: {
            localization: {
            "grid": {
                "blankMember": "No data",
                "dateInvalidCaption": ""
            }
            }
        },
        report: {
            dataSource: {
                //DATA TYPES
                data: [ 
                {   
                    "Event_ID": {
                        type: "id"
                    },
                    "Problem_Date": {
                        type: "date string"
                    },
                    "Date": {
                        type: "date"
                    },
                    "Time": {
                        type: "time"
                    },
                    "Recovery_Time": {
                        type: "datetime"
                    },
                    "Hour": {
                        type: "hour"
                    },
                    "Minute": {
                        type: "minute"
                    },
                    "Second": {
                        type: "second"
                    },
                    "Host": {
                        type: "string"
                    },
                    "Problem": {
                        type: "string"
                    },
                    "Severity": {
                        type: "string"
                    },
                    "Acknowledged": {
                        type: "string"
                    },
                    "Status": {
                        type: "string"
                    }
                }, <?php echo $json_resolved; ?>] //php pivot data
            },
            //SLICE, ROWS AND COLUMNS
            "slice": {
                "reportFilters": [
                    {
                        "uniqueName": "Acknowledged"
                    },
                    {
                        "uniqueName": "Date.Year"
                    },
                    {
                        "uniqueName": "Date.Month"
                    },
                    {
                        "uniqueName": "Date.Day"
                    },
                    {
                        "uniqueName": "Hour"
                    },
                    {
                        "uniqueName": "Minute"
                    },
                    {
                        "uniqueName": "Second"
                    },
                    {
                        "uniqueName": "Recovery_Time"
                    },
                ],
                "rows": [
                    {
                        "uniqueName": "Event_ID"
                    },
                    {
                        "uniqueName": "Problem_Date"
                    },
                    {
                        "uniqueName": "Time"
                    },
                    {
                        "uniqueName": "Host"
                    },
                    {
                        "uniqueName": "Problem"
                    },
                    {
                        "uniqueName": "Severity"
                    },
                    {
                        "uniqueName": "Status"
                    }
                ],
                measures: [{
                    uniqueName: "Problem",
                    aggregation: "count"
                }]
            },
            //OPTIONS
            "options": {
                "grid": {
                    "title": pivotTitle,
                    "type": "classic",
                    "showTotals": "columns"
                }
            }, //CONDITIONS
            "conditions": [{
                "formula": "#value > 0",
                "measure": "Problem",
                "format": {
                    "backgroundColor": "#0598df",
                    "color": "#FFFFFF",
                    "fontFamily": "Arial",
                    "fontSize": "12px"
                }
            }]
        },

    });

    function customizePivotToolbar(toolbar) {
        // get all tabs 
        let tabs = toolbar.getTabs();
        toolbar.getTabs = function() {
            // add new tab 
            tabs.unshift({
                id: "expand-tab",
                title: "Expand All",
                handler: expandTab,
                icon: this.icons.fullscreen
            }, {
                id: "collapse-tab",
                title: "Collapse All",
                handler: collapseTab,
                icon: this.icons.minimize
            });
            return tabs;
        }
        //to expand pivot
        let expandTab = function() {
            reportpivot.expandAllData();
        }
        //to collapse and shrink pivot
        let collapseTab = function() {
            reportpivot.collapseAllData();
        }

        reportpivot.expandAllData();
    }
</script>