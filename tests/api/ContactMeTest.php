<?php

namespace api;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'sql.php';

class ContactMeTest extends TestCase {
    private $http;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
    }

    public function tearDown() {
        $this->http = NULL;
    }

    public function testNoName() {
        $response = $this->http->request('POST', 'api/contact-me.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Name is required", (string)$response->getBody());
    }

    public function testBlankName() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'name' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Name can not be blank", (string)$response->getBody());
    }

    public function testNoPhone() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'name' => 'Max'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Phone number is required", (string)$response->getBody());
    }

    public function testBlankPhone() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'name' => 'Max',
                'phone' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Phone number can not be blank", (string)$response->getBody());
    }

    public function testNoEmail() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'name' => 'Max',
                'phone' => '1234'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is required", (string)$response->getBody());
    }

    public function testBlankEmail() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'name' => 'Max',
                'phone' => '1234',
                'email' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email can not be blank", (string)$response->getBody());
    }

    public function testBadEmail() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'name' => 'Max',
                'phone' => '1234',
                'email' => 'max@max'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is not valid", (string)$response->getBody());
    }

    public function testNoMessage() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'name' => 'Max',
                'phone' => '1234',
                'email' => 'msaperst+sstest@gmail.com'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Message is required", (string)$response->getBody());
    }

    public function testBlankMessage() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'name' => 'Max',
                'phone' => '1234',
                'email' => 'msaperst+sstest@gmail.com',
                'message' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Message can not be blank", (string)$response->getBody());
    }

    public function testAll() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'name' => 'Max',
                'phone' => '571-245-3351',
                'email' => 'msaperst+sstest@gmail.com',
                'message' => 'Hi There! I am a test email'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Thank you for submitting your comment. We greatly appreciate your interest and feedback. Someone will get back to you within 24 hours.", (string)$response->getBody());
    }

    public function testAllSS() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'name' => 'Max',
                'phone' => '571-245-3351',
                'email' => 'msaperst@saperstonestudios.com',
                'message' => 'Hi There! I am a test email'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Thank you for submitting your comment. We greatly appreciate your interest and feedback. Someone will get back to you within 24 hours.", (string)$response->getBody());
    }
}

?>