<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$gen_report = 1;

include "session.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

//check file output.pdf if exist, delete it
$output_pdffile = "output.pdf";

if (file_exists($output_pdffile)) {
    unlink($output_pdffile);
}

$mail = new PHPMailer(true); 

// $report_schedule = $_GET["report_schedule"];

$report_id = $_GET["report_id"];

//open file to return report json 
$genreport_file = fopen("reporting/gen_report.json", "r") or die("Unable to open file!");
$genreport_data =  fread($genreport_file,filesize("reporting/gen_report.json"));
fclose($genreport_file);

//decode report json 
$genreport_data = json_decode($genreport_data, true);

// get synthesis smtp server
//fetch smtp
$params = array(
    "output" => "extend",
    "search" => array("name" => "Email SMTP")
);
$result = $zbx->call('mediatype.get',$params);
foreach ($result as $mediatype) {
    $mediatype_id = $mediatype["mediatypeid"];
    $mediatype_name = $mediatype["name"];
    $mediatype_type = $mediatype["type"];
    $mediatype_smtpserver = $mediatype["smtp_server"];
    $mediatype_smtphelo = $mediatype["smtp_helo"];
    $mediatype_smtpemail = $mediatype["smtp_email"];
    $mediatype_smtpport = $mediatype["smtp_port"];
    $mediatype_smtpauth = $mediatype["smtp_authentication"];
    $mediatype_smtpusername = $mediatype["username"];
    $mediatype_smtppassword = $mediatype["passwd"];
    $mediatype_status = $mediatype["status"];
    $mediatype_smtp_security = $mediatype["smtp_security"];
    $mediatype_smtp_verify_peer = $mediatype["smtp_verify_peer"];
    $mediatype_smtp_verify_host = $mediatype["smtp_verify_host"];
}

//config SMTP values
//smtp auth
if ($mediatype_smtpauth == 1) {
    $mediatype_smtpauth = true;
}
else {
    $mediatype_smtpauth = false;
}

//smtp security
if ($mediatype_smtp_security == 0) {
    $mediatype_smtp_security = "";
}
else if ($mediatype_smtp_security == 1) {
    $mediatype_smtp_security = "tls";
}
else if ($mediatype_smtp_security == 2) {
    $mediatype_smtp_security = "ssl";
}

//set up name appear in email
$mediatype_smtpusername = substr($mediatype_smtpemail, 0, strpos($mediatype_smtpemail, '@'));

$mail = new PHPMailer(true);

//Enable SMTP debugging.
$mail->SMTPDebug = 0;                               
//Set PHPMailer to use SMTP.
$mail->isSMTP();            
$mail->SMTPKeepAlive = true;
//Set SMTP host name                          
$mail->Host = $mediatype_smtpserver;
//Set this to true if SMTP host requires authentication to send email
$mail->SMTPAuth = $mediatype_smtpauth;                          
//Provide username and password     
$mail->Username = $mediatype_smtpemail;                
$mail->Password = $mediatype_smtppassword;                           
//If SMTP requires TLS encryption then set it
$mail->SMTPAutoTLS = false;
$mail->SMTPSecure = $mediatype_smtp_security;                           
//Set TCP port to connect to
$mail->Port = $mediatype_smtpport;                                   

$mail->From = $mediatype_smtpemail;
$mail->FromName = $mediatype_smtpusername;

$mail->isHTML(true);

