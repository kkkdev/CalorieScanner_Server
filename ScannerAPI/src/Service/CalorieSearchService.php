<?php

namespace kkkdev\CalorieScanner\Service;

ini_set("display_errors", 1);
require __DIR__ . "/../../vendor/autoload.php";

use kkkdev\CalorieScanner\Parser;
use kkkdev\CalorieScanner\API;
use kkkdev\CalorieScanner\Auth;
use kkkdev\CalorieScanner\Util;
use djchen\OAuth2\Client\Provider\Fitbit;
use GuzzleHttp;

class CalorieSearchService {

    const FITBIT_USER_ID = "4LPH24";
    const SEARCH_WORD_LIMIT = 3;

    private $oYahooShoppingSearchDataParser;

    public function __construct() {
        
    }

    /** 候補を検索 */
    public function searchCandidateByJanCode($sJanCode = "") {
        $aResult = [];
        if (!$sJanCode) {
            return [];
        }
        if (!$aProductNameList = $this->getSearchProductNameList($sJanCode)) {
            return [];
        }
        if (!$sCalorieSearchWord = $this->makeCalorieSearchWord($aProductNameList)) {
            return [];
        }
        $aResult = $this->getCalorieSearchResult($sCalorieSearchWord);
        return $aResult;
    }

    private function getSearchProductNameList($sJanCode) {
        $sURL = API\YahooShoppingSearchAPI::getURL_SearchByJanCode($sJanCode);
        //自前Promiseは要勉強
        $client = new GuzzleHttp\Client();
        $response = $client->get($sURL);
        $this->oYahooShoppingSearchDataParser = new Parser\YahooShoppingSearchDataParser($response->getBody());
        return $this->oYahooShoppingSearchDataParser->getProductNameList();
    }

    private function makeCalorieSearchWord(Array $aProductNameList) {
        //スペースで区切って単語を抽出
        $a = array_map(function ($sProductName) {
            $sProductName = str_replace(["(", ")", "（", "）"], "", $sProductName);
            $a = array_map("trim", preg_split("/ |　/", $sProductName));
            return $a;
        }, $aProductNameList);
        //1次元の配列にする
        $a = array_reduce($a, function ($aCarry, $aItem) {
            if (!$aCarry) {
                $aCarry = [];
            }
            return array_merge($aCarry, $aItem);
        });
        $a = array_count_values($a);
        arsort($a);
        $a = array_slice($a, 0, self::SEARCH_WORD_LIMIT);
        $sSearchWord = join(" ", array_keys($a));
        return $sSearchWord;
    }

    private function getCalorieSearchResult($sSearchWord) {
        $aResult = [];
        $oFitBitAuthAdapter = new Auth\FitBitAuthAdapter();
        $oFitBitAuthAdapter->setFitbitUserID(self::FITBIT_USER_ID);
        $oAccessToken;
        if (!$oAccessToken = $oFitBitAuthAdapter->getAccessToken()) {
            $oFitBitAuthAdapter->auth();
            $oAccessToken = $oFitBitAuthAdapter->getAccessToken();
        }

        //API
        $oFitBitAPIUtil = new API\FitBitAPI($oAccessToken);
        $sURL = Fitbit::BASE_FITBIT_API_URL . "/1/foods/search.json?query=" . $sSearchWord;

        try {
            $aRes = $oFitBitAPIUtil->getAPIData($sURL);
            foreach ($aRes['foods'] as $i => $aFoods) {
                if (!$sFoodName = &$aFoods['name']) {
                    continue;
                }
                // echo $sSearchWord . " " . $sFoodName . "<br/>";

                $fSim = Util\MultibyteUTF8Levenshtein::levenshtein_normalized_utf8($sSearchWord, $sFoodName);
                $aTmp = [
                    "name" => mb_substr($sFoodName, 0, 18) . "...",
                    "cal" => &$aFoods['calories'],
                    "ratio" => $fSim
                ];
                $aResult[] = $aTmp;
            }
        } catch (Exception $e) {
            
        }
        usort($aResult, function($a, $b) {
            $fRatio_a = &$a["ratio"];
            $fRatio_b = &$b["ratio"];
            return ($fRatio_a > $fRatio_b) ? -1 : 1;
        });
        return $aResult;
    }

}

/**
$o = new CalorieSearchService();
$o->searchCandidateByJanCode("4901360315239");
*/