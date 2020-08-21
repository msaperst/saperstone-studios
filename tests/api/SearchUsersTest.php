<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class SearchUsersTest extends TestCase {
    private $http;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
    }

    public function tearDown() {
        $this->http = NULL;
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/search-users.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        try {
            $this->http->request('POST', 'api/search-users.php', [
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testNoKeyword() {
        date_default_timezone_set("America/New_York");
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/search-users.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $users = json_decode($response->getBody(), true);
        $this->assertTrue( 5 <= sizeOf($users));
        $this->assertEquals(0, $users[0]['id']);
        $this->assertEquals('<i>All Users</i>', $users[0]['usr']);
        $this->assertEquals('admin', $users[0]['role']);
        $this->assertEquals(1, $users[1]['id']);
        $this->assertEquals('msaperst', $users[1]['usr']);
        $this->assertEquals('admin', $users[1]['role']);
        $this->assertEquals(2, $users[2]['id']);
        $this->assertEquals('lsaperst', $users[2]['usr']);
        $this->assertEquals('admin', $users[2]['role']);
        $this->assertEquals(3, $users[3]['id']);
        $this->assertEquals('downloader', $users[3]['usr']);
        $this->assertEquals('downloader', $users[3]['role']);
        $this->assertEquals(4, $users[4]['id']);
        $this->assertEquals('uploader', $users[4]['usr']);
        $this->assertEquals('uploader', $users[4]['role']);
    }

    public function testKeywordBlank() {
        date_default_timezone_set("America/New_York");
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/search-users.php', [
            'query' => [
                'keyword' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $users = json_decode($response->getBody(), true);
        $this->assertTrue( 5 <= sizeOf($users));
        $this->assertEquals(0, $users[0]['id']);
        $this->assertEquals('<i>All Users</i>', $users[0]['usr']);
        $this->assertEquals('admin', $users[0]['role']);
        $this->assertEquals(1, $users[1]['id']);
        $this->assertEquals('msaperst', $users[1]['usr']);
        $this->assertEquals('admin', $users[1]['role']);
        $this->assertEquals(2, $users[2]['id']);
        $this->assertEquals('lsaperst', $users[2]['usr']);
        $this->assertEquals('admin', $users[2]['role']);
        $this->assertEquals(3, $users[3]['id']);
        $this->assertEquals('downloader', $users[3]['usr']);
        $this->assertEquals('downloader', $users[3]['role']);
        $this->assertEquals(4, $users[4]['id']);
        $this->assertEquals('uploader', $users[4]['usr']);
        $this->assertEquals('uploader', $users[4]['role']);
    }

    public function testKeyword() {
        date_default_timezone_set("America/New_York");
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/search-users.php', [
            'query' => [
                'keyword' => 'r'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $users = json_decode($response->getBody(), true);
        $this->assertTrue( 5 <= sizeOf($users));
        $this->assertEquals(0, $users[0]['id']);
        $this->assertEquals('<i>All Users</i>', $users[0]['usr']);
        $this->assertEquals('admin', $users[0]['role']);
        $this->assertEquals(1, $users[1]['id']);
        $this->assertEquals('msaperst', $users[1]['usr']);
        $this->assertEquals('admin', $users[1]['role']);
        $this->assertEquals(2, $users[2]['id']);
        $this->assertEquals('lsaperst', $users[2]['usr']);
        $this->assertEquals('admin', $users[2]['role']);
        $this->assertEquals(3, $users[3]['id']);
        $this->assertEquals('downloader', $users[3]['usr']);
        $this->assertEquals('downloader', $users[3]['role']);
        $this->assertEquals(4, $users[4]['id']);
        $this->assertEquals('uploader', $users[4]['usr']);
        $this->assertEquals('uploader', $users[4]['role']);
    }

    public function testKeywords() {
        date_default_timezone_set("America/New_York");
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/search-users.php', [
            'query' => [
                'keyword' => 'der'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $users = json_decode($response->getBody(), true);
        $this->assertTrue( 2 <= sizeOf($users));
        $this->assertEquals(3, $users[0]['id']);
        $this->assertEquals('downloader', $users[0]['usr']);
        $this->assertEquals('downloader', $users[0]['role']);
        $this->assertEquals(4, $users[1]['id']);
        $this->assertEquals('uploader', $users[1]['usr']);
        $this->assertEquals('uploader', $users[1]['role']);
    }

    public function testTooKeyword() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/search-users.php', [
            'query' => [
                'keyword' => 'xyz'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array(), json_decode($response->getBody(), true));
    }
}