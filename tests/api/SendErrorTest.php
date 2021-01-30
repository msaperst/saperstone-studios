<?php

namespace api;

use CustomAsserts;
use Google\Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class SendErrorTest extends TestCase {
    /**
     * @var Client
     */
    private $http;

    /**
     *
     */
    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
    }

    /**
     *
     */
    public function tearDown() {
        $this->http = NULL;
    }

    /**
     * @throws GuzzleException
     */
    public function testNoError() {
        $response = $this->http->request('POST', 'api/send-error.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Error is required", $response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankError() {
        $response = $this->http->request('POST', 'api/send-error.php', [
            'form_params' => [
                'error' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Error can not be blank", $response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoPage() {
        $response = $this->http->request('POST', 'api/send-error.php', [
            'form_params' => [
                'error' => '404'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Page is required", $response->getBody());
    }

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws GuzzleException
     * @throws Exception
     */
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
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page localhost/123.html\r
\t\tThey came from page localhost\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: unknown unknown\r
Resolution: \r
OS: unknown\r
Full UA: GuzzleHttp/7\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'localhost/123.html\' target=\'_blank\'>localhost/123.html</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'localhost\' target=\'_blank\'>localhost</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>');
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function testResolution() {
        $response = $this->http->request('POST', 'api/send-error.php', [
            'form_params' => [
                'error' => '403',
                'page' => 'localhost/123.html',
                'referrer' => 'localhost',
                'resolution' => '200x400'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", $response->getBody());
        CustomAsserts::assertEmailMatches('403 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 403 on page localhost/123.html\r
\t\tThey came from page localhost\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: unknown unknown\r
Resolution: 200x400\r
OS: unknown\r
Full UA: GuzzleHttp/7\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 403 on page <a href=\'localhost/123.html\' target=\'_blank\'>localhost/123.html</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'localhost\' target=\'_blank\'>localhost</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: 200x400<br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>');
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function testLoggedIn() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/send-error.php', [
            'form_params' => [
                'error' => '401',
                'page' => 'localhost/123.html',
                'referrer' => 'localhost'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", $response->getBody());
        CustomAsserts::assertEmailMatches('401 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 401 on page localhost/123.html\r
\t\tThey came from page localhost\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
User Id: 1\r
Name: Max Saperstone\r
Email: msaperst@gmail.com\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: unknown unknown\r
Resolution: \r
OS: unknown\r
Full UA: GuzzleHttp/7\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 401 on page <a href=\'localhost/123.html\' target=\'_blank\'>localhost/123.html</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'localhost\' target=\'_blank\'>localhost</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>User Id</strong>: 1<br/><strong>Name</strong>: Max Saperstone<br/><strong>Email</strong>: <a href=\'mailto:msaperst@gmail.com\'>msaperst@gmail.com</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>');
    }
}