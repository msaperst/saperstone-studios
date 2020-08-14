<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class IsDownloadableTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES (999, 'sample-album-no-access', 'sample album for testing without any download access', 'sample', 4, '123');");
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('3', 999, '990');");
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('0', 999, '991');");
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('3', '*', '992');");
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('0', '*', '993');");
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('0', '998', '995');");
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('5', '*', '996');");
        for ($i = 0; $i < 10; $i++) {
            $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `location`, `width`, `height`, `active`) VALUES ('99$i', 999, '', $i, '', '600', '400', '1');");
        }
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 999");
        $this->sql->executeStatement("DELETE FROM `download_rights` WHERE `image` LIKE '%99%%'");
        $this->sql->executeStatement("DELETE FROM `download_rights` WHERE `album` = '999'");
        $this->sql->executeStatement("DELETE FROM `download_rights` WHERE `album` = '*' AND `image` = '*' AND `user` = '0'");
        $this->sql->executeStatement("DELETE FROM `download_rights` WHERE `album` = '*' AND `image` = '*' AND `user` = '3'");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 999");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `album_images` AUTO_INCREMENT = $count;");
        system("rm -rf " . escapeshellarg(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample'));
        $this->sql->disconnect();
    }

    public function testAdminHasAccess() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, (string)$response->getBody());
    }

    public function testNoAlbum() {
        $response = $this->http->request('GET', 'api/is-downloadable.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id is required", (string)$response->getBody());
    }

    public function testBlankAlbum() {
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'query' => [
                'album' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id can not be blank", (string)$response->getBody());
    }

    public function testLetterAlbum() {
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'query' => [
                'album' => 'a'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string)$response->getBody());
    }

    public function testBadAlbumId() {
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'query' => [
                'album' => 9999
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string)$response->getBody());
    }

    public function testUserCantAccess() {
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'query' => [
                'album' => 999
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("0", (string)$response->getBody());
    }

    public function testNoImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146',
            'searched' => json_encode([
                999 => md5('album123')
            ])
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'query' => [
                'album' => 999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image id is required", (string)$response->getBody());
    }

    public function testBlankImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146',
            'searched' => json_encode([
                999 => md5('album123')
            ])
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'query' => [
                'album' => 999,
                'image' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image id can not be blank", (string)$response->getBody());
    }

    public function testBadImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146',
            'searched' => json_encode([
                999 => md5('album123')
            ])
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'query' => [
                'album' => 999,
                'image' => '9999'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image id does not match any images", (string)$response->getBody());
    }

    public function testYourImageYourAlbumYourUser() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146',
            'searched' => json_encode([
                999 => md5('album123')
            ])
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'query' => [
                'album' => 999,
                'image' => '0'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, (string)$response->getBody());
    }

    public function testYourImageYourAlbumOpenUser() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146',
            'searched' => json_encode([
                999 => md5('album123')
            ])
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'query' => [
                'album' => 999,
                'image' => '1'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, (string)$response->getBody());
    }

    public function testYourImageOpenAlbumYourUser() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146',
            'searched' => json_encode([
                999 => md5('album123')
            ])
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'query' => [
                'album' => 999,
                'image' => '2'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, (string)$response->getBody());
    }

    public function testYourImageOpenAlbumOpenUser() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146',
            'searched' => json_encode([
                999 => md5('album123')
            ])
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'query' => [
                'album' => 999,
                'image' => '3'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, (string)$response->getBody());
    }

    public function testOpenImageYourAlbumYourUser() {
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('3', 999, '*');");
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146',
            'searched' => json_encode([
                999 => md5('album123')
            ])
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'query' => [
                'album' => 999,
                'image' => '4'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, (string)$response->getBody());
    }

    public function testOpenImageYourAlbumOpenUser() {
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('0', 999, '*');");
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146',
            'searched' => json_encode([
                999 => md5('album123')
            ])
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'query' => [
                'album' => 999,
                'image' => '4'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, (string)$response->getBody());
    }

    public function testOpenImageOpenAlbumYourUser() {
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('3', '*', '*');");
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146',
            'searched' => json_encode([
                999 => md5('album123')
            ])
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'query' => [
                'album' => 999,
                'image' => '4'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, (string)$response->getBody());
    }

    public function testOpenImageOpenAlbumOpenUser() {
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('0', '*', '*');");
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146',
            'searched' => json_encode([
                999 => md5('album123')
            ])
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'query' => [
                'album' => 999,
                'image' => '4'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, (string)$response->getBody());
    }

    public function testClosedImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146',
            'searched' => json_encode([
                999 => md5('album123')
            ])
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'query' => [
                'album' => 999,
                'image' => '4'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(0, (string)$response->getBody());
    }

    public function testClosedAlbum() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146',
            'searched' => json_encode([
                999 => md5('album123')
            ])
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'query' => [
                'album' => 999,
                'image' => '5'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(0, (string)$response->getBody());
    }

    public function testClosedUser() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146',
            'searched' => json_encode([
                999 => md5('album123')
            ])
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/is-downloadable.php', [
            'query' => [
                'album' => 999,
                'image' => '6'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(0, (string)$response->getBody());
    }
}
