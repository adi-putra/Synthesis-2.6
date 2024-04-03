<?php

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
// include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
include "session.php";
// Set the name and width of the map

// Set the hostgroup ID that the map will be based on
$usrid = $_GET['usrid'];
$name = $_GET['name'];
$width = $_GET['width'];
$height = $_GET['height'];


    $params = array(
        'output' => 'extend',
        'name' => $name,
        'width' => $width,
        'height' => $height,
        'usrid' => $usrid
    );

    $result = $zbx->call('map.create', $params);
    foreach ($result as $map) {
        $new_mapid = $map[0];
    }

    // print json_encode($result);
    // Print the ID of the new map

    //display message box Record Been Added
    print '<script>alert("User Map successfully added.");</script>';

    //go to map.php page
    print '<script>window.location.assign("map_edit.php?mapid='.$new_mapid.'");</script>';


// print_r($hostgroup_id);

//map.create name-mapname
//map.delete ()-mapid

?>