$count = 0;
foreach ($genreport_data as $data) {

    if ($data["reportid"] == $report_id) {
        
        //schedule properties
        $rep_name = $data["name"];
        $rep_start = $data["report_start"];
        $rep_end = $data["report_end"];
        $rep_generateon = $data["report_generateon"];

        if ($data["schedule"] == 1) {
            $rep_schedule = "Daily";
        }
        else if ($data["schedule"] == 2) {
            $rep_schedule = "Weekly";
        }
        else {
            $rep_schedule = "Monthly";
        }

        if ($data["period"] == 1) {
            $rep_period = "Previous day";
        }
        else if ($data["period"] == 2) {
            $rep_period = "Previous week";
        }
        else {
            $rep_period = "Previous month";
        }

        //email properties
        $mail->Subject = "Synthesis - Schedule Reporting for $rep_name ($rep_schedule)";
        $mail->Body = '<table>
                            <tr>
                                <th>Report Name</th>
                                <td>'.$rep_name.'</td>
                            </tr>
                            <tr>
                                <th>Generate On</th>
                                <td>'.$rep_generateon.'</td>
                            </tr>
                            <tr>
                                <th>Range</th>
                                <td>'.$rep_start.' - '.$rep_end.'</td>
                            </tr>
                            <tr>
                                <th>Schedule</th>
                                <td>'.$rep_schedule.'</td>
                            </tr>
                            <tr>
                                <th>Period</th>
                                <td>'.$rep_period.'</td>
                            </tr>
                        </table>';

        // $mail->AltBody = "Synthesis - Test Attachment";
        
        //find attachments
        $files = glob('*'.$data["name"].'.pdf');

        if (!empty($files)) {
            // loop and send report
            for ($i=0; $i < count($files); $i++) {
                try {

                    // print json_encode($data["usrgrpids"])."<br>";
                    // print json_encode($data["userids"])."<br>";
                    
                    //add recipients by user group
                    if (!empty($data["usrgrpids"])) {
                        $params = array(
                        "output" => "extend",
                        "usrgrpids" => $data["usrgrpids"],
                        "selectMedias" => "extend"
                        );
                        $result = $zbx->call('user.get',$params);
                        foreach ($result as $user) {
                            foreach ($user["medias"] as $media) {

                                if (is_array($media["sendto"])) {
                                    $media_email = $media["sendto"][0];
                                }
                                else {
                                    $media_email = $media["sendto"];
                                }
                                
                                //check if user email is disabled by user or not
                                if ($media["active"] == 0) {
                                    //add recipient
                                    if(filter_var($media_email, FILTER_VALIDATE_EMAIL)) {
                                        $mail->addAddress($media_email);
                                    }
                                }
                            }
                        }
                    }

                    if (!empty($data["userids"])) {
                        //add recipients by user
                        $params = array(
                        "output" => "extend",
                        "userids" => $data["userids"],
                        "selectMedias" => "extend"
                        );
                        $result = $zbx->call('user.get',$params);
                        foreach ($result as $user) {
                            foreach ($user["medias"] as $media) {

                                if (is_array($media["sendto"])) {
                                    $media_email = $media["sendto"][0];
                                }
                                else {
                                    $media_email = $media["sendto"];
                                }
                                
                                //check if user email is disabled by user or not
                                if ($media["active"] == 0) {
                                    //add recipient
                                    if(filter_var($media_email, FILTER_VALIDATE_EMAIL)) {
                                        $mail->addAddress($media_email);
                                    }
                                }
                            }
                        }
                    }

                    //check if recipients is not empty, send email
                    $mail_recipients = $mail->getAllRecipientAddresses();
                    
                    if (!empty($mail_recipients)) {

                        //attach pdf report
                        $mail->addAttachment($files[$i]);
            
                        $mail->send();
            
                        // Clear all addresses and attachments for the next iteration
                        // $mail->clearAddresses();
                        // $mail->clearAttachments();
                    }

                    unset($genreport_data[$count]['report_start']);
                    unset($genreport_data[$count]['report_end']);
                    unset($genreport_data[$count]['report_generateon']);
        
                    unlink($files[$i]);
                    // echo "Report has been sent successfully!";
                }
                catch (Exception $e) {

                    unset($genreport_data[$count]['report_start']);
                    unset($genreport_data[$count]['report_end']);
                    unset($genreport_data[$count]['report_generateon']);
        
                    unlink($files[$i]);

                    echo "Report could not be sent. Error: {$mail->ErrorInfo}";
                }

                //Clear all addresses and attachments for the next iteration
                $mail->clearAddresses();
                $mail->clearAttachments();

                unset($genreport_data[$count]['report_start']);
                unset($genreport_data[$count]['report_end']);
                unset($genreport_data[$count]['report_generateon']);
    
                unlink($files[$i]);
            }
        }
        else {
            // echo no files is generated.
            echo "No file has been found/generated. Please contact support.";
        }
    }

    $count++;
}

// print json_encode($mail_recipients)."<br>";

$mail->SmtpClose();

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

// $browser->close();

?>