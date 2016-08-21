<?php

ini_set("display_errors", 1);
error_reporting(E_ALL);
require __DIR__ . "/vendor/autoload.php";

use kkkdev\CalorieScanner\Auth;
use kkkdev\CalorieScanner\Service;
use djchen\OAuth2\Client\Provider\Fitbit;

$app = new \Slim\App();

$app->get('/search/{jan}', function ($request, $response, $args) {
    $sJanCode = $request->getAttribute('jan');
    $o = new Service\CalorieSearchService();
    $aResult = ["result" => $o->searchCandidateByJanCode($sJanCode)];
    $response->withJson($aResult);
    return $response;
});
$app->run();
