<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class ContactMeTest extends TestCase {
    private $http;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
    }

    public function tearDown() {
        $this->http = NULL;
    }

    public function testEmptyParams() {
        $response = $this->http->request('POST', 'api/contact-me.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Name is required", $response->getBody());
    }

    public function testOnlyName() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
                'form_params' => [
                    'name' => 'Max'
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Phone is required", $response->getBody());
    }

    public function testOnlyNamePhone() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
                'form_params' => [
                    'name' => 'Max',
                    'phone' => '571-245-3351'
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is required", $response->getBody());
    }

    public function testOnlyNamePhoneEmail() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
                'form_params' => [
                    'name' => 'Max',
                    'phone' => '571-245-3351',
                    'email' => 'msaperst@gmail.com'
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("A message is required", $response->getBody());
    }

    public function testAll() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
                'form_params' => [
                    'name' => 'Max',
                    'phone' => '571-245-3351',
                    'email' => 'msaperst@gmail.com',
                    'message' => 'Hi There! I am a test email'
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Thank you for submitting your comment. We greatly appreciate your interest and feedback. Someone will get back to you within 24 hours.", $response->getBody());
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
        $this->assertEquals("Thank you for submitting your comment. We greatly appreciate your interest and feedback. Someone will get back to you within 24 hours.", $response->getBody());
    }
}

?>