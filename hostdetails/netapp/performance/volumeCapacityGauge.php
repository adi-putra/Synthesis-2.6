
<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');

//get hostid
$hostid = $_GET["hostid"] ?? array("10713");
$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? strtotime("now");

//display time format
$diff = $timetill - $timefrom;
?>

<!DOCTYPE html>
<html>

    <head>
    <meta charset="utf-8">
    <title></title>

    </head>
    <body>

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

            $params = array(
                "output" => array("itemid","name","lastvalue"),
                "applicationids" => $applicationid,
               
            );

            //call api method
            $volume = $zbx->call('item.get',$params);

            ?>
                        
                <!-- Chart code -->
                <script>
                   
                    am4core.ready(function() {

                        // Themes begin
                        am4core.options.autoDispose = true;
                        am4core.useTheme(am4themes_animated);
                        // Themes end

                        <?php
                            foreach ($volume as $row) {

                                $name = $row['name'];

                                if (strpos($name, 'Volume Total Data Capacity') !== false) {

                                    if(strpos($name, 'Backup') == false && strpos($name, 'Rescan') == false ){

                                        $name = substr($name, 30,-1);
                                        $total_capacity = number_format($row["lastvalue"]/(1024*1024*1024*1024),2); //TB
                            
                                       ?>

                                        // create chart
                                        var chart_<?= $name; ?> = am4core.create("chartdiv_<?= $name; ?>", am4charts.GaugeChart);
                                        chart_<?= $name; ?>.innerRadius = -15;

                                        // chart axis
                                        var axis_<?= $name; ?> = chart_<?= $name; ?>.xAxes.push(new am4charts.ValueAxis());
                                        var total_capacity = <?= $total_capacity ?>;
                                        if(total_capacity == 0){

                                            axis_<?= $name; ?>.min = -1;

                                        }else{

                                            axis_<?= $name; ?>.min = 0;

                                        }
                                       
                                        axis_<?= $name; ?>.max = total_capacity;
                                        axis_<?= $name; ?>.strictMinMax = true;
                                        
                                        //color
                                        var colorSet_<?= $name; ?> = new am4core.ColorSet();
                                        var gradient_<?= $name; ?> = new am4core.LinearGradient();
                                        gradient_<?= $name; ?>.stops.push({color:am4core.color("green")})
                                        gradient_<?= $name; ?>.stops.push({color:am4core.color("yellow")})
                                        gradient_<?= $name; ?>.stops.push({color:am4core.color("red")})

                                        axis_<?= $name; ?>.renderer.line.stroke = gradient_<?= $name; ?>;
                                        axis_<?= $name; ?>.renderer.line.strokeWidth = 15;
                                        axis_<?= $name; ?>.renderer.line.strokeOpacity = 1;
                                        axis_<?= $name; ?>.renderer.grid.template.disabled = true;

                                        var hand_<?= $name; ?> = chart_<?= $name; ?>.hands.push(new am4charts.ClockHand());
                                        hand_<?= $name; ?>.radius = am4core.percent(97);
                                      
                                        //legend
                                        var legend_<?= $name; ?> = new am4charts.Legend();
                                        legend_<?= $name; ?>.isMeasured = false;
                                        legend_<?= $name; ?>.y = am4core.percent(100);
                                        legend_<?= $name; ?>.verticalCenter = "bottom";
                                        legend_<?= $name; ?>.parent = chart_<?= $name; ?>.chartContainer;
                                        legend_<?= $name; ?>.data = [{
                                        "name":'Total Capacity: '+total_capacity +" TB",
                                        "fill": chart_<?= $name; ?>.colors.getIndex(9)
                                        }];
                                    <?php
                                    }
                                }

                                if (strpos($name, 'Volume Used Data Capacity') !== false) {

                                    if(strpos($name, 'Backup') == false && strpos($name, 'Rescan') == false ){

                                        $name = substr($name, 29,-1);
                                        $used_capacity = number_format($row["lastvalue"]/(1024*1024*1024*1024),2); //TB

                                        ?>
                                        setInterval(function() {
                                            hand_<?= $name; ?>.showValue(<?= $used_capacity ?>, am4core.ease.cubicOut);

                                            var used_capacity = <?= $used_capacity ?>;
                                            label_<?= $name; ?>.text = used_capacity.toString()+" TB";
                                        
                                            
                                        }, 6000);

                                       //lable and legend

                                        var labelList = new am4core.ListTemplate(new am4core.Label());
                                        labelList.template.isMeasured = false;
                                        labelList.template.background.strokeWidth = 2;
                                        labelList.template.fontSize = 13;
                                        labelList.template.padding(10, 20, 10, 20);
                                        labelList.template.y = am4core.percent(50);
                                        labelList.template.x = am4core.percent(40);
                                        labelList.template.horizontalCenter = "middle";

                                        var label_<?= $name; ?> = labelList.create();
                                        label_<?= $name; ?>.parent = chart_<?= $name; ?>.chartContainer;
                                        label_<?= $name; ?>.x = am4core.percent(60);
                                        label_<?= $name; ?>.background.stroke = chart_<?= $name; ?>.colors.getIndex(1);
                                        label_<?= $name; ?>.fill = chart_<?= $name; ?>.colors.getIndex(1);
                                        label_<?= $name; ?>.text = "0";
                       
                                       <?php  
                                    }
                                }
                            }
                            ?>
                    }); // end am4core.ready()
                       
                </script>

                <!-- HTML -->
                <div style="overflow-x:auto;">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <?php
                                foreach ($volume as $row) {

                                    $name = $row['name'];

                                    if (strpos($name, 'Volume Total Data Capacity') !== false) {

                                        if(strpos($name, 'Backup') == false && strpos($name, 'Rescan') == false ){

                                            $name = substr($name, 30,-1);
                                ?>
                                <th><?= $name ?></th>
                            <?php
                                }}}
                            ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php
                                foreach ($volume as $row) {

                                    $name = $row['name'];

                                    if (strpos($name, 'Volume Total Data Capacity') !== false) {

                                        if(strpos($name, 'Backup') == false && strpos($name, 'Rescan') == false ){

                                            $name = substr($name, 30,-1);
                                ?>
                                <td><div  id="chartdiv_<?= $name; ?>" style="width: 90%;height: 300px;"></div></td>
                                <?php
                                }}}
                                ?>
                            </tr>
                        </tbody>   
                    </table>
                </div>
    </body>
</html>