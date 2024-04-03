<?php 
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$dataURI = $_POST["pdfbase"];

$filename = $_POST["name"].".pdf";

$base64_decode = base64_decode($dataURI);

$pdf = fopen($filename, 'w');

fwrite($pdf, $base64_decode);
?>