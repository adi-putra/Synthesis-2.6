<?php

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

include "session.php";

//get values
$report_id = $_POST["report_id"];
$report_name = $_POST["report_name"];
$report_url = $_POST["report_url"];
$hostid = json_decode($_POST["hostid"]);
$usrgrpid = json_decode($_POST["usrgrpid"]);
$userid = json_decode($_POST["userid"]);
$report_schedule = $_POST["report_schedule"];
$report_period = $_POST["report_period"];

if ($hostid == null) {
    $hostid = [];
}

if ($usrgrpid == null) {
    $usrgrpid = [];
}

if ($userid == null) {
    $userid = [];
}

// echo $report_id."\n";
// echo $report_name."\n";
// echo $report_url."\n";
// print json_encode($usrgrpid)."\n";
// print json_encode($userid)."\n";
// print $report_starttime."\n";

// die();

//grant permission to files
exec('sudo chmod 777 reporting/gen_report.json', $output, $return);

// open file to return report json 
$genreport_file = fopen("reporting/gen_report.json", "r") or die("Unable to open file!");
$genreport_data =  fread($genreport_file,filesize("reporting/gen_report.json"));
fclose($genreport_file);

//decode report json 
$genreport_data = json_decode($genreport_data, true);

//set new json
$new_genreport_data = [];

//count json data
$genreport_data_count = count($genreport_data);

//declare add new report schedule
$addreport_check = true; 

$cron_minute = 0;

// new cron entry
$newCronEntry = "";

foreach ($genreport_data as $data) {

    // if have same url, update the array
    if ($data["url"] == $report_url || $data["reportid"] == $report_id) {

        $new_genreport_data[] = array(
            "reportid" => $report_id,
            "name" => $report_name,
            "hostids" => $hostid,
            "usrgrpids" =>$usrgrpid,
            "userids" => $userid,
            "url" => $report_url,
            "schedule" => $report_schedule,
            "period" => $report_period
        );

        // check schedule
        if ($report_schedule == 1) {
            $cron_gen_time = "$cron_minute 0 * * *";
            $cron_send_time = "30 0 * * *";
        }
        else if ($report_schedule == 2) {
            $cron_gen_time = "$cron_minute 0 * * 0";
            $cron_send_time = "30 0 * * 0";
        }
        else if ($report_schedule == 3) {
            $cron_gen_time = "$cron_minute 0 1 * 0";
            $cron_send_time = "30 0 1 * 0";
        }

        $cron_reportid = $report_id;

        //toggle add new report schedule to false
        $addreport_check = false;
    }
    else {

        $new_genreport_data[] = array(
            "reportid" => $data["reportid"],
            "name" => $data["name"],
            "hostids" => $data["hostids"],
            "usrgrpids" =>$data["usrgrpids"],
            "userids" => $data["userids"],
            "url" => $data["url"],
            "schedule" => $data["schedule"],
            "period" => $data["period"]
        );

        // check schedule
        if ($data["schedule"] == 1) {
            $cron_gen_time = "$cron_minute 0 * * *";
            $cron_send_time = "30 0 * * *";
        }
        else if ($data["schedule"] == 2) {
            $cron_gen_time = "$cron_minute 0 * * 0";
            $cron_send_time = "30 0 * * 0";
        }
        else if ($data["schedule"] == 3) {
            $cron_gen_time = "$cron_minute 0 1 * 0";
            $cron_send_time = "30 0 1 * 0";
        }

        $cron_reportid = $data["reportid"];
    }

    // add cron minute to 3 minutes each report
    $cron_minute = $cron_minute + 3;

    // Edit to cron
    if ($cron_reportid != "") {
        $newCronEntry .= $cron_gen_time." /software/gen_report.sh ".$cron_reportid."\n";
        $newCronEntry .= $cron_send_time." /software/sendmail_report.sh ".$cron_reportid."\n";
    }

}

if ($addreport_check == true) {

    if ($report_id != "") {
        $new_genreport_data[] = array(
            "reportid" => $report_id,
            "name" => $report_name,
            "hostids" => $hostid,
            "usrgrpids" =>$usrgrpid,
            "userids" => $userid,
            "url" => $report_url,
            "schedule" => $report_schedule,
            "period" => $report_period
        );

        // check schedule
        if ($report_schedule == 1) {
            $cron_gen_time = "$cron_minute 0 * * *";
            $cron_send_time = "30 0 * * *";
        }
        else if ($report_schedule == 2) {
            $cron_gen_time = "$cron_minute 0 * * 0";
            $cron_send_time = "30 0 * * 0";
        }
        else if ($report_schedule == 3) {
            $cron_gen_time = "$cron_minute 0 1 * 0";
            $cron_send_time = "30 0 1 * 0";
        }

        $cron_reportid = $report_id;

        // add cron minute to 3 minutes each report
        $cron_minute = $cron_minute + 3;

        // Edit to cron
        if ($cron_reportid != "") {
            $newCronEntry .= $cron_gen_time." /software/gen_report.sh ".$cron_reportid."\n";
            $newCronEntry .= $cron_send_time." /software/sendmail_report.sh ".$cron_reportid."\n";
        }
        
    }

}

$new_genreport_data = json_encode($new_genreport_data, JSON_PRETTY_PRINT);

// echo $new_genreport_data;

//write to gen report json file
$genreport_file = fopen("reporting/gen_report.json", "w+") or die("Unable to open file!");
fwrite($genreport_file, $new_genreport_data);
fclose($genreport_file);


// revert permission to files
exec('sudo chmod 644 reporting/gen_report.json', $output, $return);

//grant permission to files
exec('sudo chmod 777 /var/spool/cron/root', $output, $return);

// Load the updated crontab entries
file_put_contents('/var/spool/cron/root', $newCronEntry);

// revert permission to files
exec('sudo chmod 644 /var/spool/cron/root', $output, $return);

// restart crontab
exec('sudo systemctl restart crond', $output, $return); // Get current crontab entries
?>