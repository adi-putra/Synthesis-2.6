<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$gen_report = 1;

include "session.php";

use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Exception\NavigationExpired;

require __DIR__ . '/vendor/autoload.php';

// $report_schedule = $_GET["report_schedule"] ?? 0;

$report_id = $_GET["report_id"];

// $timefrom = strtotime("-1 month");
// $timetill = strtotime("now");

// replace default 'chrome' with 'chromium-browser'
$browserFactory = new BrowserFactory();

// starts headless chrome
$browser = $browserFactory->createBrowser([
    "enableImages" => true,
    // "keepAlive" => true,
    "startupTimeout" => 60,
    // 'connectionDelay' => 0.8,            // add 0.8 second of delay between each instruction sent to chrome,
    // 'debugLogger'     => 'php://stdout', // will enable verbose mode
    // "headless" => false
]);

//open file to return report json 
$genreport_file = fopen("reporting/gen_report.json", "r") or die("Unable to open file!");
$genreport_data =  fread($genreport_file,filesize("reporting/gen_report.json"));
fclose($genreport_file);

//decode report json 
$genreport_data = json_decode($genreport_data, true);

// creates a new page and navigate to an URL
$page = $browser->createPage();
$page->setViewport(1920, 1080)->await(); // wait for the operation to complete
$navigation = $page->navigate("http://localhost/synthesis/login_exec.php?username=$zabUser&password=$zabPassword");

try {
    $navigation->waitForNavigation(Page::NETWORK_IDLE, 20000);
} catch (OperationTimedOut $e) {
    $navigation->waitForNavigation();
}

//loop and use google headless to generate report
$count = 0;
$hostArr = "";
foreach ($genreport_data as $data) {

    if ($data["reportid"] == $report_id) {

        // check schedule
        if ($data["period"] == 1) {
            $timefrom = strtotime("-1 day");
            $timetill = strtotime("now");
        }
        else if ($data["period"] == 2) {
            $timefrom = strtotime("-1 week");
            $timetill = strtotime("now");
        }
        else {
            $timefrom = strtotime("-1 month");
            $timetill = strtotime("now");
        }

        //hostids
        if (!empty($data["hostids"])) {
            for ($i=0; $i < count($data["hostids"]); $i++) { 
                $hostArr .= "hostid[]=" . $data["hostids"][$i] . "&";
            }
        }

        $timerange = "&timefrom=$timefrom&timetill=$timetill";

        // email properties
        $report_start = date("d/m/Y h:i:s A", $timefrom);
        $report_end = date("d/m/Y h:i:s A", $timetill);
        $genreport_data[$count]["report_start"] = $report_start;
        $genreport_data[$count]["report_end"] = $report_end;

        // generate report
        $rep_name = str_replace(' ', '%20', $data["name"]);
        $dataurl = $data["url"]."&".$hostArr.$timerange."&gen_report=1&reportname=".$rep_name;
        // $command = "google-chrome --headless --no-sandbox --disable-gpu '$dataurl'";

        // print $command;

        // die();

        $page->navigate($dataurl)->waitForNavigation(Page::NETWORK_IDLE, 60000);

        // $page->screenshot()->saveToFile('output.png');

        // die();

        // $page->mouse()->find('#genreport_btn')->click();
        // $page->screenshot()->saveToFile('output.png');
        sleep(10);

        // $page->screenshot()->saveToFile('output.png');

        $genreport_data[$count]["report_generateon"] = date(DATE_RFC1036);
    }

    $count++;
}

// print json_encode($genreport_data);

//grant permission to files
exec('sudo chmod 777 reporting/gen_report.json', $output, $return);

$new_genreport_data = json_encode($genreport_data, JSON_PRETTY_PRINT);

// echo $new_genreport_data;

//write to gen report json file
$genreport_file = fopen("reporting/gen_report.json", "w+") or die("Unable to open file!");
fwrite($genreport_file, $new_genreport_data);
fclose($genreport_file);


// revert permission to files
exec('sudo chmod 644 reporting/gen_report.json', $output, $return);

$browser->close();

?>