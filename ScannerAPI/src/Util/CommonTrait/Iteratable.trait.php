<?php

namespace kkkdev\CalorieScanner\Util\CommonTrait;

trait Iteratable
{
    protected $aIteratableList = [];

    public function hasNext()
    {
        $bHasNext = !!current($this->aIteratableList);
        if (!$bHasNext) {
            reset($this->aIteratableList);
        }
        return $bHasNext;
    }

    public function getNext()
    {
        $oObj = null;
        if ($this->hasNext()) {
            $oObj = current($this->aIteratableList);
            next($this->aIteratableList);
        }
        return $oObj;
        ;
    }
}
