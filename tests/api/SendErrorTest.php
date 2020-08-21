<?php

namespace api;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class SendErrorTest extends TestCase {
    private $http;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
    }

    public function tearDown() {
        $this->http = NULL;
    }

    public function testNoError() {
        $response = $this->http->request('POST', 'api/send-error.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Error is required", $response->getBody());
    }

    public function testBlankError() {
        $response = $this->http->request('POST', 'api/send-error.php', [
            'form_params' => [
                'error' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Error can not be blank", $response->getBody());
    }

    public function testNoPage() {
        $response = $this->http->request('POST', 'api/send-error.php', [
            'form_params' => [
                'error' => '404'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Page is required", $response->getBody());
    }

    public function testBlankPage() {
        $response = $this->http->request('POST', 'api/send-error.php', [
            'form_params' => [
                'error' => '404',
                'page' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Page can not be blank", $response->getBody());
    }

    public function testNoReferrer() {
        $response = $this->http->request('POST', 'api/send-error.php', [
            'form_params' => [
                'error' => '404',
                'page' => 'localhost/123.html'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Referral is required", $response->getBody());
    }

    public function testBlankReferrer() {
        $response = $this->http->request('POST', 'api/send-error.php', [
            'form_params' => [
                'error' => '404',
                'page' => 'localhost/123.html',
                'referrer' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Referral can not be blank", $response->getBody());
    }

    public function testNoResolution() {
        $response = $this->http->request('POST', 'api/send-error.php', [
            'form_params' => [
                'error' => '404',
                'page' => 'localhost/123.html',
                'referrer' => 'localhost'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", $response->getBody());
        // TODO - need to confirm email is sent
    }

    public function testResolution() {
        $response = $this->http->request('POST', 'api/send-error.php', [
            'form_params' => [
                'error' => '404',
                'page' => 'localhost/123.html',
                'referrer' => 'localhost',
                'resolution' => '200x400'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", $response->getBody());
        // TODO - need to confirm email is sent
    }
}