<?php
$number = $_GET["number"];
$arr1 = array("January","February","March","April","May","June","July","August","September","October","November","December");
$arr = array_reverse($arr1);
$addzero = "";

if (strlen($number) != 12) {
  $minus = 12 - strlen($number);
  for ($i=0; $i < $minus; $i++) { 
    $addzero .= "0";
  }
  $search2 = $addzero.$number;
}
else {
  $search2 = $number;
}

$search_arr = str_split($search2);
$out2 = array();
foreach($search_arr as $key => $value){
    if($value == 1){
        $out2[] = $arr[$key];
    }
}

echo "(".implode(", ", array_reverse($out2)).")";//January, February
//return $search2;
?>