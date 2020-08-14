<?php

namespace api;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class GetProductOptionsTest extends TestCase {
    private $http;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
    }

    public function tearDown() {
        $this->http = NULL;
    }

    public function testNoProduct() {
        $response = $this->http->request('GET', 'api/get-product-options.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product type is required", (string)$response->getBody());
    }

    public function testBlankProduct() {
        $response = $this->http->request('GET', 'api/get-product-options.php', [
            'query' => [
                'type' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product type can not be blank", (string)$response->getBody());
    }

    public function testLetterProduct() {
        $response = $this->http->request('GET', 'api/get-product-options.php', [
            'query' => [
                'type' => 'a'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product type does not match any products", (string)$response->getBody());
    }

    public function testBadProduct() {
        $response = $this->http->request('GET', 'api/get-product-options.php', [
            'query' => [
                'type' => 9999
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product type does not match any products", (string)$response->getBody());
    }

    public function testProductOptionsNone() {
        $response = $this->http->request('GET', 'api/get-product-options.php', [
            'query' => [
                'type' => 1
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array(), json_decode($response->getBody(), true));
    }

    public function testProductOptions() {
        $response = $this->http->request('GET', 'api/get-product-options.php', [
            'query' => [
                'type' => 5
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $productOptions = json_decode($response->getBody(), true);
        $this->assertEquals('Bamboo', $productOptions[0]);
        $this->assertEquals('Black', $productOptions[1]);
        $this->assertEquals('Light Wood', $productOptions[2]);
        $this->assertEquals('Stainless Steel', $productOptions[3]);
        $this->assertEquals('White', $productOptions[4]);
    }
}
