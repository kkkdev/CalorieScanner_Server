<?php
ini_set("display_errors", 1);
require __DIR__ . "/vendor/autoload.php";

use kkkdev\CalorieScanner\Auth;
use kkkdev\CalorieScanner\API;

//認証系のアダプタークラス
$oFitBitAuthAdapter = new Auth\FitBitAuthAdapter();
$oFitBitAuthAdapter->auth();

//APIアクセスユーティリティ
$oAccessToken = ($oFitBitAuthAdapter->getAccessToken());

$oFitBitAPIUtil = new API\FitBitAPI($oAccessToken);
//FitBitユーザーID取得
$sFitbitUserID = $oFitBitAPIUtil->getFitbitUserID();
//ローカルにアクセストークンを保存
$oFitBitAuthAdapter->saveAccessToken_ByUserID($sFitbitUserID, $oAccessToken);
//認証が無事完了したら、リダイレクト
$oFitBitAuthAdapter->redirect();
exit;
