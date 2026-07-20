<?php

namespace api;

use CustomAsserts;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';

class DownloadSendEmailTest extends TestCase {
    private $http;

    public function setUp(): void {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/']);
        // Ensure every single test starts with a completely clean mailbox slate!
        CustomAsserts::clearAllEmails();
    }

    public function tearDown(): void {
        $this->http = NULL;
    }

    public function testNoEmail() {
        $response = $this->http->request('POST', 'api/download-send-email.php', [
            'form_params' => [
                'file' => '../tmp/sample.zip'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $result = json_decode($response->getBody(), true);
        $this->assertEquals("Email address is required", $result['error']);
        CustomAsserts::assertEmailCount(0);
    }

    public function testBlankEmail() {
        $response = $this->http->request('POST', 'api/download-send-email.php', [
            'form_params' => [
                'email' => '',
                'file' => '../tmp/sample.zip'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $result = json_decode($response->getBody(), true);
        $this->assertEquals("Email address can not be blank", $result['error']);
        CustomAsserts::assertEmailCount(0);
    }

    public function testNoFile() {
        $response = $this->http->request('POST', 'api/download-send-email.php', [
            'form_params' => [
                'email' => 'test@saperstonestudios.com'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $result = json_decode($response->getBody(), true);
        $this->assertEquals("Image file is required", $result['error']);
        CustomAsserts::assertEmailCount(0);
    }

    public function testBlankFile() {
        $response = $this->http->request('POST', 'api/download-send-email.php', [
            'form_params' => [
                'email' => 'test@saperstonestudios.com',
                'file' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $result = json_decode($response->getBody(), true);
        $this->assertEquals("Image file can not be blank", $result['error']);
        CustomAsserts::assertEmailCount(0);
    }

    public function testInvalidEmailStructure() {
        $response = $this->http->request('POST', 'api/download-send-email.php', [
            'form_params' => [
                'email' => 'not-an-email',
                'file' => '../tmp/sample.zip'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $result = json_decode($response->getBody(), true);
        $this->assertEquals("Invalid email address provided.", $result['error']);
        CustomAsserts::assertEmailCount(0);
    }

    public function testSuccessfulExecutionTrigger() {
        try {
            $response = $this->http->request('POST', 'api/download-send-email.php', [
                'form_params' => [
                    'email' => 'validuser@gmail.com',
                    'file' => '../tmp/sample.zip'
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals("", (string)$response->getBody());
            CustomAsserts::assertEmailCount(0);
            sleep(25);
            //ensure no email is sent as no file exists
            CustomAsserts::assertEmailCount(0);
            // create the file, and then ensure the email is sent
            touch(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "tmp/sample.zip");
            sleep(2);
            CustomAsserts::assertEmailCount(1);
            CustomAsserts::assertEmailMatches(
                'validuser@gmail.com',
                'noreply@saperstonestudios.com',
                'Your Download Is Ready',
                'You can access your photos at https://saperstonestudios.com/tmp/sample.zip

This download will be available for the next 48 hours',
                "<html><body><p>Your download is ready</p><p>You can access your photos at <a href='https://saperstonestudios.com/tmp/sample.zip'>https://saperstonestudios.com/tmp/sample.zip</a></p><p>This download will be available for the next 48 hours</p></body></html>");
        } finally {
            unlink(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "tmp/sample.zip");
        }
    }
}