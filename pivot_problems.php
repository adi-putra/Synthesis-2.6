<?php
include 'session.php';
// get group id
$groupid = $_GET["groupid"] ?? 16;

//get host id in group
$gethostid = array();
$params = array(
    "output" => array("hostid"),
    "groupids" => $groupid
);
//call api
$result = $zbx->call('host.get',$params);
foreach ($result as $host) {
    $gethostid[] = $host["hostid"];
}

$hostid = $_GET["hostid"] ?? $gethostid; // if no GET host id, retrieve from groupid

$timefrom = $_GET['timefrom'] ?? time() - 2592000;
$timetill = $_GET['timetill'] ?? time();
                                    
                                    
                                    //start pivot data fetch
                                    $pivot_data = ""; //store pivot JSON
                                    foreach ($hostid as $hostID) {
                                        $params = array(
                                            "output" => array("name"),
                                            "hostids" => $hostID
                                        );
                                        //call api problem.get only to get eventid
                                        $result = $zbx->call('host.get',$params);
                                        foreach ($result as $host) {
                                            $hostname = $host["name"];
                                        }

                                        $params = array(
                                        "output" => array("severity", "acknowledged", "name", "clock", "r_clock"),
                                        "hostids" => $hostID,
                                        "selectAcknowledges" => array("userid", "clock", "message", "action"),
                                        "recent" => true,
                                        "time_from" => $timefrom,
                                        "time_till" => $timetill
                                        );
                                        //call api problem.get only to get eventid
                                        $result = $zbx->call('problem.get',$params);
                                        foreach ($result as $row) {

                                            //acknowledge class
                                            if ($row['acknowledged'] == "0") {
                                              $row['acknowledged'] = "No";
                                            }
                                            else{
                                              $row['acknowledged'] = "Yes";
                                            }
                                            $acknowledged = $row['acknowledged'];

                                            //problem names
                                            $name = $row['name'];

                                            //if r_clock exist, then the problem status is RESOLVE
                                            if ($row["r_clock"] > 0) {
                                                $recovery_time =  date("Y-m-d\\TH:i:s", $row["r_clock"]);
                                                $status = "RESOLVE";
                                            }
                                            else if ($row["r_clock"] == "0") {
                                                $recovery_time =  "";
                                                $status = "PROBLEM";
                                            }

                                            //severity classes
                                            if ($row["severity"] == "0") {
                                            $row["severity"] = "Unclassied";
                                            }
                                            else if ($row["severity"] == "1") {
                                            $row["severity"] = "Info";
                                            }
                                            else if ($row["severity"] == "2") {
                                              $row["severity"] = "Warning";
                                            }
                                            else if ($row["severity"] == "3") {
                                              $row["severity"] = "Average";
                                            }
                                            else if ($row["severity"] == "4") {
                                              $row["severity"] = "High";
                                            }
                                            else if ($row["severity"] == "5") {
                                              $row["severity"] = "Disaster";
                                            }
                                            $severity = $row['severity'];

                                                
                                                //time config
                                                $datetime = date("Y-m-d", $row["clock"]);
                                                $date = date("Y-m-d", $row["clock"]);
                                                $time = date("H:i:s", $row["clock"]);
                                                $time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $time);
                                                sscanf($time, "%d:%d:%d", $hours, $minutes, $seconds);
                                                $time = $hours * 3600 + $minutes * 60 + $seconds;
                                                $row["Hour"] = date("H", $row["clock"]);
                                                $hour = $row["Hour"];
                                                $row["Minute"] = date("i", $row["clock"]);
                                                $minute = $row["Minute"];
                                                $row["Second"] = date("s", $row["clock"]);
                                                $second = $row["Second"];

                                                //if acknowledge is empty
                                                if (empty($row['acknowledges'])) {
                                                    $pivot_data .= "{Host: '$hostname', ";
                                                    $pivot_data .= "Message_Ack: 'No data', ";
                                                    $pivot_data .= "Action_Ack: 'No data', ";
                                                    $pivot_data .= "Datetime_Ack: 'No data', ";
                                                    $pivot_data .= "User_Ack: 'No data', ";
                                                    $pivot_data .= "Problem_Date: '$datetime', ";
                                                    $pivot_data .= "Date: '$date', ";
                                                    $pivot_data .= "Time: $time, ";
                                                    $pivot_data .= "Hour: $hour, ";
                                                    $pivot_data .= "Minute: $minute, ";
                                                    $pivot_data .= "Second: $second, ";
                                                    $pivot_data .= "Problem: '$name', ";
                                                    $pivot_data .= "Severity: '$severity', ";
                                                    $pivot_data .= "Acknowledged: '$acknowledged',";
                                                    $pivot_data .= "Recovery_Time: '$recovery_time',";
                                                    $pivot_data .= "Status: '$status'},";
                                                }

                                                //if acknowledge not empty
                                                else {

                                                foreach ($row['acknowledges'] as $ack) {
                                                    //insert data with acknowledgement values
                                                    $ack['clock'] = date("Y-m-d\\TH:i:s", $ack['clock']);

                                                    //JSON string
                                                    $pivot_data .= "{Host: '$hostname', ";
                                                    $pivot_data .= "Message_Ack: '".$ack["message"]."', ";
                                                    $pivot_data .= "Action_Ack: '".$ack["action"]."', ";
                                                    $pivot_data .= "Datetime_Ack: '".$ack["clock"]."', ";
                                                    $params = array(
                                                        "output" => array("name"),
                                                        "userids" => $ack["userid"]
                                                    );
                                                    //call api problem.get only to get eventid
                                                    $result2 = $zbx->call('user.get',$params);
                                                    foreach ($result2 as $user) {
                                                        $user_ack = $user["name"];
                                                        $pivot_data .= "User_Ack: '".$user_ack."', ";
                                                    }
                                                    $pivot_data .= "User_Ack: '".$user_ack."', ";
                                                    $pivot_data .= "Datetime: '$datetime', ";
                                                    $pivot_data .= "Problem_Date: '$date', ";
                                                    $pivot_data .= "Time: $time, ";
                                                    $pivot_data .= "Hour: $hour, ";
                                                    $pivot_data .= "Minute: $minute, ";
                                                    $pivot_data .= "Second: $second, ";
                                                    $pivot_data .= "Problem: '$name', ";
                                                    $pivot_data .= "Severity: '$severity', ";
                                                    $pivot_data .= "Acknowledged: '$acknowledged',";
                                                    $pivot_data .= "Recovery_Time: '$recovery_time',";
                                                    $pivot_data .= "Status: '$status'},";

                                                }
                                            }
                                          }         
                                        }           
                                        $pivot_data = substr($pivot_data, 0, -1);
                                        //echo $pivot_data;      
