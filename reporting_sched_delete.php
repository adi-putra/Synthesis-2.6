<?php

include "session.php";

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

//get values
$report_id = $_GET["report_id"];

//grant permission to files
exec('sudo chmod 777 reporting/gen_report.json', $output, $return);

//open file to return report json 
$genreport_file = fopen("reporting/gen_report.json", "r") or die("Unable to open file!");
$genreport_data =  fread($genreport_file,filesize("reporting/gen_report.json"));
fclose($genreport_file);

//decode report json 
$genreport_data = json_decode($genreport_data, true);

//set new json
$new_genreport_data = [];

//count json data
$genreport_data_count = count($genreport_data);

//count
$count = 1;

$cron_minute = 0;

foreach ($genreport_data as $data) {

    // if have same url, update the array
    if ($data["reportid"] == $report_id) {
        continue;
    }
    else {

        $new_genreport_data[] = array(
            "reportid" => "$count",
            "name" => $data["name"],
            "hostids" => $data["hostids"],
            "usrgrpids" => $data["usrgrpids"],
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

        $newCronEntry .= $cron_gen_time." /software/gen_report.sh ".$cron_reportid."\n";
        $newCronEntry .= $cron_send_time." /software/sendmail_report.sh ".$cron_reportid."\n";

        // add cron minute to 3 minutes each report
        $cron_minute = $cron_minute + 3;

        $count++;
    }

}

$new_genreport_data = json_encode($new_genreport_data, JSON_PRETTY_PRINT);

// echo $new_genreport_data;

$genreport_file = fopen("reporting/gen_report.json", "w") or die("Unable to open file!");
fwrite($genreport_file, $new_genreport_data);
fclose($genreport_file);

//revert permission to files
exec('sudo chmod 644 reporting/gen_report.json', $output, $return);

//go to schedule report page
print '<script>window.location.assign("reporting_sched_list.php");</script>';

//grant permission to files
exec('sudo chmod 777 /var/spool/cron/root', $output, $return);

// Load the updated crontab entries
file_put_contents('/var/spool/cron/root', $newCronEntry);

// revert permission to files
exec('sudo chmod 644 /var/spool/cron/root', $output, $return);

//restart cron service
exec('sudo systemctl restart crond.service', $output, $return);

//display message box Record Been deleted
print '<script>alert("Scheduled report has been deleted.");</script>';
?>