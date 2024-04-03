<?php
  
  // ini_set('display_errors', 1);
  // ini_set('display_startup_errors', 1);
  // error_reporting(E_ALL);

  include "session.php";

  $timefrom = $_GET['timefrom'] ?? strtotime('today');
  $timetill = $_GET['timetill'] ?? time();

?>
<!DOCTYPE html>
<html>
  <?php include("head.php");?>
  <body class="skin-blue">
    <div class="wrapper">
      
      <?php include("header.php");?>
      
      <?php include('sidebar.php'); ?>

      <!-- Right side column. Contains the navbar and content of the page -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Hosts
            <small>Hosts List</small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Hosts</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
           <div class="col-xs-12">
              <div class="box">
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Interface</th>
                        <th>Availability</th>
                        <th>Group</th>
                        <th style="text-align: center;">Status</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                      $params = array(
                        "output" => array("hostid","name", "status", "available", "ipmi_available", "snmp_available", "jmx_available"),
                        "selectGroups" => array("name"),
                        "selectInterfaces" => array("ip", "port", "type", "available")
                      );

                      $result = $zbx->call('host.get',$params);

                      // echo "<pre>";
                      // print_r($result);
                      // echo "</pre>";
                      //count id from 1 and list all details from result
                      $id = 1;
                      foreach ($result as $v) {

                        $hostid = $v['hostid'];
                        $name = $v['name'];
                        $name = str_replace("Zabbix","Synthesis",$name);
                        $status = $v['status'];
                        $agent_avail = "";
                        $snmp_avail = "";
                        $ipmi_avail = "";
                        $jmx_avail = "";

                        foreach ($v['groups'] as $group) {
                          $groupid = $group['groupid'];
                          $groupname = $group["name"];
                        }
                        
                        print "<tr>
                          <td>$id</td>";

                      //print hostname
                      // $hostlink = hostToLink($hostid, $zbx);
                      print "<td><a href='hostdetails.php?hostid=".$hostid."'>".$name."</a></td>";

                      $ip1 = 0;
                        print "<td>";
                        foreach ($v['interfaces'] as $interface) {
                          $ip = $interface['ip'];
                          $port = $interface['port'];
                          if ($ip1 != $ip) {
                            print "$ip <br>";
                            $ip1 = $ip;
                          }
                        }
                        print "</td>";

                        //Availability
                        print("<td style='text-align: center; padding: 0;'>");
                       
                        foreach ($v['interfaces'] as $interface) {

                          if ($interface["type"] == 1) {
                            $agent_avail = $interface["available"];
                          }

                          if ($interface["type"] == 2) {
                            $snmp_avail = $interface["available"];
                          }

                          if ($interface["type"] == 3) {
                            $ipmi_avail = $interface["available"];
                          }

                          if ($interface["type"] == 3) {
                            $jmx_avail = $interface["available"];
                          }
                        }

                        if ($agent_avail == 1) {
                          print "<button class='btn bg-olive margin' style='margin: 0px;'>AGENT</button>";
                        }
                        else if ($agent_avail == 2) {
                          print "<button class='btn bg-red margin' style='margin: 0px;'>AGENT</button>";
                        }
                        else {
                          print"<button class='btn bg-white margin' style='margin: 0px;'>AGENT</button>";
                        }

                        if ($snmp_avail == 1) {
                          print "<button class='btn bg-olive margin' style='margin: 0px;'>SNMP</button>";
                        }
                        else if ($snmp_avail == 2) {
                          print "<button class='btn bg-red margin' style='margin: 0px;'>SNMP</button>";
                        }
                        else {
                          print "<button class='btn bg-white margin' style='margin: 0px;'>SNMP</button>";
                        }

                        if ($jmx_avail == 1) {
                          print "<button class='btn bg-olive margin' style='margin: 0px;'>JMX</button>";
                        }
                        else if ($jmx_avail == 2) {
                          print "<button class='btn bg-red margin' style='margin: 0px;'>JMX</button>";
                        }
                        else {
                          print "<button class='btn bg-white margin' style='margin: 0px;'>JMX</button>";
                        }

                        if ($ipmi_avail == 1) {
                          print "<button class='btn bg-olive margin' style='margin: 0px;'>IPMI</button>";
                        }
                        else if ($ipmi_avail == 2) {
                          print "<button class='btn bg-red margin' style='margin: 0px;'>IPMI</button>";
                        }
                        else {
                          print "<button class='btn bg-white margin' style='margin: 0px;'>IPMI</button>";
                        }

                        //VMWare
                      $params = array(
                        "output" => array("lastvalue"),
                        "hostids" => $hostid,
                        "search" => array("name" => array(
                          'VMware: Status of "DL3xx Production Cluster" cluster',
                          'VMware: hypervisor ping',
                          'VMware: power state'
                        )),
                        "searchByAny" => true
                        );
                        //call api method
                        $result = $zbx->call('item.get',$params);
                        if (!empty($result)) {
                          foreach ($result as $item) {
                            $statusvm = $item["lastvalue"];
                          }
                          if($statusvm == 1){
                            print "<button class='btn bg-olive margin' style='margin: 0px;'>VM</button>";
                          }
                          else {
                            print "<button class='btn bg-red margin' style='margin: 0px;'>VM</button>";
                          }
                        }
                        else {
                          print "<button class='btn bg-white margin' style='margin: 0px;'>VM</button>";
                        }
                        
                        print "</td>";

                        
                        //group name
                        print "<td>";
                        foreach ($v['groups'] as $group) {
                          $groupname = str_replace("Zabbix","Synthesis",$group["name"]);
                          print "$groupname<br>"; 
                        }
                        print "</td>";
                        
                        if ($status == 0) {
                          print "<td><button class='btn btn-block btn-success btn-sm'>Enabled</button></td></tr>";
                        }
                        else {
                          print "<td><button class='btn btn-block btn-danger btn-sm'>Disabled</button></td></tr>";
                        };

                        $id = $id + 1;
                      } 
                      ?>                 
                    </tbody>
                  </table>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div><!-- /.col -->
          </div><!-- /.row -->
        </section><!-- /.content -->
      <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
        
        </div><!-- /.content-wrapper -->
      <?php include("footer.php");?>

    </div><!-- ./wrapper -->
    
    <!-- page script -->
    <script type="text/javascript">
      $(function () {
        $("#example1").dataTable();
      });
    </script>

<?php
$zbx->logout();//logout from zabbix API
?>

  </body>
</html>