<?php

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
// include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
include "session.php";
// Set the name and width of the map

$width = 1920;
$height = 1090;
// Set the hostgroup ID that the map will be based on
$hostgroupsId = $_GET['groupid'];

// $params = array(
//     'output' => ['groupid']
// );

// $result = $zbx->call('hostgroup.get', $params);
// foreach ($result as $hostgroup) {
//     $hostgroup_id = $hostgroup['groupid'];

    $params = array(
        'output' => 'extend',
        'groupids' => $hostgroupsId
    );
    $hostgroups = $zbx->call('hostgroup.get', $params);
    foreach ($hostgroups as $hostgroup) {
        $hostgroup_name = $hostgroup['name'];

        $params = array(
            'output' => 'extend'
        );
        $maps = $zbx->call('map.get', $params);

        $group_map_exists = false;

        foreach ($maps as $map) {
            if ($map['name'] == $hostgroup_name) {
                $group_map_exists = true;
            }
        }

        if (!$group_map_exists) {
            // Get the hosts in the hostgroup
            $params = array(
                'output' => 'extend',
                'groupids' => $hostgroupsId,
                'selectInterfaces' => 'extend'
            );
            $hosts = $zbx->call('host.get', $params);


            // Create an empty array to hold the map elements
            $selements = array();

            // Loop through each host and create a map element for it
            $i = 1;
            $x = 100;
            $y = 50;

            foreach ($hosts as $host) {

                if ($i % 4 == 1) {
                    $x = 100;
                } else {
                    $x += 400;
                }

                if ($i % 4 == 0) {
                    $y += 250;
                }

                if ($y > $height - 200) {
                    $height += 500;
                }

                $selement = array(
                    'elementtype' => 0, // Host element type
                    'label' => $host['name'],
                    'label_location' => -1,
                    'iconid_off' => 151,
                    'iconid_on' => 0,
                    'x' => $x, // Set the x position of the element (in pixels)
                    'y' => $y, // Set the y position of the element (in pixels)
                    'width' => 200,
                    'height' => 200,
                    'elements' => array(
                        array(
                            'hostid' => $host['hostid']
                        )
                    ),
                    'severity' => 0
                );
                $selements[] = $selement;
                $i++;
            }
            $map_json = json_encode($selements);
        }
    }
    // Encode the new selements array as JSON

    // file_put_contents("sysmap_data.json", $map_json, JSON_PRETTY_PRINT);
    // print_r($map_json);
    // print($hostgroup_name);

    // Create the map using the map.create method
    $params = array(
        'name' => $hostgroup_name,
        'width' => $width,
        'height' => $height,
        'selements' => $selements
    );

    $result = $zbx->call('map.create', $params);
    foreach ($result as $map) {
        $new_mapid = $map[0];
    }

    // print json_encode($result);
    // Print the ID of the new map

    //display message box Record Been Added
    print '<script>alert("Host group map successfully added.");</script>';

    //go to user.php page
    print '<script>window.location.assign("map_display.php?mapid='.$new_mapid.'");</script>';


// print_r($hostgroup_id);

//map.create name-mapname
//map.delete ()-mapid

?>