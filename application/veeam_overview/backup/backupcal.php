<?php

include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);	

$hostid = $_GET["hostid"] ?? array("10729");
$groupid = $_GET["groupid"];

$hostArr = "";
foreach ($hostid as $hostID) {
    $hostArr .= "hostid[]=" . $hostID . "&";
    $counthost++;
}

//time variables
$timefrom = $_GET['timefrom'] ?? strtotime("last sunday");
$timetill = $_GET['timetill'] ?? strtotime("next saturday");

//display time format
$diff = $timetill - $timefrom;

//store itemid array
$itemid = array();
$count2 = 0;
$series = array();
$color = array();

foreach ($hostid as $hostID) {

    $params = array(
    "output" => array("name"),
    "hostids" => $hostID
    );
    //call api method
    $result = $zbx->call('host.get', $params);
    foreach ($result as $host) {
        $gethostname = $host["name"];
    }

    //last run time job
    $params = array(
    "output" => array("itemid", "name", "error", "lastvalue"),
    "hostids" => $hostID,
    "search" => array("name" => "last run time job"), //seach id contains specific word
    );
    //call api method
    $result = $zbx->call('item.get', $params);
    if (!empty($result)) {
        foreach ($result as $item) {

            //get host name in veeam and item run id
            $gethost_veeam = substr($item["name"], 20);
            $itemrun_id = $item["itemid"];

            // echo $itemname_run."<br>";

            //last end time job
            $params = array(
            "output" => array("itemid", "name", "error", "lastvalue"),
            "hostids" => $hostID,
            "search" => array("name" => "last end time job : $gethost_veeam"), //seach id contains specific word
            );
            //call api method
            $result = $zbx->call('item.get', $params);

            foreach ($result as $item) {
                //get item end id
                $itemend_id = $item["itemid"];
            }

            $itemid[] = array(
                "runid" => $itemrun_id,
                "endid" => $itemend_id,
                "name" => $gethost_veeam,
                "hostname" => $gethostname
            );
        }
    }

    // $params = array(
    // "output" => array("itemid", "name", "error", "lastvalue"),
    // "hostids" => $hostID,
    // "search" => array("name" => "last end time job"), //seach id contains specific word
    // );
    // //call api method
    // $result = $zbx->call('item.get', $params);
    // if (!empty($result)) {
    //     foreach ($result as $item) {
    //         $itemid[] = array(
    //             "id" => $item["itemid"], 
    //             "name" => $item["name"],
    //             "hostname" => $gethostname,
    //             "lastvalue" => $item["lastvalue"]
    //         );
    //     }
    // }
}

// print "<pre>";
// print json_encode($itemid, JSON_PRETTY_PRINT);
// print "</pre>";

$itemdata = array();
$itemseries = "";

$counthost = 0;

foreach ($itemid as $item) {

  // echo $item["name"];

    $params = array(
        "output" => "extend",
        "history" => 3,
        "itemids" => $item["endid"],
        "sortfield" => "clock",
        "sortorder" => "DESC",
        "time_from" => $timefrom,
        "time_till" => $timetill
    );
    //call api history.get with params
    $result1 = $zbx->call('history.get', $params);
    if (!empty($result1)) {
        foreach (array_reverse($result1) as $history1) {

            $runclock = $history1["value"] * 1000;
            $runclock_txt = date("Y-m-d h:i A", $history1["value"]);

            // $params = array(
            //     "output" => "extend",
            //     "history" => 3,
            //     "itemids" => $item["endid"],
            //     "sortfield" => "clock",
            //     "sortorder" => "DESC",
            //     "time_from" => $timefrom,
            //     "time_till" => $timetill
            // );
            // //call api history.get with params
            // $result2 = $zbx->call('history.get', $params);

            // foreach (array_reverse($result2) as $history2) {
            //     $endclock = $history2["value"] * 1000;
            //     $endclock_txt = date("Y-m-d h:i A", $history2["value"]);
            // }

            $itemdata[] = array(
                // "title" => $item["name"]." ".$runclock_txt." - ".$endclock_txt,
                "name" => $item["name"]." ".$runclock_txt,
                "title" => $item["name"],
                "start" => $runclock
            );
        }
    }
    
    // $series[$count2] = array(
    //     "name" => $item["hostname"].": ".$item["name"],
    //     "data" => $itemdata
    // );
    // $count2++;
}

