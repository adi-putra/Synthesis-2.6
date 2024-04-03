<?php

include "session.php";

$hostid = $_GET["hostid"];

if ($hostid == "") {
    echo '<script language="javascript">';
    echo 'alert("No host ID passed.");';
    echo 'history.back();';
    echo '</script>';
}
else {
    $params = array(
        "output" => array("name"),
        "hostids" => $hostid,
        "selectGroups" => array("name"),
    );
    //call api problem.get only to get eventid
    $hosttolink = $zbx->call('host.get',$params);
    foreach ($hosttolink as $htl) {
    
        $h_name = $htl["name"];
    
        foreach ($htl["groups"] as $htl_g) {
            if (
                stripos($htl_g["name"], 'linux') !== false || 
                stripos($htl_g["name"], 'zabbix') !== false || 
                stripos($htl_g["name"], 'synthesis') !== false || 
                stripos($htl_g["name"], 'sql') !== false || 
                stripos($htl_g["name"], 'windows') !== false || 
                stripos($htl_g["name"], 'firewall') !== false ||
                stripos($htl_g["name"], 'esx') !== false || 
                stripos($htl_g["name"], 'ilo') !== false || 
                stripos($htl_g["name"], 'imm') !== false || 
                stripos($htl_g["name"], 'switch') !== false || 
                stripos($htl_g["name"], 'vm') !== false || 
                stripos($htl_g["name"], 'templates') !== false || 
                stripos($htl_g["name"], 'ups') !== false || 
                stripos($htl_g["name"], 'qnap') !== false || 
                stripos($htl_g["name"], 'vmware hypervisor') !== false || 
                stripos($htl_g["name"], 'vmware group') !== false ||
                stripos($htl_g["name"], 'vcenter') !== false ||
                stripos($htl_g["name"], 'netapp') !== false
                ) {
                  $g_name = $htl_g["name"];
                }
        }
    
        if (stripos($g_name, 'linux') !== false || stripos($g_name, 'zabbix') !== false || stripos($g_name, 'synthesis') !== false) {
            $link = 'hostdetails_linux.php?hostid='.$hostid;
        }
        else if (stripos($g_name, 'firewall') !== false || stripos($g_name, 'fw') !== false) {
            $link = 'hostdetails_firewall.php?hostid='.$hostid;
        }
        else if (stripos($g_name, 'esx') !== false) {
            $link = 'hostdetails_esx.php?hostid='.$hostid;
        }
        else if (stripos($g_name, 'ups') !== false) {
            $link = 'hostdetails_ups.php?hostid='.$hostid;
        }
        else if (stripos($g_name, 'switch') !== false) {
            $link = 'hostdetails_switches.php?hostid='.$hostid;
        }
        else if (stripos($g_name, 'ilo') !== false) {
            $link = 'hostdetails_ilo.php?hostid='.$hostid;
        }
        else if (stripos($g_name, 'imm') !== false) {
            $link = 'hostdetails_imm.php?hostid='.$hostid;
        }
        else if (stripos($g_name, 'vm') !== false || stripos($g_name, 'synthesis - vmware group') !== false) {
            $link = 'hostdetails_vm.php?hostid='.$hostid;
        }
        else if (stripos($g_name, 'synthesis - vmware hypervisor group') !== false) {
            $link = "hostdetails_hypervisor.php?hostid=".$hostid;
        }
        else if (stripos($g_name, 'vcenter') !== false) {
            $link = "hostdetails_vcenter.php?hostid=".$hostid;
        }
        else if (stripos($g_name, 'netapp') !== false) {
            $link = "hostdetails_netapp.php?hostid=".$hostid;
        }
        else if (stripos($g_name, 'qnap') !== false) {
            $link = "hostdetails_qnap.php?hostid=".$hostid;
        }
        else if (stripos($g_name, 'windows') !== false || stripos($g_name, 'ws') !== false || stripos($g_name, 'sql') !== false) {
            $link = "hostdetails_windows.php?hostid=".$hostid;
        }

        if ($link == "") {
            echo '<script language="javascript">';
            echo 'alert("No module for this host.");';
            echo 'history.back();';
            echo '</script>';
        }
        else {
            echo '<script language="javascript">';
            echo 'location.assign("'.$link.'");';
            echo '</script>';
        }
    }
}

?>