<?php

ini_set("display_errors", 1);
require __DIR__ . "/vendor/autoload.php";

/**
 * 認証
 * 
 *  */
use kkkdev\CalorieScanner\Auth;
use kkkdev\CalorieScanner\API;
use djchen\OAuth2\Client\Provider\Fitbit;

$app = new \Slim\App();
$app->get('/auth', function ($request, $response, $args) {
//認証系のアダプタークラス
    $oFitBitAuthAdapter = new Auth\FitBitAuthAdapter();
    $oFitBitAuthAdapter->setRedirectURL("http://dbf2at4towcoe.cloudfront.net/api/auth.php/view");
    $oFitBitAuthAdapter->auth();

//既に認証済だったら(=authメソッドでリダイレクトされなかったら) 、リダイレクト先に直接飛ぶ
    $oFitBitAuthAdapter->redirect();
    exit;
});

$app->get('/view', function ($request, $response, $args) {
    $oFitBitAuthAdapter = new Auth\FitBitAuthAdapter();
    $key = "hogehoge";
    echo $sUserID = $oFitBitAuthAdapter->getUserID();
exit;
    //API
    $oFitBitAPIUtil = new API\FitBitAPI($oFitBitAuthAdapter->getAccessToken());
    $sURL = Fitbit::BASE_FITBIT_API_URL . "/1/foods/search.json?query=ブルボン";
    try {
        $res = $oFitBitAPIUtil->getAPIData($sURL);
    } catch (Exception $e) {
        var_export($e);
        exit;
    }
    var_export($res);exit;
});
$app->run();
