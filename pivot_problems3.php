<?php
include 'session.php';
//ini_set("memory_limit",-1);
//error_reporting(E_ALL);
//ini_set('display_errors', 'on');
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

$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? time();
                                    
$diff = $timetill - $timefrom;
if ($diff >= 7948800)  {
    //remove memory limit
    ini_set("memory_limit",-1);
}                                                                 
                                    //string for hostid url and count
                                    $hostUrl = "";
                                    $count = 0;

                                    
                                    $pivot_data = array(); //store pivot JSON

                                    //start pivot data fetch
                                    foreach ($hostid as $hostID) {
                                        $hostUrl .= "hostid[]=".$hostID."&";
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
                                        "output" => array("severity", "acknowledged", "name", "clock", "r_clock", "value"),
                                        "hostids" => $hostID,
                                        "selectAcknowledges" => array("userid", "clock", "message", "action"),
                                        "time_from" => $timefrom,
                                        "time_till" => $timetill,
                                        "select_alerts" => "extend"
                                        );
                                        //call api problem.get only to get eventid
                                        $result = $zbx->call('event.get',$params);
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

                                            //if value is 0, means OK
                                            if ($row["value"] == 0) {
                                                $status = "RESOLVED";
                                            }
                                            else if ($row["value"] == 1) {
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

                                                //if alert is not send, store variable "No"
                                                if (empty($row["alerts"])) {
                                                    $alertSent = "No";
                                                }
                                                else {
                                                    $alertSent = "Yes";
                                                }

                                                //if acknowledge is empty
                                                if (empty($row['acknowledges'])) {
                                                    $pivot_data[$count] = array(
                                                        "Host" => $hostname, 
                                                        "Message_Ack" => "No data", 
                                                        "Action_Ack" => "No data", 
                                                        "Datetime_Ack" => "No data", 
                                                        "User_Ack" => "No data", 
                                                        "Problem_Date" => "$datetime",
                                                        "Date" => $date,
                                                        "Time" => $time,
                                                        "Hour" => $hour,
                                                        "Minute" => $minute,
                                                        "Second" => $second,
                                                        "Problem" => $name,
                                                        "Severity" => $severity,
                                                        "Acknowledged" => $acknowledged,
                                                        "Recovery_Time" => $recovery_time,
                                                        "Status" => $status,
                                                        "Alert_Sent" => $alertSent
                                                    );
                                                }

                                                //if acknowledge not empty
                                                else {

                                                foreach ($row['acknowledges'] as $ack) {
                                                    //insert data with acknowledgement values
                                                    $ack['clock'] = date("Y-m-d\\TH:i:s", $ack['clock']);

                                                    //JSON string
                                                    $pivot_data[$count] = array("Host" => $hostname);
                                                    $pivot_data[$count] = array("Message_Ack" => $ack["message"]);
                                                    $pivot_data[$count] = array("Action_Ack" => $ack["action"]);
                                                    $pivot_data[$count] = array("Datetime_Ack" => $ack["clock"]);
                                                    $params = array(
                                                        "output" => array("name"),
                                                        "userids" => $ack["userid"]
                                                    );
                                                    //call api problem.get only to get eventid
                                                    $result2 = $zbx->call('user.get',$params);
                                                    foreach ($result2 as $user) {
                                                        $user_ack = $user["name"];
                                                        $pivot_data[$count] = array("User_Ack" => $user_ack);
                                                    }
                                                    $pivot_data[$count] = array("User_Ack" => $user_ack);
                                                    $pivot_data[$count] = array("Problem_Date" => "$datetime");
                                                    $pivot_data[$count] = array("Date" => $date);
                                                    $pivot_data[$count] = array("Time" => $time);
                                                    $pivot_data[$count] = array("Hour" => $hour);
                                                    $pivot_data[$count] = array("Minute" => $minute);
                                                    $pivot_data[$count] = array("Second" => $second);
                                                    $pivot_data[$count] = array("Problem" => $name);
                                                    $pivot_data[$count] = array("Severity" => $severity);
                                                    $pivot_data[$count] = array("Acknowledged" => $acknowledged);
                                                    $pivot_data[$count] = array("Recovery_Time" => $recovery_time);
                                                    $pivot_data[$count] = array("Status" => $status);
                                                    $pivot_data[$count] = array("Alert_Sent" => $alertSent);
                                                }
                                            }
                                            $count++;
                                          }         
                                          
                                        }           
                                        //convert to json and trim first and last character
                                        $json_string = json_encode($pivot_data);  
                                        $json_string = substr($json_string, 0, -1); 
                                        $json_string = substr($json_string, 1);

