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
$app->post('/send', function () use ($app, $validator) {
    /**
     * Receive HTTP Post vars
     */
    $postVars = $app->request->post();
    $city = isset($postVars['city']) ? $postVars['city'] : "";
    $name = isset($postVars['name']) ? $postVars['name'] : "";
    $phone = isset($postVars['phone']) ? $postVars['phone'] : "";
    $email = isset($postVars['email']) ? $postVars['email'] : "";
    $text = isset($postVars['message']) ? $postVars['message'] : "";
    $site = $_SERVER['HTTP_HOST'];
    $time = date("Y-m-d H:i:s");

    /**
     * Create validators
     */
    $lengthValidator = $validator::string()->length(1, 32);
    $emailValidator = $validator::email();
    $phoneValidator = $validator::regex('/^((\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/i');
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
        $message = "
        Отправлено сообщение с сайта #SITE#

        Город: #CITY#
        Имя: #NAME#
        Телефон: #PHONE#
        E-Mail: #EMAIL#
        Сообщение: #MESSAGE#
        Время отправки: #TIME#

        ---

        Сообщение отправлено автоматически.
        ";
        $message = str_replace("#SITE#", $site, $message);
        $message = str_replace("#CITY#", $city, $message);
        $message = str_replace("#NAME#", $name, $message);
        $message = str_replace("#PHONE#", $phone, $message);
        $message = str_replace("#EMAIL#", $email, $message);
        $message = str_replace("#MESSAGE#", $text, $message);
        $message = str_replace("#TIME#", $time, $message);
        mail ('aleks.omelich@gmail.com', 'Заявка с сайта ' . $site , $message);
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