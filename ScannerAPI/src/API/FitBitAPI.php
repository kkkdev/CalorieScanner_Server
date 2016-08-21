<?php
namespace kkkdev\CalorieScanner\API;

require_once __DIR__ . '/MyFitBitAccessProvider.php';
require_once __DIR__ . '/../Auth/FitBitAuthParam.php';

use kkkdev\CalorieScanner\Auth;
use djchen\OAuth2\Client\Provider\Fitbit;
use League\OAuth2\Client\Token\AccessToken;

class FitBitAPI
{
    private $oAccessToken;

    const FITBIT_API_OWN_BASE_URL_TPL = Fitbit::BASE_FITBIT_API_URL . "/1/user/-/%s";
    const FITBIT_API_ENDPOINT_PROFILE = 'profile.json';

    public function __construct(AccessToken $oAccessToken)
    {
        $this->oAccessToken = $oAccessToken;
    }

    public function getFitbitUserID()
    {
        $sAPIURL = sprintf(self::FITBIT_API_OWN_BASE_URL_TPL, self::FITBIT_API_ENDPOINT_PROFILE);
        if ($response = $this->getAPIData($sAPIURL)) {
            return $response['user']['encodedId'];
        }
        return "";
    }

    public function getAPIData($sURL)
    {
        $oProvider = self::getFitbitProvider();
        $request = $oProvider->getAuthenticatedRequest(
        'GET', $sURL, $this->oAccessToken, ['headers' => ['Accept-Language' => 'ja_JP'], ['Accept-Locale' => 'ja_JP']]
    );
// Make the authenticated API request and get the response.
    return $oProvider->getResponse($request);
    }

    public function postAPIData($sURL, $aPostBobyData)
    {
        $oProvider = $this->getFitbitProvider();
        $sQuery = http_build_query($aPostBobyData);
        $request = $oProvider->getAuthenticatedRequest(
        'POST', $sURL . '?' . $sQuery, $this->oAccessToken, ['headers' => ['Accept-Language' => 'en_US'], ['Accept-Locale' => 'en_US'],
        ]
    );
        return $oProvider->getResponse($request);
    }

    public static function getFitbitProvider()
    {
        return new MyFitBitAccessProvider(Auth\FitBitAuthParam::getAuthorizationParams());
    }
}