?>
<html>
    <?php include "head.php" ?>
<body>
    <br>
    <section class="content">
    <div class="row">
        <!-- Date Form -->
        <div class="col-xs-12">
            <div class="box box-solid box-primary">
                <div class="box-header">
                    <h3 class="box-title">Filter Date <i class="fa fa-filter"></i></h3>
                    <div class="box-tools pull-right">
                        <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-4">
                            <div class="form-group">
                                <label>From:</label>
                                <div class='input-group date' id='datetimepicker6'>
                                    <input type='text' class="form-control" name="timefrom" placeholder="From:" value="<?php echo date('d-m-Y h:i A', $timefrom); ?>" />
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                                <br>
                                <label>To:</label>
                                <div class='input-group date' id='datetimepicker7'>
                                    <input type='text' class="form-control" name="timetill" placeholder="To:" value="<?php echo date('d-m-Y h:i A', $timetill); ?>"/>
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                                <br>
                                <button class="btn bg-green" onclick="changeDateTime();">Apply</button>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            
                        </div>
                        <div class="col-xs-1">
                            <button class="btn btn-block btn-primary btn-xs" onclick="last2days();">Last 2 days</button>
                            <button class="btn btn-block btn-primary btn-xs" onclick="last7days();">Last 7 days</button>
                            <button class="btn btn-block btn-primary btn-xs" onclick="last30days();">Last 30 days</button>
                            <button class="btn btn-block btn-primary btn-xs" onclick="last3months();">Last 3 months</button>
                            <button class="btn btn-block btn-primary btn-xs">Last 6 months</button>
                            <button class="btn btn-block btn-primary btn-xs">Last 1 year</button>
                            <button class="btn btn-block btn-primary btn-xs">Last 2 years</button>
                        </div>
                        <div class="col-xs-1">
                            <button class="btn btn-block btn-primary btn-xs">Yesterday</button>
                            <button class="btn btn-block btn-primary btn-xs">Day before yesterday</button>
                            <button class="btn btn-block btn-primary btn-xs">This day last week</button>
                            <button class="btn btn-block btn-primary btn-xs">Previous week</button>
                            <button class="btn btn-block btn-primary btn-xs">Previous month</button>
                            <button class="btn btn-block btn-primary btn-xs">Previous year</button>
                        </div>
                        <div class="col-xs-1">
                            <button class="btn btn-block btn-primary btn-xs">Today</button>
                            <button class="btn btn-block btn-primary btn-xs">Today so far</button>
                            <button class="btn btn-block btn-primary btn-xs">This week</button>
                            <button class="btn btn-block btn-primary btn-xs">This week so far</button>
                            <button class="btn btn-block btn-primary btn-xs">This month</button>
                            <button class="btn btn-block btn-primary btn-xs">This month so far</button>
                            <button class="btn btn-block btn-primary btn-xs">This year</button>
                            <button class="btn btn-block btn-primary btn-xs">This year so far</button>
                        </div>
                        <div class="col-xs-1">
                            <button class="btn btn-block btn-primary btn-xs">Last 5 minutes</button>
                            <button class="btn btn-block btn-primary btn-xs">Last 15 minutes</button>
                            <button class="btn btn-block btn-primary btn-xs">Last 30 minutes</button>
                            <button class="btn btn-block btn-primary btn-xs">Last 1 hour</button>
                            <button class="btn btn-block btn-primary btn-xs">Last 3 hours</button>
                            <button class="btn btn-block btn-primary btn-xs">Last 6 hours</button>
                            <button class="btn btn-block btn-primary btn-xs">Last 12 hours</button>
                            <button class="btn btn-block btn-primary btn-xs">Last 1 day</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pivot Table Load -->
    <div class="row">
        <div class="col-xs-12">
            <div id="wdr-component"></div>
        </div>
    </div>

    </section>

<script>
    function changeDateTime() {
  var gettimefrom = document.getElementsByName("timefrom")[0].value;
  var gettimetill = document.getElementsByName("timetill")[0].value;
  //if value is blank
  if (gettimefrom === "" || gettimetill === "") {
    alert("Zero/Wrong inputs detected!");
    return false;
  } else {
    var timefrom = moment(gettimefrom, "D-M-Y hh:mm A").unix();
    var timetill = moment(gettimetill, "D-M-Y hh:mm A").unix();

    //get current URL
    var currentURL = document.URL;

    //trim and fine the position in url string to cut timefrom and timetill
    var stringPos = currentURL.search("timefrom");
    var trimmedUrl = currentURL.substr(0, stringPos);

    //declare new URL together with new values
    var newURL = trimmedUrl+"timefrom="+timefrom+"&timetill="+timetill

    //alert(hostUrl);
    //submit the form by reopening the window with new values 
    location.assign(newURL);
  }
}

