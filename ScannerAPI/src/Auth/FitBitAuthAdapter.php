<?php

namespace kkkdev\CalorieScanner\Auth;

require_once __DIR__ . "/FitBitAuthParam.php";
require_once __DIR__ . "/../API/FitBitAPI.php";
require_once __DIR__ . "/../API/MyFitBitAccessProvider.php";

use djchen\OAuth2\Client\Provider\Fitbit;
use phpFastCache\CacheManager;
use League\OAuth2\Client\Token\AccessToken;
use kkkdev\CalorieScanner\API;

/*
 * Description of FitBitAuthAdapter
 *
 * [要件]
 * ・ユーザーIDをセッションから取り出し、保持
 * ・AccessTokenクラスを取得 / 保存
 * ・ユーザーIDを取得
 * @author kkkdev
 */

class FitBitAuthAdapter
{

    const AUTHINFO_CACHE_PATH = "/var/cache/fitbitAuth";
    const SESSION_FITBIT_USERID_KEY = "FitbitUserID";
    const SESSION_REDIRECT_URL_KEY = "RedirectURL";
    const FILECACHE_LIMIT_SEC = 31536000; //86400 * 365

    private $sFitBitUserID;
    private $oCache;
    private $oAccessTokenTmp = null;

    /**
     * 
     * @param type $sRedirectURL="" 認証後にリダイレクトする際のリダイレクト先
     */
    public function __construct()
    {
        if (!session_id()) {
            session_start();
        }

//ユーザーIDが存在したら(=認証済だったら)、取り出す
        if (isset($_SESSION[self::SESSION_FITBIT_USERID_KEY])) {
            $this->sFitBitUserID = $_SESSION[self::SESSION_FITBIT_USERID_KEY];
        }

//キャッシュクラスを初期化
        $this->initCacheSetting();
    }

    //-------------------------------------------------------------------------- public
    /**
     * 認証
     * 
     * ・トークンがなかったら認証へリダイレクト
     * ・あっても期限切れだったらリフレッシュ
     */
    public function auth()
    {
        //トークンがなかったら認証へリダイレクト
        if (!$this->accessTokenExists()) {
            $this->initOAuth();
        }

//あっても期限切れだったらリフレッシュ
        if ($this->accessTokenExpired()) {
            try {
                $this->refreshToken();
//リフレッシュがうまくいったらreturn
                return;
            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
                //うまくいかなかった場合は、再度作りなおす(もしくは、できてるものをセットし直す)
                $this->initOAuth();
            }
        }
    }

    public function getAccessToken()
    {
        //変数にあったら、それを返す
        if ($this->oAccessTokenTmp) {
            return $this->oAccessTokenTmp;
        }
//ユーザーIDがあったら、キャッシュから取り出してみる
        if ($this->sFitBitUserID) {
            $this->oAccessTokenTmp = CacheManager::get($this->sFitBitUserID);
            return $this->oAccessTokenTmp;
        }
//それでもなかったらカラ
        return null;
    }

    /**
     * アクセストークンとユーザーIDをキーに保存
     * @param type $sFitbitUserID
     * @param AccessToken $oAccessToken
     */
    public function saveAccessToken_ByUserID($sFitbitUserID, AccessToken $oAccessToken)
    {
        $this->oCache->set($sFitbitUserID, $oAccessToken, self::FILECACHE_LIMIT_SEC);
        $_SESSION[self::SESSION_FITBIT_USERID_KEY] = $sFitbitUserID;
    }

    public function getUserID()
    {
        return $this->sFitBitUserID;
    }

    /**
     * FitBitユーザーIDをセット
     *  */
    public function setFitbitUserID($sFitbitUserID)
    {
        $this->sFitBitUserID = $sFitbitUserID;
    }

    public static function redirect()
    {
        if (isset($_SESSION[self::SESSION_REDIRECT_URL_KEY])) {
            header("Location:" . $_SESSION[self::SESSION_REDIRECT_URL_KEY]);
            exit;
        }
    }

    public static function getRequestURL()
    {
        $tmp = &$_SERVER['HTTPS'];
        $sProtocol = ($tmp and $tmp == 'on') ? 'https' : 'http';
        return sprintf("%s://%s%s", $sProtocol, $_SERVER["SERVER_NAME"], $_SERVER['REQUEST_URI']);
    }

    public function setRedirectURL($sRedirectURL = "")
    {
        $_SESSION[self::SESSION_REDIRECT_URL_KEY] = $sRedirectURL;
    }

    //-------------------------------------------------------------------------- private

    /**
     * キャッシュクラスを初期化
     *  */
    private function initCacheSetting()
    {
        CacheManager::setup(["path" => self::AUTHINFO_CACHE_PATH]);
        CacheManager::CachingMethod("phpfastcache");
        if ($oCache = CacheManager::Files()) {
            $this->oCache = $oCache;
        }
        //apache実行ユーザー以外の場合はreturn
        if (trim(`whoami`) != "daemon") {
            return;
        }
        $sCommand = sprintf("chmod -R 0777 %s", self::AUTHINFO_CACHE_PATH);
        `{$sCommand}`;
    }

    private function accessTokenExists()
    {
        if ($oAccessToken = CacheManager::get($this->sFitBitUserID)) {
            return true;
        }
        return false;
    }

    private function accessTokenExpired()
    {
        $oAccessToken = CacheManager::get($this->sFitBitUserID);
        if (!$oAccessToken) {
            return true;
        }
        return $oAccessToken->hasExpired();
    }

    private function initOAuth()
    {
        $provider = API\FitBitAPI::getFitbitProvider();
        if (!isset($_GET['access_token'])) {
            $sAuthorizationURL = $provider->getAuthorizationUrl(FitBitAuthParam::getAuthorizationParams());
// Get the state generated for you and store it to the session.
            $_SESSION['oauth2state'] = $provider->getState();
            //認証先(FitBitサーバー).
            header('Location:' . $sAuthorizationURL);
            exit;
        } else {
            try {
                $aParam = $_GET;
                $aParam["refresh_token"] = "a3ef67"; //ダミー(Implicit grantを使うので不要)
                $oAccessToken = new AccessToken($aParam);
                $this->oAccessTokenTmp = $oAccessToken;
            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
                exit($e->getMessage());
            }
        }
    }

//http://oauth2-client.thephpleague.com/usage/
    private function refreshToken()
    {
        //リフレッシュは一旦捨てる
        $provider = API\FitBitAPI::getFitbitProvider();
        $oAccessToken_new = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $this->getAccessToken()->getRefreshToken()
        ]);
        $this->oAccessTokenTmp = $oAccessToken_new;
    }
}
