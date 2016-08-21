<?php

namespace kkkdev\CalorieScanner\Util\CommonTrait;

/**
 * 変数でデータをキャッシュするtrait
 */
trait ValueCacheable
{

    private $aResultCacheEachKey = [];

    protected function isExists($sCacheKey)
    {
        if (array_key_exists($sCacheKey, $this->aResultCacheEachKey) and $this->aResultCacheEachKey[$sCacheKey]) {
            return true;
        }
        return false;
    }

    protected function getCache($sCacheKey)
    {
        return $this->aResultCacheEachKey[$sCacheKey];
    }

    protected function setCache($sCacheKey, $data)
    {
        $this->aResultCacheEachKey[$sCacheKey] = $data;
    }

    protected function deleteCache($sCacheKey)
    {
        unset($this->aResultCacheEachKey[$sCacheKey]);
    }
}
