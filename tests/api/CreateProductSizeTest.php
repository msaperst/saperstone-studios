<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class CreateProductSizeTest extends TestCase {
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
        try {
            $this->http->request('POST', 'api/create-product-size.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], 'localhost');
        try {
            $this->http->request('POST', 'api/create-product-size.php', [
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testNoType() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-product-size.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product type is required", (string)$response->getBody());
    }

    public function testBlankType() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-product-size.php', [
            'form_params' => [
                'type' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product type can not be blank", (string)$response->getBody());
    }

    public function testNoSize() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-product-size.php', [
            'form_params' => [
                'type' => '1'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product size is required", (string)$response->getBody());
    }

    public function testBlankSize() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-product-size.php', [
            'form_params' => [
                'type' => '1',
                'size' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product size can not be blank", (string)$response->getBody());
    }

    public function testNoCost() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-product-size.php', [
            'form_params' => [
                'type' => '1',
                'size' => '1x1'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product cost is required", (string)$response->getBody());
    }

    public function testBlankCost() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-product-size.php', [
            'form_params' => [
                'type' => '1',
                'size' => '1x1',
                'cost' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product cost can not be blank", (string)$response->getBody());
    }

    public function testNoPrice() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-product-size.php', [
            'form_params' => [
                'type' => '1',
                'size' => '1x1',
                'cost' => '12.456'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product price is required", (string)$response->getBody());
    }

    public function testBlankPrice() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-product-size.php', [
            'form_params' => [
                'type' => '1',
                'size' => '1x1',
                'cost' => '$12.56',
                'price' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product price can not be blank", (string)$response->getBody());
    }

    public function testProductSize() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/create-product-size.php', [
                'form_params' => [
                    'type' => '1',
                    'size' => '1x1',
                    'cost' => '$12.50',
                    'price' => 12.1234
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $productId = $response->getBody();
            $productSizeDetails = $this->sql->getRow("SELECT * FROM `products` WHERE `products`.`id` = $productId;");
            $this->assertEquals($productId, $productSizeDetails['id']);
            $this->assertEquals(1, $productSizeDetails['product_type']);
            $this->assertEquals('1x1', $productSizeDetails['size']);
            $this->assertEquals(12.50, $productSizeDetails['cost']);
            $this->assertEquals(12.12, $productSizeDetails['price']);
        } finally {
            $this->sql->executeStatement("DELETE FROM `products` WHERE `products`.`id` = $productId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `products`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `products` AUTO_INCREMENT = $count;");
        }
    }
}

?>