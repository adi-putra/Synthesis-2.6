<?php
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
//get hostid
$hostid = $_GET['hostid'];

//get hostname
$params = array(
	"output" => array("name"),
	"hostids" => $hostid,
	"selectInterfaces"
	);
//call api method
$result = $zbx->call('host.get',$params);
foreach ($result as $host) {
	$hostname = $host["name"];
}

//for seconds to time
function secondsToTime($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

//format value to bytes
function formatBytes($bytes, $precision = 2) { 
	$units = array('B', 'KB', 'MB', 'GB', 'TB'); 

	$bytes = max($bytes, 0); 
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
	$pow = min($pow, count($units) - 1); 

	// Uncomment one of the following alternatives
	$bytes /= pow(1024, $pow);
	// $bytes /= (1 << (10 * $pow)); 

	return round($bytes, $precision) . ' ' . $units[$pow]; 
} 
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title></title>
</head>
<body>

  <div style="overflow-x:auto;">

    <table id="volume_table" class="table table-bordered table-striped">
      <?php

          $params = array(
            "output" => array("name","lastvalue"),
            "hostids" => $hostid
          );
          //call api method
          $result = $zbx->call('application.get',$params);

          foreach($result as $data){

            if($data['name'] == 'Volume API'){

              $applicationid = $data['applicationid'];

            }
          }
        
          //Autosupport Failed Sends
          $params = array(
                            "output" => array("itemid","name","lastvalue"),
                            "applicationids" => $applicationid,
                            
                          );
          //call api method
          $volume = $zbx->call('item.get',$params);

          // echo "<pre>";
          // print_r($volume);
          // echo "</pre>";
         
          //tbl head
          print'<thead>';
          print'<tr>';
              print'<th>Details/Name</th>';
              foreach ($volume as $row) {

                  $name = $row['name'];
                  $value = $row["lastvalue"];
          
                  if (strpos($name, 'Volume Used Data Capacity') !== false) {

                      $name = substr($name, 29 ,-1);
                      print "<th>$name</th>";
                  }
              }
          print'</tr>';
          print '</thead>';


          //Volume Total Data Capacity
          print "<tr>";   
          print "<td><b>Volume Total Data Capacity(GB)<b></td>"; 
          foreach ($volume as $row) {

            $name = $row['name'];
            $value = $row["lastvalue"];

            if (strpos($name, 'Volume Total Data Capacity') !== false) {
              
              $value = number_format($value/(1024*1024*1024),2);
              print "<td>$value GB</td>";
            
            }
          }
          print "</tr>";
          
          //Volume Used Data Capacity
          print "<tr>";   
          print "<td><b>Volume Used Data Capacity(GB)<b></td>"; 
          foreach ($volume as $row) {

            $name = $row['name'];
            $value = $row["lastvalue"];

            if (strpos($name, 'Volume Used Data Capacity') !== false) {
              
              $value = number_format($value/(1024*1024*1024),2);
              print "<td>$value GB</td>";
            
            }
          }
          print "</tr>";
          

          //Volume Used Data Percentage
          print "<tr>";
          print "<td><b>Volume Used Data Percentage(%)</b></td>";
          foreach ($volume as $row) {

            $name = $row['name'];
            $value = $row["lastvalue"];

            if (strpos($name, 'Volume Used Data Percentage') !== false) {

               print "<td>$value %</td>";
    
            }
          }
          print "</tr>";

      ?>
    </table>
  </div>

  <!-- page script -->
  <script type="text/javascript">
    $(function () {
      $("#volume_table").dataTable(
        {
            dom: 'Bfrtip',
            buttons: [
                'colvis'
            ]
        } 
      );
    });
  </script>
</body>
</html>