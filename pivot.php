<?php
include 'session.php';

$hostid = "10361";

$timefrom = time() - 30*24*60*60;
$timetill = time();

                                        $params = array(
                                        "output" => array("severity", "acknowledged", "name", "clock"),
                                        "hostids" => $hostid,
                                        "time_from" => $timefrom,
                                        "time_till" => $timetill
                                        );
                                        //call api problem.get only to get eventid
                                        $result = $zbx->call('problem.get',$params);
                                        $pivot_data = "";
                                        foreach ($result as $row) {
                                            
                                            $datetime = date("Y-m-d\\TH:i:s", $row["clock"]);
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

                                            $pivot_data .= "{Datetime: '$datetime', ";
                                            $pivot_data .= "Date: '$date', ";
                                            $pivot_data .= "Time: $time, ";
                                            $pivot_data .= "Hour: $hour, ";
                                            $pivot_data .= "Minute: $minute, ";
                                            $pivot_data .= "Second: $second, ";

                                            //problem names
                                            $name = $row['name'];

                                            //severity classes
                                            if ($row["severity"] == "1") {
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

                                            //acknowledge class
                                            if ($row['acknowledged'] == "0") {
                                              $row['acknowledged'] = "No";
                                            }
                                            else{
                                              $row['acknowledged'] = "Yes";
                                            }
                                            $acknowledged = $row['acknowledged'];

                                            $pivot_data .= "Problem: '$name', ";
                                            $pivot_data .= "Severity: '$severity', ";
                                            $pivot_data .= "Acknowledged: '$acknowledged'},";
                                          }                                    

?>
<html>
<head></head>
<body>
<button onclick="expandAll()">Expand all data</button>
<button onclick="collapseAll()">Collapse all data</button>
<button onclick="refreshpivot()">Click to refresh</button>

<div id="wdr-component"></div>


<link href="https://cdn.webdatarocks.com/latest/webdatarocks.min.css" rel="stylesheet"/>
<script src="https://cdn.webdatarocks.com/latest/webdatarocks.toolbar.min.js"></script>
<script src="https://cdn.webdatarocks.com/latest/webdatarocks.js"></script>
<script>
var pivot = new WebDataRocks({
  container: "#wdr-component",
  toolbar: true,
  report: {
    dataSource: {
      //DATA TYPES
      data: [{
        "Datetime": {
            type: "datetime"
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
        }
    }, <?php print $pivot_data; ?>]//php pivot data
    },
    //SLICE, ROWS AND COLUMNS
    slice: {
          "reportFilters": [
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
                    "uniqueName": "Time"
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
                }
            ],
            rows: [{
                uniqueName: "Problem"
            },
            {
                uniqueName: "Severity"
            }
            ],
            columns: [{
                    uniqueName: "Datetime"
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
            "type": "classic",
            "showTotals": "columns"
        }
    },  //CONDITIONS
        "conditions": [
        {
            "formula": "#value > 1",
            "measure": "Problem",
            "format": {
                "backgroundColor": "#0598df",
                "color": "#FFFFFF",
                "fontFamily": "Arial",
                "fontSize": "12px"
            }
        }
    ]
  }
});

function expandAll() {
  webdatarocks.expandAllData();
}
function collapseAll() {
  webdatarocks.collapseAllData();
}
function refreshpivot() {
    webdatarocks.refresh();
}
</script>
</body>
</html>

