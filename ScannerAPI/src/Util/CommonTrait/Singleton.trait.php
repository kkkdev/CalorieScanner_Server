<?php
namespace kkkdev\CalorieScanner\Util\CommonTrait;

trait Singleton
{
    private static $oInstance = null;

    public static function getInstance()
    {
        if (!self::$oInstance) {
            self::$oInstance = new self();
        }
        return self::$oInstance;
    }
}
