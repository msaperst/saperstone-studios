<?php

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Google\Exception as ExceptionAlias;
use PHPUnit\Framework\Assert;

require_once 'Gmail.php';

class CustomAsserts {

    /**
     * @param $range - how many seconds the timestamp can be off by
     * @param $time - the times we want to check. Should be in "Y-m-d H:i:s" format
     */
    public static function timeWithin($range, $time) {
        date_default_timezone_set('America/New_York');
        Assert::assertStringMatchesFormat('%d-%d-%d %d:%d:%d', $time);
        self::timestampWithin($range, strtotime($time));
    }

    /**
     * @param $range - how many seconds the timestamp can be off by
     * @param $timestamp - the timestamp we want to check. Should be seconds since unix epoch
     */
    public static function timestampWithin($range, $timestamp) {
        date_default_timezone_set('America/New_York');
        $time = time();
        Assert::assertTrue($timestamp <= $time + $range, "Timestamp $timestamp is outside the range +/-$range from now ($time)");
        Assert::assertTrue($timestamp >= $time - $range, "Timestamp $timestamp is outside the range +/-$range from now ($time)");
    }

    /**
     * @param $range - how many seconds the timestamp can be off by
     * @param $time - the times we want to check. Should be in "Y-m-d H-i-s" format
     */
    public static function dashedTimeWithin($range, $time) {
        date_default_timezone_set('America/New_York');
        Assert::assertStringMatchesFormat('%d-%d-%d %d-%d-%d', $time);
        $time = str_replace('-', ':', $time);
        $time = preg_replace('/' . preg_quote(':', '/') . '/', '-', $time, 2);
        self::timestampWithin($range, strtotime($time));
    }

    /**
     * @param $driver
     * @param $message
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public static function successMessage($driver, $message) {
        self::checkMessage($driver, 'success', $message);
    }

    /**
     * @param $driver
     * @param $type
     * @param $message
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    private static function checkMessage($driver, $type, $message) {
        $successBy = WebDriverBy::classname("alert-$type");
        $wait = new WebDriverWait($driver, 10);
        $wait->until(WebDriverExpectedCondition::presenceOfElementLocated($successBy));
        $actualMessage = $driver->findElement($successBy)->getText();
        Assert::assertEquals("×
$message", $actualMessage, $actualMessage);
    }

    /**
     * @param $driver
     * @param $message
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public static function warningMessage($driver, $message) {
        self::checkMessage($driver, 'warning', $message);
    }

    /**
     * @param $driver
     * @param $message
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public static function infoMessage($driver, $message) {
        self::checkMessage($driver, 'info', $message);
    }

    /**
     * @param $driver
     * @param $message
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public static function errorMessage($driver, $message) {
        self::checkMessage($driver, 'danger', $message);
    }

    /**
     * @param $subject
     * @param $txt
     * @param $html
     * @param null $attachment
     * @throws ExceptionAlias
     */
    public static function assertEmailEquals($subject, $txt, $html, $attachment = NULL) {
        try {
            $gmail = new Gmail($subject);
            $gmailTxt = $gmail->getEmailTxt();
            Assert::assertEquals($txt, $gmailTxt, $gmailTxt);
            $gmailHtml = $gmail->getEmailHtml();
            Assert::assertEquals($html, $gmailHtml, $gmailHtml);
            if ($attachment != NULL) {
                $filename = $gmail->saveAttachment();
                self::filesAreEqual($attachment, $filename);
            }
        } finally {
            $gmail->deleteEmail();
            if (isset($filename) && file_exists($filename)) {
                unlink($filename);
            }
        }
    }

    /**
     * @param $subject
     * @param $txt
     * @param $html
     * @param null $attachment
     * @throws ExceptionAlias
     */
    public static function assertEmailMatches($subject, $txt, $html, $attachment = NULL) {
        try {
            $gmail = new Gmail($subject);
            $gmailTxt = $gmail->getEmailTxt();
            Assert::assertStringMatchesFormat($txt, $gmailTxt, $gmailTxt);
            $gmailHtml = $gmail->getEmailHtml();
            Assert::assertStringMatchesFormat($html, $gmailHtml, $gmailHtml);
            if ($attachment != NULL) {
                $filename = $gmail->saveAttachment();
                self::filesAreEqual($attachment, $filename);
            }
        } finally {
            $gmail->deleteEmail();
            if (isset($filename) && file_exists($filename)) {
                unlink($filename);
            }
        }
    }

    /**
     * @param $a
     * @param $b
     */
    public static function filesAreEqual($a, $b) {
        // Check if filesize is different
        if (filesize($a) !== filesize($b)) {
            Assert::assertTrue(false);
        }
        // Check if content is different
        $ah = fopen($a, 'rb');
        $bh = fopen($b, 'rb');
        $result = true;
        while (!feof($ah)) {
            if (fread($ah, 8192) != fread($bh, 8192)) {
                $result = false;
                break;
            }
        }
        fclose($ah);
        fclose($bh);
        Assert::assertTrue($result);
    }

    /**
     * @param $url
     * @param $code
     */
    public static function httpCodeEquals($url, $code) {
        $url = str_replace(" ", '%20', $url);
        $handle = curl_init($url);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
        curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        Assert::assertEquals($code, $httpCode, $httpCode);
    }
}