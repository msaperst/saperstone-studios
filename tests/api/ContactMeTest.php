<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

/**
 * Class ContactMeTest
 * @package api
 */
class ContactMeTest extends TestCase {
    /**
     * @var Client
     */
    private $http;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
    }

    public function tearDown() {
        $this->http = NULL;
    }

    /**
     * @throws GuzzleException
     */
    public function testNoName() {
        $response = $this->http->request('POST', 'api/contact-me.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Name is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankName() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'name' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Name can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoPhone() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'name' => 'Max'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Phone number is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws GuzzleException
     */
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