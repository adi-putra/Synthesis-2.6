<?php 
$url = 'http://10.10.200.88:5000/api';
$data = array(
    'key1' => 'value1',
    'key2' => 'value2'
);

$options = array(
    'http' => array(
        'method'  => 'POST',
        'header'  => 'Content-Type: application/json',
        'content' => json_encode($data),
    )
);

$context  = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === false) {
    // Error handling
} else {
    // Process the response
    echo $response;
}
?>