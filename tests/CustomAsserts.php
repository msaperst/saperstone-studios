<?php

use PHPUnit\Framework\Assert;

class CustomAsserts {

    /**
     * @param $range - how many seconds the timestamp can be off by
     * @param $timestamp    - the timestamp we want to check. Should be seconds since unix epoch
     */
    public static function timestampWithin($range, $timestamp) {
        date_default_timezone_set('America/New_York');
        $time = time();
        Assert::assertTrue( $timestamp <= $time + $range, "Timestamp $timestamp is outside the range +/-$range from now ($time)");
        Assert::assertTrue( $timestamp >= $time - $range, "Timestamp $timestamp is outside the range +/-$range from now ($time)");
    }

    /**
     * @param $range - how many seconds the timestamp can be off by
     * @param $time    - the times we want to check. Should be in "Y-m-d H:i:s" format
     */
    public static function timeWithin($range, $time) {
        date_default_timezone_set('America/New_York');
        Assert::assertStringMatchesFormat('%d-%d-%d %d:%d:%d', $time);
        self::timestampWithin($range, strtotime($time));
    }

    /**
     * @param $range - how many seconds the timestamp can be off by
     * @param $time    - the times we want to check. Should be in "Y-m-d H-i-s" format
     */
    public static function dashedTimeWithin($range, $time) {
        date_default_timezone_set('America/New_York');
        Assert::assertStringMatchesFormat('%d-%d-%d %d-%d-%d', $time);
        $time = str_replace('-', ':', $time);
        $time = preg_replace('/'.preg_quote(':', '/').'/', '-', $time, 2);
        self::timestampWithin($range, strtotime($time));
    }
}