<html>
    <head>
    </head>
</html>
<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
include($_SERVER['DOCUMENT_ROOT'] . "/synthesis/db/db_conn.php");

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

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

//CLOSED PROBLEMS PIVOT
$closed_data = array(); //store pivot JSON
$closed_count = 0;

//$sql = "SELECT * FROM problem WHERE r_eventid IS NULL ORDER BY clock DESC LIMIT 100"; 
$sql = "SELECT * FROM events WHERE clock >= $timefrom AND clock <= $timetill";
//$sql = "SELECT * FROM events WHERE eventid=49259456";
//$sql = "SELECT * FROM problem LIMIT 600";

$event_result = $conn->query($sql);

while($event = $event_result->fetch_assoc()) {

    //problems
    $name = $event['name'];
    $name = str_replace("Zabbix","Synthesis",$event['name']);
    $acknowledged = $event['acknowledged'];
    $eventid = $event['eventid'];
    $severity = $event['severity'];
    $objectid = $event['objectid'];
    $clock = $event['clock'];

    //event recovery
    $sql = "SELECT * FROM event_recovery WHERE eventid='$eventid'";
    $event_rec_results = $conn->query($sql);

    if (mysqli_num_rows($event_rec_results) == 0) { 
        continue;
    } else { 
        while($event_rec = $event_rec_results->fetch_assoc()) {
            if (!empty($event_rec["userid"])) {
                $userid = $event_rec["userid"];
                $r_eventid = $event_rec['r_eventid'];
            }
            else {
                continue 2;
            }
        }
    }   

    //r_event
    $sql = "SELECT * FROM events WHERE eventid='$r_eventid'";
    $r_event_results = $conn->query($sql);

    while($r_event = $r_event_results->fetch_assoc()) {
        $r_clock = $r_event["clock"];
    }

    //users
    $sql = "SELECT username FROM users WHERE userid='$userid'";
    $user_results = $conn->query($sql);

    while($user = $user_results->fetch_assoc()) {
        $user_username = $user["username"];
    }

    //time config
    $datetime = date("Y-m-d", $clock);
    $date = date("Y-m-d", $clock);
    $time = date("H:i:s", $clock);
    $time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $time);
    sscanf($time, "%d:%d:%d", $hours, $minutes, $seconds);
    $time = $hours * 3600 + $minutes * 60 + $seconds;
    $v["Hour"] = date("H", $clock);
    $hour = $v["Hour"];
    $v["Minute"] = date("i", $clock);
    $minute = $v["Minute"];
    $v["Second"] = date("s", $clock);
    $second = $v["Second"];
    $r_clock1 = date("Y-m-d\ H:i:s\ ", $r_clock);

    //status value
    $status_value = "RESOLVED";

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
    $message = "";
    $sql = "SELECT message FROM alerts WHERE eventid='$eventid'";
    $alert_results = $conn->query($sql);
    if (empty($alert_results)) {
        $message = "";
    }
    else {
        while($alert = $alert_results->fetch_assoc()) {
            $message_str = $alert['message'];
            $startpoint_str = stripos($message_str, "Last value:");
            $endpoint_str = stripos($message_str, "zbxtg;");
            $message_value = substr($message_str, $startpoint_str, ($endpoint_str - $startpoint_str));
        
            if (strlen($message_value) > 0) {
                $message = $message_value;
            }
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

    $closed_data[$closed_count] = array(
        "Event_ID" => "$eventid",
        "Problem_Date" => "$datetime",
        "Date" => $date,
        "Time" => $time,
        "Hour" => $hour,
        "Minute" => $minute,
        "Second" => $second,
        "Time" => "$time",
        "Recovery_Time" => "$r_clock1",
        "Host" => "$gethostname",
        "Problem" => "$name",
        "Severity" => "$severity",
        "Closed_By" => "$user_username",
        "Message" => "$message",
        "Acknowledged" => "$acknowledged"
    );
    
    $closed_count++;
    
}


//convert to json and trim first and last character
$json_closed = mb_convert_encoding($closed_data, 'UTF-8', 'UTF-8');
$json_closed = json_encode($json_closed);
$json_closed = substr($json_closed, 0, -1);
$json_closed = substr($json_closed, 1);

//echo $json_closed;
?>

<html>
	<head>
    </head>
    <body>
        <div class="row" style="overflow-x: auto; white-space: nowrap;">
            <div class="col-xs-12">
                <div id="closed-report"></div>
            </div>
        </div>
    </body>
</html>

<!-- Pivot script -->
<script type="text/javascript">
    var timefrom = '<?php echo date('d M Y H:i', $timefrom) ?>';
    var timetill = '<?php echo date('d M Y H:i', $timetill) ?>';
    var timerange = timefrom + " - " + timetill;
    var pivotTitle = "Closed Problems Report " + "(" + timerange + ")";

    var reportpivot = $("#closed-report").flexmonster({
        componentFolder: "/synthesis/plugins/flexmonster/",
        height: 800,
        width: 2300,
        licenseKey: "Z7XF-XHJB30-3F3313-0L6T1D-343J5E-6G5L4B-362H3M-2U364P-362P37",
        toolbar: true,
        beforetoolbarcreated: customizeClosedToolbar,
        customizeCell: customizeCell,
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
                    "Message": {
                        type: "string"
                    },
                    "Closed_By": {
                        type: "string"
                    }
                }, <?php echo $json_closed; ?>] //php pivot data
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
                        "uniqueName": "Message"
                    },
                    {
                        "uniqueName": "Closed_By"
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
                    "backgroundColor": "#FF5F5F",
                    "color": "#FFFFFF",
                    "fontFamily": "Arial",
                    "fontSize": "12px"
                }
            }]
        },

    });

    function customizeCell(cell,data) {
        cell.text = `<p style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title='${data.label}'>${cell.text}</p>`;
    }

    function customizeClosedToolbar(toolbar) {
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
            // remove some not used tabs 
            tabs = tabs.filter(tab => tab.id != "fm-tab-connect");
            tabs = tabs.filter(tab => tab.id != "fm-tab-open");
            tabs = tabs.filter(tab => tab.id != "fm-tab-save");
            tabs = tabs.filter(tab => tab.id != "fm-tab-format");
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