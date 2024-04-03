<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
						
						$tableData = "";
						$params = array(
                        "output" => array("hostid", "name", "status"),
                        "selectGroups" => array("name")
                        );

                        $result = $zbx->call('host.get',$params);
                        foreach ($result as $row) {
                          foreach ($row["groups"] as $group) {
                            $getgroupname = $group["name"];
                          }
                          $gethostname = $row["name"];

                          if ($row["status"] == 0) {
                            $getstatus = "UP";
                          }
                          else {
                            $getstatus = "DOWN";
                          }
            
                          $gethostid = $row["hostid"];

                          if (stripos($gethostname, "windows") !== false OR stripos($gethostname, "ws") !== false OR stripos($getgroupname, "windows") !== false OR stripos($getgroupname, "ws") !== false) {
                            $tableData .= "[";
                            $tableData .= $gethostid.",";
                            $tableData .= $getstatus.",";

                            $params = array(
                            "output" => array("itemid"),
                            "hostids" => $gethostid,
                            "search" => array("name" => "System information")//seach id contains specific word
                            );

                            //call api method
                            $result = $zbx->call('item.get',$params);
                            foreach ($result as $item) {
                              $itemid = $item["itemid"];
                            }

                            if (isset($itemid) == false) {
                              $tableData .= "No data,";
                            }

                            else {
                              $params = array(
                              "output" => array("lastvalue"),
                              "itemids" => $itemid,
                              );
                              //call api method
                              $result = $zbx->call('item.get',$params);
                              foreach ($result as $row) {
                                $sysinfo = $row["lastvalue"];
                                $tableData .= $sysinfo.",";
                              }
                            }

                            $params = array(
                            "output" => array("itemid"),
                            "hostids" => $gethostid,
                            "search" => array("name" => "system uptime")//seach id contains specific word
                            );

                            //call api method
                            $result = $zbx->call('item.get',$params);
                            foreach ($result as $item) {
                              $itemid = $item["itemid"];
                            }

                            if ($itemid == '') {
                              $tableData .= "No data,";
                            }

                            else {
                              $params = array(
                              "output" => array("lastvalue"),
                              "itemids" => $itemid,
                              );
                              //call api method
                              $result = $zbx->call('item.get',$params);
                              foreach ($result as $row) {
                                $updays = $row["lastvalue"];
                                $upsince = time() - $updays;
                                $upsince = date("d-m-Y h:i:s A", $upsince);
                                $tableData .= $upsince.",";
                              }
                            }
                            $tableData .= "],";
                          }
                        }

                        $tableData = json_encode($tableData);
                        print '{'.'"data": ['.$tableData.']'.'}';
?>