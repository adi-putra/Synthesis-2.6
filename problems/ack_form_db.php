<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
include($_SERVER['DOCUMENT_ROOT'] . "/synthesis/db/db_conn.php");

$eventid = $_GET["eventid"];
$hostid = $_GET["hostid"];

//echo $eventid." ".$hostid;

//get problem name
$params = array(
    "output" => array("name", "acknowledged", "severity"),
    "eventids" => $eventid
);

$result = $zbx->call('event.get', $params);
foreach ($result as $event) {
    $problemName = $event["name"];
    $ack_status = $event["acknowledged"];
    $old_severity = $event["severity"];
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
        <p>
            <b>Problem: </b><br>
            <?php echo $problemName; ?>
        </p>
        <input type="hidden" name="eventid" value='<?php echo $eventid; ?>'>
        <input type="hidden" name="hostid" value='<?php echo $hostid; ?>'>
        <label>History: </label>
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
                    //get problem acknowledgment record from db
                    $count_ack = 0;
                    $sql = "SELECT * FROM ack WHERE eventid='$eventid' ORDER BY ack_date DESC";
                    $db_result = $conn->query($sql); 
                    if ($db_result->num_rows > 0) {
                        // output data of each row
                        while($db_row = $db_result->fetch_assoc()) {
                            if ($count_ack == 0) {
                                //set current acknowledge status
                                $close_status = $db_row["ack_close"];
                                $acknowledged = $db_row["ack_status"];
                            }

                            $ack_status = $db_row["ack_status"];

                            //determine value for ack status
                            if ($ack_status == 1) {
                                $ack_status = "<i class='fas fa-thumbs-up'></i>";
                            }
                            else {
                                $ack_status = "<i class='fas fa-thumbs-down'></i>";
                            }

                            print "<tr>
                                    <td>".$db_row["ack_date"]."</td>
                                    <td>".$db_row["ack_user"]."</td>
                                    <td>".$db_row["ack_message"]."</td>
                                    <td>$ack_status</td>
                                    </tr>";

                            $count_ack++;
                        }
                    } 
                    else {
                        echo "<tr>
                                <td colspan='5' style='text-align: center;'>No data</td>
                            </tr>";
                    }
                ?>
            </tbody>
        </table>
        <label>Note: </label>
        <textarea class="form-control" rows="5" cols="10" name="ack_message"></textarea><br>
        <?php
        //determine if problem is acknowledge or not, change label for checkbox ack status
        if ($acknowledged == 0) {
            print '<input type="checkbox" id="ack_status" name="ack_status" value="2">
                    <label for="ack_status">&nbsp;Acknowledge</label><br>';
        }
        else {
            print '<input type="checkbox" id="ack_status" name="ack_status" value="2" checked>
                    <label for="ack_status">&nbsp;Acknowledge</label><br>';
        }
        ?>
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
        <?php
        if ($close_status == 1) {
            print '<input type="checkbox" id="ack_close" name="ack_close" value="1" checked>
            <label for="ack_close">&nbsp;Close problem</label><br>';
        }
        else {
            print '<input type="checkbox" id="ack_close" name="ack_close" value="1">
            <label for="ack_close">&nbsp;Close problem</label><br>';
        }
        ?>
        </div>
        <div class="modal-footer">
        <button onclick="form_reset()" id="resetBtn" class="btn btn-default pull-left">Reset</button>
        <button id="submitackForm" class="btn btn-success">Save changes</button>
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
    });

    $("#closeackModal").click()
    //alert(gethostid + " " + geteventid + " " + getack_message + " " + getack_status + " " + getack_severity + " " + getack_close + " " );
  });
});

function form_reset() {
    document.getElementById("ackForm").reset();
}
function form_submit() {
    document.getElementById("ackForm").submit();
}
</script>

<?php
$conn->close();
?>