function last2days() {
  var timefrom = moment().subtract(2, 'days').unix();
  var timetill = moment().unix();

  //get current URL
  var currentURL = document.URL;

  //trim and fine the position in url string to cut timefrom and timetill
  var stringPos = currentURL.search("timefrom");
  var trimmedUrl = currentURL.substr(0, stringPos);

  //declare new URL together with new values
  var newURL = trimmedUrl+"timefrom="+timefrom+"&timetill="+timetill

  //alert(hostUrl);
  //submit the form by reopening the window with new values 
  location.assign(newURL);
}

function last7days() {
  var timefrom = moment().subtract(7, 'days').unix();
  var timetill = moment().unix();

  //get current URL
  var currentURL = document.URL;

  //trim and fine the position in url string to cut timefrom and timetill
  var stringPos = currentURL.search("timefrom");
  var trimmedUrl = currentURL.substr(0, stringPos);

  //declare new URL together with new values
  var newURL = trimmedUrl+"timefrom="+timefrom+"&timetill="+timetill

  //alert(hostUrl);
  //submit the form by reopening the window with new values 
  location.assign(newURL);
}

function last30days() {
  var timefrom = moment().subtract(30, 'days').unix();
  var timetill = moment().unix();

  //get current URL
  var currentURL = document.URL;

  //trim and fine the position in url string to cut timefrom and timetill
  var stringPos = currentURL.search("timefrom");
  var trimmedUrl = currentURL.substr(0, stringPos);

  //declare new URL together with new values
  var newURL = trimmedUrl+"timefrom="+timefrom+"&timetill="+timetill

  //alert(hostUrl);
  //submit the form by reopening the window with new values 
  location.assign(newURL);
}

function last3months() {
  var timefrom = moment().subtract(3, 'months').unix();
  var timetill = moment().unix();

  //get current URL
  var currentURL = document.URL;

  //trim and fine the position in url string to cut timefrom and timetill
  var stringPos = currentURL.search("timefrom");
  var trimmedUrl = currentURL.substr(0, stringPos);

  //declare new URL together with new values
  var newURL = trimmedUrl+"timefrom="+timefrom+"&timetill="+timetill

  //alert(hostUrl);
  //submit the form by reopening the window with new values 
  location.assign(newURL);
}
</script>

<!-- Change Date script -->
  <script type="text/javascript">
    $(function() {
      $('#datetimepicker6').datetimepicker({
        format: "DD-MM-YYYY hh:mm A"
      });
      $('#datetimepicker7').datetimepicker({
        format: "DD-MM-YYYY hh:mm A"
      });
      $("#datetimepicker6").on("dp.change", function(e) {
        $('#datetimepicker7').data("DateTimePicker").minDate(e.date);
      });
      $("#datetimepicker7").on("dp.change", function(e) {
        $('#datetimepicker6').data("DateTimePicker").maxDate(e.date);
      });
    });
  </script>

<script>
var timefrom = '<?php echo date('d M Y H:i', $timefrom)?>';
var timetill = '<?php echo date('d M Y H:i', $timetill)?>';
var timerange = timefrom + " - " + timetill;
var pivotTitle = "Events Report " + "(" + timerange + ")" ;

var pivot = new Flexmonster({
  container: "#wdr-component",
  componentFolder: "flexmonster/",
  licenseKey: "Z7XF-XHJB30-3F3313-0L6T1D-343J5E-6G5L4B-362H3M-2U364P-362P37",
  toolbar: true,
  beforetoolbarcreated: customizeToolbar,
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
        },
        "Alert_Sent": {
            type: "string"
        }
    }, <?php echo $json_string; ?>]
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
            },
            {
                "uniqueName": "Alert_Sent"
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
                    uniqueName: "[Measures]"
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

function customizeToolbar(toolbar) { 
    // get all tabs 
    var tabs = toolbar.getTabs(); 
    toolbar.getTabs = function () { 
        // add new tab 
        tabs.unshift(
        {  
            id: "expand-tab", 
            title: "Expand All", 
            handler: expandTab,  
            icon: this.icons.fullscreen
        },
        {  
            id: "collapse-tab", 
            title: "Collapse All", 
            handler: collapseTab,  
            icon: this.icons.minimize
        }); 
        return tabs; 
    } 
    //to expand pivot
    var expandTab = function() {
      flexmonster.expandAllData();
    }
    //to collapse and shrink pivot
    var collapseTab = function() {
      flexmonster.collapseAllData();
    }
} 

</script>
</body>
</html>

