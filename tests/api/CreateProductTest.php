<?php

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;

$_SERVER ['DOCUMENT_ROOT'] = dirname(__DIR__);
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "src/sql.php";

class CreateProductTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        $response;
        try {
            $response = $this->http->request('POST', 'api/create-product.php');
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], 'localhost');
        $response;
        try {
            $response = $this->http->request('POST', 'api/create-product.php', [
                'cookies' => $cookieJar
            ]);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testNoCategory() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-product.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product category is required", (string)$response->getBody());
    }

    public function testBlankCategory() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-product.php', [
            'form_params' => [
                'category' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product category can not be blank", (string)$response->getBody());
    }

    public function testBadCategory() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-product.php', [
            'form_params' => [
                'category' => 'wedding'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product category is not valid", (string)$response->getBody());
    }

    public function testNoName() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-product.php', [
            'form_params' => [
                'category' => 'signature'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product name is required", (string)$response->getBody());
    }

    public function testBlankName() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-product.php', [
            'form_params' => [
                'category' => 'signature',
                'name' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product name can not be blank", (string)$response->getBody());
    }

    public function testProduct() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/create-product.php', [
                'form_params' => [
                    'category' => 'signature',
                    'name' => 'explosion'
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $productId = $response->getBody();
            $productDetails = $this->sql->getRow("SELECT * FROM `product_types` WHERE `product_types`.`id` = $productId;");
            $this->assertEquals($productId, $productDetails['id']);
            $this->assertEquals('signature', $productDetails['category']);
            $this->assertEquals('explosion', $productDetails['name']);
        } finally {
            $this->sql->executeStatement("DELETE FROM `product_types` WHERE `product_types`.`id` = $productId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `product_types`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `product_types` AUTO_INCREMENT = $count;");
        }
    }
}

?>