<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


require 'vendor/autoload.php';
$app = new \Slim\Slim();

$app->get('/city/:name', function ($name) {
    $result = array();
    $cities = file_get_contents(realpath(dirname(__FILE__))."/cities.json");
    $cities = json_decode($cities);
    foreach($cities as $city) {
        if (stripos($city, $name) === 0) {
            $result[] = $city;
        }
    }
    sendResult($result);
});
$app->run();

/**
 * Send result to a client in JSON
 * @param $data array Data
 * @return bool
 */
function sendResult($data)
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return false;
}