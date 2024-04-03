<?php
$number = $_GET["number"];
$arr = array('Sunday', 'Saturday', 'Friday', 'Thursday', 'Wednesday', 'Tuesday', 'Monday');
$addzero = "";

if (strlen($number) != 7) {
$minus = 7 - strlen($number);
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

echo "(".implode(", ", array_reverse($out2)).")";//Monday, Wednesday, Friday
//return $search2;
?>