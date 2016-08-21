<?php
namespace kkkdev\CalorieScanner\Auth;

class FitBitAuthParam
{
    //OAuth関係
  const OAUTH_CLIENT_ID = "********";
    const OAUTH_CLIENT_SECRET = "********";
    const OAUTH_CLIENT_REDIRECT_URL = "https://******/api/oauth_landing.php";
    const OAUTH_EXPIRES = 31536000;

    public static function getAuthorizationParams(array $aScope = [])
    {
        $aParams = [
      'clientId' => self::OAUTH_CLIENT_ID,
      'clientSecret' => self::OAUTH_CLIENT_SECRET,
      'redirectUri' => self::OAUTH_CLIENT_REDIRECT_URL,
      'responseType' => "token",
      'expiresIn' => self::OAUTH_EXPIRES,
    ];
        if (count($aScope) > 0) {
            $aParams = array_push($aParams, $aScope);
        }
        return $aParams;
    }
}
