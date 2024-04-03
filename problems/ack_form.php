<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

$eventid = $_GET["eventid"];
$hostid = $_GET["hostid"];
$resolved = $_GET["resolved"] ?? 0;

//echo $eventid." ".$hostid;

//get problem name
$params = array(
    "output" => array("name", "acknowledged", "severity", "objectid"),
    "eventids" => $eventid,
    "select_acknowledges" => "extend",
    "selectHosts" => array("name")
);

$result = $zbx->call('event.get', $params);
foreach ($result as $event) {
    $problemName = $event["name"];
    $problemName = str_replace("Zabbix","Synthesis",$problemName);
    $event_objectid = $event["objectid"];
    $ack_status = $event["acknowledged"];
    $old_severity = $event["severity"];
    foreach ($event["hosts"] as $host) {
        $hostname = $host["name"];
    }
}

//get status manual close
$params = array(
    "output" => "extend",
    "search" => array("description" => $problemName)
);

$result = $zbx->call('trigger.get', $params);
foreach ($result as $trigger) {
    $close_status = $trigger["manual_close"];
}

function getActionStr($bit) {
    if ($bit == 1) {
        $action_str = "<i class='fas fa-times-circle'></i>&nbsp;";
    }
    else if ($bit == 2) {
        $action_str = "<i class='fas fa-thumbs-up'></i>&nbsp;";
    }
    else if ($bit == 4) {
        $action_str = "<i class='fas fa-comment'></i>&nbsp;";
    }
    else if ($bit == 3) {
        $action_str = "<i class='fas fa-times-circle'></i>&nbsp;<i class='fas fa-thumbs-up'></i>&nbsp;";
    }
    else if ($bit == 5) {
        $action_str = "<i class='fas fa-times-circle'></i>&nbsp;<i class='fas fa-comment'></i>&nbsp;";
    }
    else if ($bit == 6) {
        $action_str = "<i class='fas fa-thumbs-up'></i>&nbsp;<i class='fas fa-comment'></i>&nbsp;";
    }
    else if ($bit == 7) {
        $action_str = "<i class='fas fa-times-circle'></i>&nbsp;<i class='fas fa-thumbs-up'></i>&nbsp;<i class='fas fa-comment'></i>&nbsp;";
    }
    else if ($bit == 16) {
        $action_str = "<i class='fas fa-thumbs-down'></i>&nbsp;";
    }
    else if ($bit == 17) {
        $action_str = "<i class='fas fa-thumbs-down'></i>&nbsp;<i class='fas fa-times-circle'></i>&nbsp;";
    }
    else if ($bit == 20) {
        $action_str = "<i class='fas fa-thumbs-down'></i>&nbsp;<i class='fas fa-comment'></i>&nbsp;";
    }

    return $action_str;
}



//get current severity from db, if null get from zabbix
?>

<!DOCTYPE html>
<head>

</head>