?>
<html>
<head>
    <?php include "head.php" ?>
</head>
<body>
<button onclick="expandAll()">Expand all data</button>
<button onclick="collapseAll()">Collapse all data</button>

<div id="wdr-component"></div>
<div id="chartContainer" style="margin-top: 50px;"></div>

<!-- Flexmonster pivot lib -->
<script src="flexmonster/flexmonster.js"></script>

<script>
var timefrom = '<?php echo date('d M Y H:i', $timefrom)?>';
var timetill = '<?php echo date('d M Y H:i', $timetill)?>';
var timerange = timefrom + " - " + timetill;
var pivotTitle = "Problems Report " + "(" + timerange + ")" ;

var pivot = new Flexmonster({
  container: "#wdr-component",
  componentFolder: "flexmonster/",
  licenseKey: "Z7XF-XHJB30-3F3313-0L6T1D-343J5E-6G5L4B-362H3M-2U364P-362P37",
  toolbar: true,
  report: {
    dataSource: {
      //DATA TYPES
      data: [{
        "Host": {
            type: "string"
        },
        "Message_Ack": {
            type: "string"
        },
        "Action_Ack": {
            type: "number"
        },
        "Datetime_Ack": {
            type: "datetime"
        },
        "User_Ack": {
            type: "string"
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
        "Hour": {
            type: "hour"
        },
        "Minute": {
            type: "minute"
        },
        "Second": {
            type: "second"
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
        "Recovery_Time": {
            type: "datetime"
        },
        "Status": {
            type: "string"
        }
    }, <?php echo $pivot_data; ?>]//php pivot data
    },
    //SLICE, ROWS AND COLUMNS
    "slice": {
        "reportFilters": [
            {
                "uniqueName": "Message_Ack"
            },
            {
                "uniqueName": "Action_Ack"
            },
            {
                "uniqueName": "Datetime_Ack"
            },
            {
                "uniqueName": "User_Ack"
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
                "uniqueName": "Acknowledged"
            },
            {
                "uniqueName": "Recovery_Time"
            }
        ],
        "rows": [
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
            columns: [{
                    uniqueName: "Problem_Date"
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
    },  //CONDITIONS
        "conditions": [
        {
            "formula": "#value > 0",
            "measure": "Problem",
            "format": {
                "backgroundColor": "#0598df",
                "color": "#FFFFFF",
                "fontFamily": "Arial",
                "fontSize": "12px"
            }
        }
    ]
  },
});

//to expand pivot
function expandAll() {
  flexmonster.expandAllData();
}

//to collapse and shrink pivot
function collapseAll() {
  flexmonster.collapseAllData();
}

</script>
</body>
</html>

