<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'vendor/autoload.php';
use Respect\Validation\Validator as v;

$validator = new v;
$app = new \Slim\Slim();
/**
 * AJAX Helper for typeahead
 */
$app->get('/city/:name', function ($name) {
    $result = array();
    $cities = file_get_contents(realpath(dirname(__FILE__)) . "/cities.json");
    $cities = json_decode($cities);
    foreach ($cities as $city) {
        if (stripos($city, $name) === 0) {
            $result[] = $city;
        }
    }
    sendResult($result);
});
/**
 * AJAX Form receiver & validator
 */
$app->get('/send', function () use ($app, $validator) {
    /**
     * Receive HTTP Post vars
     */
    $postVars = $app->request->get();
    $city = isset($postVars['city']) ? $postVars['city'] : "";
    $name = isset($postVars['name']) ? $postVars['name'] : "";
    $phone = isset($postVars['phone']) ? $postVars['phone'] : "";
    $email = isset($postVars['email']) ? $postVars['email'] : "";
    $message = isset($postVars['message']) ? $postVars['message'] : "";
    $site = $_SERVER['HTTP_HOST'];
    $time = date("Y-m-d H:i:s");

    /**
     * Create validators
     */
    $lengthValidator = $validator::string()->length(1, 32);
    $emailValidator = $validator::email();
    $phoneValidator = $validator::phone();
    $cyrillicValidator = $validator::regex('/^[А-я ]+$/iu');

    /**
     * Status flag
     */
    $errors = array();

    /**
     * Validate fields
     */
    // city
    if (!$lengthValidator->validate($city) || !$cyrillicValidator->validate($city)) {
        $errors[] = 'city';
    }
    // name
    if (!$lengthValidator->validate($name) || !$cyrillicValidator->validate($name)) {
        $errors[] = 'name';
    }
    // phone
    if (!$phoneValidator->validate($phone)) {
        $errors[] = 'phone';
    }
    // email
    if (!$emailValidator->validate($email)) {
        $errors[] = 'email';
    }
    if (count($errors)) {
        sendResult(array(
            'status' => 'error',
            'fields' => $errors
        ));
    } else {
        sendResult(array(
            'status' => 'ok',
        ));
    }
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