// $itemdata = array_unique($itemdata);

// $itemseries = json_encode($itemdata, JSON_PRETTY_PRINT);
// $itemseries .= $itemdata;

// for ($i=0; $i < $count2; $i++) { 
//     $color[] = "#".random_color();
// }

$itemseries = [];
foreach($itemdata as $item) {
    $hash = $item["name"];
    $itemseries[$hash] = $item;
}
$itemseries = json_encode(array_values($itemseries), JSON_PRETTY_PRINT);

// print "<pre>";
// print $itemseries;
// print "</pre>";
?>
<html>
    <head>
        <style>
            #calendar {
                max-height: 600px;
                min-height: 400px;
                height: auto;
            }
            .fc-month-view span.fc-title{
                white-space: normal;
            }
        </style>
    </head>
    <body>
        <div id='calendar'></div>
    </body>
</html>

<script>

var timefrom = <?php echo $timefrom; ?> * 1000;
var timetill = <?php echo $timetill; ?> * 1000;
var hostArr = '<?php echo $hostArr; ?>';
var currentURL = document.URL;

var calendarEl = document.getElementById('calendar');

var calendar = new FullCalendar.Calendar(calendarEl, {
  themeSystem: 'bootstrap',
  customButtons: {
    LastOneWeek: {
      bootstrapFontAwesome: 'fa-chevron-left',
      click: function() {
        var lastoneweek_from = moment(timefrom).startOf('week').subtract(1, 'week').unix();
        var lastoneweek_till = moment(timefrom).endOf('week').subtract(1, 'week').unix();

        // //trim and fine the position in url string to cut timefrom and timetil
        // var stringPos = currentURL.search("timefrom");
              
        // //if no timefrom in url string
        
        // if (stringPos == -1) {
        //   var newURL = currentURL + "&timefrom=" + lastoneweek_from + "&timetill=" + lastoneweek_till;
        // } 
        // else {
        //   var trimmedUrl = currentURL.substr(0, stringPos-1);

        //   //declare new URL together with new values
        //   var newURL = trimmedUrl + "&timefrom=" + lastoneweek_from + "&timetill=" + lastoneweek_till;
        // }

        var timerange = "&timefrom=" + lastoneweek_from + "&timetill=" + lastoneweek_till;

        //alert(hostUrl);
        //submit the form by reopening the window with new values 
        // location.assign(newURL);
        $("#backupcal").load("application/veeam_overview/backup/backupcal.php?" + hostArr + timerange); 
      }
    },
    NextOneWeek: {
      bootstrapFontAwesome: 'fa-chevron-right',
      click: function() {
        var addoneweek_from = moment(timefrom).startOf('week').add(1, 'week').unix();
        var addoneweek_till = moment(timefrom).endOf('week').add(1, 'week').unix();
        //trim and fine the position in url string to cut timefrom and timetil
        var stringPos = currentURL.search("timefrom");
              
        //if no timefrom in url string
        
        // if (stringPos == -1) {
        //   var newURL = currentURL + "&timefrom=" + addoneweek_from + "&timetill=" + addoneweek_till;
        // } 
        // else {
        //   var trimmedUrl = currentURL.substr(0, stringPos-1);

        //   //declare new URL together with new values
        //   var newURL = trimmedUrl + "&timefrom=" + addoneweek_from + "&timetill=" + addoneweek_till;
        // }

        var timerange = "&timefrom=" + addoneweek_from + "&timetill=" + addoneweek_till;

        //alert(hostUrl);
        //submit the form by reopening the window with new values 
        // location.assign(newURL);
        $("#backupcal").load("application/veeam_overview/backup/backupcal.php?" + hostArr + timerange);
      }
    }
  },
  headerToolbar: {
    start: '', // will normally be on the left. if RTL, will be on the right
    right: 'LastOneWeek NextOneWeek', // will normally be on the right. if RTL, will be on the left
    left: ''
  },
  events: <?php echo $itemseries; ?>,
    eventTimeFormat: { // like '14:30:00'
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    meridiem: "short"
  },
  scrollTime: '00:00:00',
  initialView: 'dayGridWeek'
});

calendar.changeView('dayGridWeek', '<?php echo date("Y-m-d", $timefrom); ?>');

calendar.render();

</script>