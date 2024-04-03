<?php
//Author: Adiputra
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

$license_text = $_POST["license_text"];

if ($license_text == "") {
    echo '<script language="javascript">';
    echo 'alert("Process aborted! License text not pasted.");';
    echo 'location.assign("license_check.php");';
    echo '</script>';
}
else if (strlen($license_text) < 200) {
    echo '<script language="javascript">';
    echo 'alert("Process aborted! Incorrect license text. Please paste a valid license text.");';
    echo 'location.assign("license_check.php");';
    echo '</script>';
}
else {
    //LIC FILE UPDATE

    //grant permissions to write file
    exec('sudo chmod 777 lic/lic.txt', $output, $return);

    //open file
    $licfile = fopen("lic/lic.txt", "w+") or die("Unable to open file!");

    //write file
    fwrite($licfile, $license_text);

    //close file
    fclose($licfile);

    //revert permissions to write file
    exec('sudo chmod 755 lic/lic.txt', $output, $return);
    //DONE LIC FILE

    echo '<script language="javascript">';
    //echo 'alert("Successfuly aborted! License text not pasted.");';
    echo 'location.assign("license_check.php");';
    echo '</script>';
}
?>