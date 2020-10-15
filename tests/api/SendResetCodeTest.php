<?php

namespace api;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class SendResetCodeTest extends TestCase {
    private $http;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
    }

    public function tearDown() {
        $this->http = NULL;
    }

    public function testNoEmail() {
        $response = $this->http->request('POST', 'api/send-reset-code.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is required", $response->getBody());
    }

    public function testBlankEmail() {
        $response = $this->http->request('POST', 'api/send-reset-code.php', [
            'form_params' => [
                'email' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email can not be blank", $response->getBody());
    }

    public function testInvalidEmail() {
        $response = $this->http->request('POST', 'api/send-reset-code.php', [
            'form_params' => [
                'email' => '1234@hi'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is not valid", $response->getBody());
    }

    public function testBadEmail() {
        $response = $this->http->request('POST', 'api/send-reset-code.php', [
            'form_params' => [
                'email' => 'msap@gmail.com'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Credentials do not match our records", (string) $response->getBody());
    }

    public function testSendResetCode() {
        try {
            $response = $this->http->request('POST', 'api/send-reset-code.php', [
                'form_params' => [
                    'email' => 'msaperst@gmail.com'
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals("", $response->getBody());
            // TODO - need to confirm email is sent
        } finally {
            $sql = new Sql();
            $sql->executeStatement("UPDATE users SET resetKey=NULL WHERE id=1;");
            $sql->disconnect();

        }
    }
}