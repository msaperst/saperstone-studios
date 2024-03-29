<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UpdateCartTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `cart` (`user`, `album`, `image`, `product`, `count`) VALUES ( '3', '3', '5', '3', '1');");
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('999', 'sample-album', 'sample album for testing', 'sample', 4, 123);");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (998, '999', '', '2', '', '', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (999, '999', '', '3', '', '', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `products` (`id`, `product_type`, `size`, `price`, `cost`) VALUES (999, '1', '12x19', 100, 10)");
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `cart` WHERE `user` = '3';");
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 999;");
        $this->sql->executeStatement("DELETE FROM `products` WHERE `products`.`id` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `album_images` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `products`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `products` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/update-cart.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals('You must be logged in to perform this action', $e->getResponse()->getBody());
        }
    }

    public function testNoCart() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-cart.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("0", (string)$response->getBody());
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM cart WHERE user = 3"));
    }

    public function testBlankImages() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-cart.php', [
            'form_params' => [
                'images' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("0", (string)$response->getBody());
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM cart WHERE user = 3"));
    }

    public function testNoImages() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-cart.php', [
            'form_params' => [
                'images' => [
                    'x' => 1
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album is required", (string)$response->getBody());
    }

    public function testNoAlbum() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-cart.php', [
            'form_params' => [
                'images' => [
                    array()
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("0", (string)$response->getBody());
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM cart WHERE user = 3"));
    }

    public function testBlankAlbum() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-cart.php', [
            'form_params' => [
                'images' => [
                    [
                        'album' => ''
                    ]
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album can not be blank", (string)$response->getBody());
    }

    public function testBadAlbum() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-cart.php', [
            'form_params' => [
                'images' => [
                    [
                        'album' => '998'
                    ]
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string)$response->getBody());
    }

    public function testNoImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-cart.php', [
            'form_params' => [
                'images' => [
                    [
                        'album' => '999'
                    ]
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image is required", (string)$response->getBody());
    }

    public function testBlankImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-cart.php', [
            'form_params' => [
                'images' => [
                    [
                        'album' => '999',
                        'image' => ''
                    ]
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image can not be blank", (string)$response->getBody());
    }

    public function testBadImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-cart.php', [
            'form_params' => [
                'images' => [
                    [
                        'album' => '999',
                        'image' => '998'
                    ]
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image id does not match any images", (string)$response->getBody());
    }

    public function testNoProduct() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-cart.php', [
            'form_params' => [
                'images' => [
                    [
                        'album' => '999',
                        'image' => '3'
                    ]
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product is required", (string)$response->getBody());
    }

    public function testBlankProduct() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-cart.php', [
            'form_params' => [
                'images' => [
                    [
                        'album' => '999',
                        'image' => '3',
                        'product' => ''
                    ]
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product can not be blank", (string)$response->getBody());
    }

    public function testBadProduct() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-cart.php', [
            'form_params' => [
                'images' => [
                    [
                        'album' => '999',
                        'image' => '3',
                        'product' => '998'
                    ]
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product id does not match any products", (string)$response->getBody());
    }

    public function testNoCount() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-cart.php', [
            'form_params' => [
                'images' => [
                    [
                        'album' => '999',
                        'image' => '3',
                        'product' => '999'
                    ]
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Count is required", (string)$response->getBody());
    }

    public function testBlankCount() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-cart.php', [
            'form_params' => [
                'images' => [
                    [
                        'album' => '999',
                        'image' => '3',
                        'product' => '999',
                        'count' => ''
                    ]
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Count can not be blank", (string)$response->getBody());
    }

    public function testSingleItem() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-cart.php', [
            'form_params' => [
                'images' => [
                    [
                        'album' => '999',
                        'image' => '3',
                        'product' => '999',
                        'count' => '2'
                    ]
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("2", (string)$response->getBody());
        $cart = $this->sql->getRows("SELECT * FROM cart WHERE user = 3");
        $this->assertEquals(1, sizeof($cart));
        $this->assertEquals(3, $cart[0]['user']);
        $this->assertEquals(999, $cart[0]['album']);
        $this->assertEquals(999, $cart[0]['image']);
        $this->assertEquals(999, $cart[0]['product']);
        $this->assertEquals(2, $cart[0]['count']);
    }

    public function testMultipleItems() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-cart.php', [
            'form_params' => [
                'images' => [
                    [
                        'album' => '999',
                        'image' => '3',
                        'product' => '999',
                        'count' => '2'
                    ],
                    [
                        'album' => '999',
                        'image' => '2',
                        'product' => '999',
                        'count' => '1'
                    ]
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("3", (string)$response->getBody());
        $cart = $this->sql->getRows("SELECT * FROM cart WHERE user = 3");
        $this->assertEquals(2, sizeof($cart));
        $this->assertEquals(3, $cart[0]['user']);
        $this->assertEquals(999, $cart[0]['album']);
        $this->assertEquals(998, $cart[0]['image']);
        $this->assertEquals(999, $cart[0]['product']);
        $this->assertEquals(1, $cart[0]['count']);
        $this->assertEquals(3, $cart[1]['user']);
        $this->assertEquals(999, $cart[1]['album']);
        $this->assertEquals(999, $cart[1]['image']);
        $this->assertEquals(999, $cart[1]['product']);
        $this->assertEquals(2, $cart[1]['count']);
    }
}