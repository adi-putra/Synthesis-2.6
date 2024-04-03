<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
$hostid = $_GET["hostid"];

function formatBytes($bytes, $precision = 2)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title></title>
</head>

<body>
    <div class="row">
        <div class="col-md-12">
            <!-- CPU Util table -->
            <table id="luncaptable" class="table table-bordered table-striped">
                <caption><i>Updated since: <?php echo date("d/m/y h:i A", time()); ?></i></caption>
                <thead>
                    <tr>
                        <th>Lun Name</th>
                        <th>Total Capacity</th>
                        <th>Available Capacity</th>
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

                    // Get Lun Name
                    $lun_name = [];
                    $params = array(
                        "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                        "hostids" => $hostid,
                        "search" => array("name" => array("LUN Total Capacity")),
                        "searchByAny" => true
                    );
                    //call api problem.get only to get eventid
                    $result = $zbx->call('item.get', $params);
                    foreach ($result as $item) {
                        if ($item["error"] == "") {
                            $item_name = substr($item["name"], 22);
                            $lun_name[] = substr($item_name, 0, -1);
                        }
                    }


                    // Loop through all nodes
                    for ($i = 0; $i < count($lun_name); $i++) {

                        // Total Capacity
                        $params = array(
                            "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                            "hostids" => $hostid,
                            "search" => array("name" => array("LUN Total Capacity - [" . $lun_name[$i] . "]")),
                            "searchByAny" => true
                        );
                        //call api problem.get only to get eventid
                        $result = $zbx->call('item.get', $params);
                        foreach ($result as $item) {
                            $luntotal_value = $item["lastvalue"];
                        }

                        // Available Capacity
                        $params = array(
                            "output" => array("itemid", "name", "lastvalue", "key_", "lastclock", "delay", "error"),
                            "hostids" => $hostID,
                            "search" => array("name" => array("LUN Available Capacity - [" . $lun_name[$i] . "]")),
                            "searchByAny" => true
                        );
                        //call api problem.get only to get eventid
                        $result = $zbx->call('item.get', $params);
                        if (!empty($result)) {
                            foreach ($result as $item) {
                                if ($item["lastvalue"] < 0) {
                                    $lunfree_value = substr($item["lastvalue"], 1);
                                    $lunfree_value = formatBytes($lunfree_value);
                                    $lunfree_value = "-" . $lunfree_value;
                                } else {
                                    $lunfree_value = formatBytes($item["lastvalue"]);
                                }
                            }
                        } else {
                            $lunfree_value = "No data";
                        }

                        print "<tr>";
                        print "<td> " . $lun_name[$i] . "</td>";
                        print "<td>" . formatBytes($luntotal_value) . "</td>";
                        print "<td>" . $lunfree_value. "</td>";
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
        $('#luncaptable').DataTable({
            "order": [
                [2, "asc"]
            ],
            "scrollY": "400px",
            scrollCollapse: true,
            "paging": true
        });
    });
</script>