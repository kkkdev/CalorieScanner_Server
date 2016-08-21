<?php

namespace kkkdev\CalorieScanner\Parser;

class YahooShoppingSearchDataParser {

    private $aResponse = [];

    public function __construct($sJsonBody) {
        $this->aResponse = json_decode($sJsonBody, true);
        $this->aResponse;
    }

    /**
     * 商品名だけを抽出する
     */
    public function getProductNameList() {
        if(!$a = $this->aResponse){
            return [];
        }
        $a = $a['ResultSet'][0]['Result'];
        $a = array_filter($a, function($v){
            return isset( $v['Name']);
        });
        $a = array_map(function($v){
            return $v['Name'];
        }, $a);;
        return $a;
    }

}
