<?php

namespace kkkdev\CalorieScanner\Util;
/**
マルチバイト(UTF-8)での編集距離算出クラス
 * 
 *  */
class MultibyteUTF8Levenshtein {

    public static function levenshtein_normalized_utf8($s1, $s2, $cost_ins = 1, $cost_rep = 1, $cost_del = 1) {
        $l1 = mb_strlen($s1, 'UTF-8');
        $l2 = mb_strlen($s2, 'UTF-8');
        $size = max($l1, $l2);
        if (!$size) {
            return 0;
        }
        if (!$s1) {
            return $l2 / $size;
        }
        if (!$s2) {
            return $s1 / $size;
        }
        return 1.0 - self::levenshtein_utf8($s1, $s2, $cost_ins, $cost_rep, $cost_del) / $size;
    }

    public static function levenshtein_utf8($s1, $s2, $cost_ins = 1, $cost_rep = 1, $cost_del = 1) {
        $s1 = preg_split('//u', $s1, -1, PREG_SPLIT_NO_EMPTY);
        $s2 = preg_split('//u', $s2, -1, PREG_SPLIT_NO_EMPTY);
        $l1 = count($s1);
        $l2 = count($s2);
        if (!$l1) {
            return $l2 * $cost_ins;
        }
        if (!$l2) {
            return $l1 * $cost_del;
        }
        $p1 = array_fill(0, $l2 + 1, 0);
        $p2 = array_fill(0, $l2 + 1, 0);
        for ($i2 = 0; $i2 <= $l2; ++$i2) {
            $p1[$i2] = $i2 * $cost_ins;
        }
        for ($i1 = 0; $i1 < $l1; ++$i1) {
            $p2[0] = $p1[0] + $cost_ins;
            for ($i2 = 0; $i2 < $l2; ++$i2) {
                $c0 = $p1[$i2] + ($s1[$i1] === $s2[$i2] ? 0 : $cost_rep);
                $c1 = $p1[$i2 + 1] + $cost_del;
                if ($c1 < $c0) {
                    $c0 = $c1;
                }
                $c2 = $p2[$i2] + $cost_ins;
                if ($c2 < $c0) {
                    $c0 = $c2;
                }
                $p2[$i2 + 1] = $c0;
            }
            $tmp = $p1;
            $p1 = $p2;
            $p2 = $tmp;
        }
        return $p1[$l2];
    }

}
