<?php

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use GuzzleHttp\Client;
use PHPUnit\Framework\Assert;

require_once 'Gmail.php';

class CustomAsserts {

    /**
     * @param $range - how many seconds the timestamp can be off by
     * @param $time - the times we want to check. Should be in "Y-m-d H:i:s" format
     */
    public static function timeWithin($range, $time): void {
        date_default_timezone_set('America/New_York');
        Assert::assertStringMatchesFormat('%d-%d-%d %d:%d:%d', $time);
        self::timestampWithin($range, strtotime($time));
    }

    /**
     * @param $range - how many seconds the timestamp can be off by
     * @param $timestamp - the timestamp we want to check. Should be seconds since unix epoch
     */
    public static function timestampWithin($range, $timestamp): void {
        date_default_timezone_set('America/New_York');
        $time = time();
        Assert::assertTrue($timestamp <= $time + $range, "Timestamp $timestamp is outside the range +/-$range from now ($time)");
        Assert::assertTrue($timestamp >= $time - $range, "Timestamp $timestamp is outside the range +/-$range from now ($time)");
    }

    /**
     * @param $range - how many seconds the timestamp can be off by
     * @param $time - the times we want to check. Should be in "Y-m-d H-i-s" format
     */
    public static function dashedTimeWithin($range, $time): void {
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
    public static function successMessage($driver, $message): void {
        self::checkMessage($driver, 'success', $message);
    }

    /**
     * @param $driver
     * @param $type
     * @param $message
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    private static function checkMessage($driver, $type, $message): void {
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
    public static function warningMessage($driver, $message): void {
        self::checkMessage($driver, 'warning', $message);
    }

    /**
     * @param $driver
     * @param $message
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public static function infoMessage($driver, $message): void {
        self::checkMessage($driver, 'info', $message);
    }

    /**
     * @param $driver
     * @param $message
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public static function errorMessage($driver, $message): void {
        self::checkMessage($driver, 'danger', $message);
    }

    private static function getMailpitClient(): Client {
        // Points to Mailpit's web/API port
        return new Client(['base_uri' => 'http://localhost:8025/api/v1/']);
    }

    /**
     * Clears out all captured emails. Call this in your test setUp() or tearDown()
     * to prevent cross-test email bleed/contamination!
     */
    public static function clearAllEmails(): void {
        self::getMailpitClient()->request('DELETE', 'messages');
    }

    public static function assertEmailCount(int $expectedCount): void {
        $client = self::getMailpitClient();
        $response = $client->request('GET', 'messages');
        $data = json_decode((string)$response->getBody(), true);

        Assert::assertCount($expectedCount, $data['messages'] ?? [], "Expected exactly $expectedCount emails sent.");
    }

    /**
     * Finds a specific email and asserts its content, including SMTP credentials and attachments.
     */
    public static function assertEmailMatches(
        string  $expectedTo,
        string  $expectedFrom,
        string  $expectedSubject,
        string  $expectedText,
        string  $expectedHtml,
        ?string $expectedAuthUser = null,
        ?string $expectedAttachmentPath = null // <-- Add optional attachment path
    ): void {
        if ($expectedAuthUser === null) {
            $expectedAuthUser = (string)getenv('EMAIL_USER');
        }

        $client = self::getMailpitClient();
        $response = $client->request('GET', 'messages');
        $data = json_decode((string)$response->getBody(), true);

        $found = false;

        foreach (($data['messages'] ?? []) as $msg) {
            $messageId = $msg['ID'];
            $detailResponse = $client->request('GET', "message/{$messageId}");
            $detail = json_decode((string)$detailResponse->getBody(), true);

            $toAddresses = array_column($detail['To'] ?? [], 'Address');
            $fromAddress = $detail['From']['Address'] ?? '';

            if (in_array($expectedTo, $toAddresses) && $fromAddress === $expectedFrom && $detail['Subject'] === $expectedSubject) {
                $found = true;

                // Assert contents
                Assert::assertStringMatchesFormat($expectedText, $detail['Text'], "Text body did not match.");
                Assert::assertStringMatchesFormat($expectedHtml, $detail['HTML'], "HTML body did not match.");

                // Assert SMTP Authentication username
                $actualAuthUser = $detail['Username'] ?? '';
                Assert::assertEquals($expectedAuthUser, $actualAuthUser, "SMTP Username mismatch.");

                // Assert Attachment if expected
                if ($expectedAttachmentPath !== null) {
                    Assert::assertNotEmpty($detail['Attachments'], "Expected an attachment, but none were found.");

                    // Grab the first attachment metadata block
                    $attachmentMeta = $detail['Attachments'][0];
                    $partId = $attachmentMeta['PartID'];
                    $fileName = $attachmentMeta['FileName'];

                    // Verify the file name matches what you expect
                    $expectedFileName = basename($expectedAttachmentPath);
                    Assert::assertEquals($expectedFileName, $fileName, "Attachment filename mismatch.");

                    // Download the binary attachment payload from Mailpit
                    $downloadResponse = $client->request('GET', "message/{$messageId}/part/{$partId}");

                    // Save it to a temporary local file to run your existing comparison logic
                    $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;
                    file_put_contents($tmpFile, (string)$downloadResponse->getBody());

                    try {
                        // Use your existing binary file comparison method
                        self::filesAreEqual($expectedAttachmentPath, $tmpFile);
                    } finally {
                        // Clean up the downloaded temp file immediately
                        if (file_exists($tmpFile)) {
                            unlink($tmpFile);
                        }
                    }
                }
                break;
            }
        }

        Assert::assertTrue($found, "Failed asserting that the specified email was sent.");
    }

    /**
     * @param $a
     * @param $b
     */
    public static function filesAreEqual($a, $b): void {
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
    public static function httpCodeEquals($url, $code): void {
        $url = str_replace(" ", '%20', $url);
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        Assert::assertEquals($code, $httpCode, $httpCode);
    }
}