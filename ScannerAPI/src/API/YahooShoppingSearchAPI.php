<?php

namespace kkkdev\CalorieScanner\API;

class YahooShoppingSearchAPI
{

    const API_URL_TPL = 'http://shopping.yahooapis.jp/ShoppingWebService/V1/json/itemSearch?appid=%s';
    const MY_APP_ID = '********************************';

    public static function getURL_SearchByJanCode($sJanCode = "")
    {
        if (!$sJanCode) {
            return "";
        }
        return sprintf(self::API_URL_TPL, self::MY_APP_ID) . "&jan=" . $sJanCode;
    }
}
