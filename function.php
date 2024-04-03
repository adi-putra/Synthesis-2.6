<?php
// functions
function hostToLink($h_id, $zbx) {
    $params = array(
        "output" => array("name"),
        "hostids" => $h_id,
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
                stripos($htl_g["name"], 'vmware hypervisor') !== false || 
                stripos($htl_g["name"], 'vmware group') !== false ||
				stripos($htl_g["name"], 'vcenter') !== false ||
				stripos($htl_g["name"], 'netapp') !== false ||
				stripos($htl_g["name"], 'qnap') !== false
				) {
                  $g_name = $htl_g["name"];
                }
        }

        if (stripos($g_name, 'linux') !== false || stripos($g_name, 'zabbix') !== false || stripos($g_name, 'synthesis') !== false) {
			$link = '<a href="hostdetails_linux.php?hostid='.$h_id.'">'.$h_name.'</a>';
		}
		else if (stripos($g_name, 'firewall') !== false || stripos($g_name, 'fw') !== false) {
			$link = '<a href="hostdetails_firewall.php?hostid='.$h_id.'">'.$h_name.'</a>';
		}
		else if (stripos($g_name, 'esx') !== false) {
			$link = '<a href="hostdetails_esx.php?hostid='.$h_id.'">'.$h_name.'</a>';
		}
		else if (stripos($g_name, 'ups') !== false) {
			$link = '<a href="hostdetails_ups.php?hostid='.$h_id.'">'.$h_name.'</a>';
		}
		else if (stripos($g_name, 'switch') !== false) {
			$link = '<a href="hostdetails_switches.php?hostid='.$h_id.'">'.$h_name.'</a>';
		}
		else if (stripos($g_name, 'ilo') !== false) {
			$link = '<a href="hostdetails_ilo.php?hostid='.$h_id.'">'.$h_name.'</a>';
		}
		else if (stripos($g_name, 'imm') !== false) {
			$link = '<a href="hostdetails_imm.php?hostid='.$h_id.'">'.$h_name.'</a>';
		}
		else if (stripos($g_name, 'vm') !== false || stripos($g_name, 'synthesis - vmware group') !== false) {
			$link = '<a href="hostdetails_vm.php?hostid='.$h_id.'">'.$h_name.'</a>';
		}
		else if (stripos($g_name, 'synthesis - vmware hypervisor group') !== false) {
			$link = '<a href="hostdetails_hypervisor.php?hostid='.$h_id.'">'.$h_name.'</a>';
		}
		else if (stripos($g_name, 'vcenter') !== false) {
			$link = '<a href="hostdetails_vcenter.php?hostid='.$h_id.'">'.$h_name.'</a>';
		}
		else if (stripos($g_name, 'netapp') !== false) {
			$link = '<a href="hostdetails_netapp.php?hostid='.$h_id.'">'.$h_name.'</a>';
		}
		else if (stripos($g_name, 'qnap') !== false) {
			$link = '<a href="hostdetails_qnap.php?hostid='.$h_id.'">'.$h_name.'</a>';
		}
		else if (stripos($g_name, 'windows') !== false || stripos($g_name, 'ws') !== false || stripos($g_name, 'sql') !== false) {
			$link = '<a href="hostdetails_windows.php?hostid='.$h_id.'">'.$h_name.'</a>';
		}
		else {
			$link = $h_name;
		}

        return $link;
    }
}
?>