<body class="skin-blue">
    <!-- Ack Form -->
    <div class="modal-body" id="ackformModal" style="overflow-y: auto; height:400px;">
    <form id="ackForm" action="/synthesis/problems/problem_update_ack.php" method="post">
        <input type="hidden" name="eventid" value='<?php echo $eventid; ?>'>
        <input type="hidden" name="hostid" value='<?php echo $hostid; ?>'>
        <input type="hidden" name="objectid" value='<?php echo $event_objectid; ?>'>
        <table class="table table-bordered table-striped">
            <tr>
                <th>Host</th>
                <td><?php echo $hostname; ?></td>
            </tr>
            <tr>
                <th>Problem</th>
                <td><?php echo $problemName; ?></td>
            </tr>
            <tr>
                <th>History</th>
                <td>
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Message</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($event["acknowledges"])) {
                                foreach ($event["acknowledges"] as $ack) {
                                    //check if message exist
                                    if (!empty($ack["message"])) {
                                        $message = $ack["message"];
                                    }
                                    else {
                                        $message = "No message";
                                    }

                                    //action_str
                                    $action_string = getActionStr($ack["action"]);
                        
                                    print "<tr>";
                                    print "<td>".date("Y-m-d\ H:i:s A\ ", $ack["clock"])."</td>";
                                    print "<td>".$ack["username"]."</td>";
                                    print "<td>$message</td>";
                                    print "<td>$action_string</td>";
                                    print "</tr>";
                                }
                            }
                            else {
                                print "<tr>";
                                print '<td colspan="4" align="center">No data</td>';
                                print "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <th>Note</th>
                <td><textarea class="form-control" rows="5" cols="10" name="ack_message"></textarea></td>   
            </tr>
            <tr>
                <th>Range</th>
                <td>
                    <input type="radio" name="event_range" value="0" checked/>
                    <label>Only this problem</label><br>
                    <input type="radio" name="event_range" value="1" />
                    <label>Select all related with this problem</label><br>
                </td>
            </tr>
            <tr>
                <th>
                    <?php
                    if ($ack_status == 0) {
                        print "Acknowledge";
                    }
                    else {
                        print "Unacknowledge";
                    }
                    ?>
                </th>
                <td>
                    <?php
                    //determine if problem is acknowledge or not, change label for checkbox ack status
                    if ($ack_status == 0) {
                        print '<input type="checkbox" id="ack_status" name="ack_status" value="2">';
                    }
                    else {
                        print '<input type="checkbox" id="ack_status" name="ack_status" value="16">';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>Close problem</th>
                <td>
                    <?php
                    if ($resolved == 1) {
                        print '<input type="checkbox" id="ack_close" name="ack_close" value="1" disabled>';
                    }
                    else {
                        if ($close_status == 1) {
                            print '<input type="checkbox" id="ack_close" name="ack_close" value="1">';
                        }
                        else {
                            print '<input type="checkbox" id="ack_close" name="ack_close" value="1" disabled>';
                        }
                    }
                    ?>
                </td>
            </tr>
        </table>
        
            <input type="checkbox" name="cbOpenSeverity" id="cbOpenSeverity" value="0" style="display: none;" />
            <label for="severityBox" style="display: none;"> Change severity</label><br>
            <div id="severityBox" style="display:none">
                <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default">
                        <input type="radio" name="ack_severity" value="0" autocomplete="off"> Unclassified
                    </label>
                    <label class="btn btn-default">
                        <input type="radio" name="ack_severity" value="1" autocomplete="off"> Information
                    </label>
                    <label class="btn btn-default">
                        <input type="radio" name="ack_severity" value="2" autocomplete="off"> Warning
                    </label>
                    <label class="btn btn-default">
                        <input type="radio" name="ack_severity" value="3" autocomplete="off"> Average
                    </label>
                    <label class="btn btn-default">
                        <input type="radio" name="ack_severity" value="4" autocomplete="off"> High
                    </label>
                    <label class="btn btn-default">
                        <input type="radio" name="ack_severity" value="5" autocomplete="off"> Disaster
                    </label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
        <button onclick="form_reset()" id="resetBtn" class="btn btn-default pull-left">Reset</button>
        <a href="maintenance.php"><button class="btn btn-primary">Maintenance</button></a>
        <button id="submitackForm" class="btn btn-success">Apply</button>
    </form>
    </div>
</body>
</html>

<script type="text/javascript">
//if checkbox change severity is checked, open severity div to change severity
$('#cbOpenSeverity').change(function() {
    $("input:radio[name='ack_severity']").each(function(i) {
        this.checked = false;
    });
    $('#severityBox').toggle();
});

//if reset button is clicked, close severity div
$('#resetBtn').click(function() {
    $('#severityBox').hide();
});
</script>

<script>
//form submit and reset button onclick
/*function form_submit() {
    document.getElementById("ackForm").submit();
    //click close button
    $('#closeackModal').click();
}*/

$(document).ready(function(){
  $("#submitackForm").click(function(){
    
    //get values from form
    var gethostid = document.getElementsByName("hostid")[0].value;
    var geteventid = document.getElementsByName("eventid")[0].value;
    var getobjectid = document.getElementsByName("objectid")[0].value;

    var getack_message = document.getElementsByName("ack_message")[0].value;

    //check checkbox ack_status
    if (document.querySelector('input[name="ack_status"]:checked') == null) {
        var getack_status = "";
    }
    else {
        var getack_status = document.querySelector('input[name="ack_status"]:checked').value;
    }

    //check radio ack_severity
    if (document.querySelector('input[name="ack_severity"]:checked') == null) {
        var getack_severity = "";
    }
    else {
        var getack_severity = document.querySelector('input[name="ack_severity"]:checked').value;
    }

    //check event range radio
    var getevent_range = document.querySelector('input[name="event_range"]:checked').value;
    
    //check checkbox ack_close
    if ($('input[name="ack_close"]').prop('disabled')) {
        var getack_close = "";
    }
    else {
        if (document.querySelector('input[name="ack_close"]:checked') == null) {
            var getack_close = "";
        }
        else {
            var getack_close = document.querySelector('input[name="ack_close"]:checked').value;
        }
    }

    //execute form
    $.post("/synthesis/problems/problem_update_ack.php", 
    {
        hostid: gethostid,
        eventid: geteventid,
        ack_message: getack_message,
        ack_status: getack_status,
        ack_severity: getack_severity,
        ack_close: getack_close,
        event_range: getevent_range,
        objectid: getobjectid
    },
    function(data, status){
        alert(data + "Acknowledgement Status: " + status);
    });

    $("#closeackModal").click()
    
    setTimeout(loadTableAfterAck, 1000);
    //alert(gethostid + " " + geteventid + " " + getack_message + " " + getack_status + " " + getack_severity + " " + getack_close + " " );
  });
});

function form_reset() {
    document.getElementById("ackForm").reset();
}
function form_submit() {
    document.getElementById("ackForm").submit();
}

function loadTableAfterAck() {
    loadProblemsTable();
    loadHistoryTable();
}
</script>

