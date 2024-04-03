<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . "/synthesis/vendor/autoload.php";

use Orhanerday\OpenAi\OpenAi;
$open_ai_key = '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';
$open_ai = new OpenAi($open_ai_key);
// get prompt parameter

$if_detect = str_replace("Synthesis", "Zabbix", $_GET['prompt']);
// $valid = preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $if_detect);
$prompt = $if_detect;
 

// set api data
$complete = $open_ai->completion([
    'model' => 'text-davinci-003',
    'prompt' => $prompt,
    'temperature' => 0.7,
    'max_tokens' => 200,
    'top_p' => 1,
    'frequency_penalty' => 0.8,
    'presence_penalty' => 0,
    'stream' => true
], function($curl_info, $data){
    // now we will get stream data
    echo $data;
    echo PHP_EOL;
    ob_flush();
    flush();
    return strlen($data);
});
?>

