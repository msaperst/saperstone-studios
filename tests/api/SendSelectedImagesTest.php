<?php

namespace api;

use CustomAsserts;
use Exception;
use Google\Exception as ExceptionAlias;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class SendSelectedImagesTest extends TestCase {
    /**
     * @var Client
     */
    private $http;
    /**
     * @var Sql
     */
    private $sql;

    /**
     * @throws Exception
     */
    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('999', 'sample-album', 'sample album for testing', 'sample', 4, '123');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `location`, `width`, `height`, `active`) VALUES ('998', 999, 'file-1', 1, '/albums/sample/sample-1.jpg', '600', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `location`, `width`, `height`, `active`) VALUES ('999', 999, 'file-2', 2, '/albums/sample/sample-2.jpg', '600', '400', '1');");
    }

    /**
     * @throws Exception
     */
    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 999;");
        $this->sql->executeStatement("DELETE FROM `favorites` WHERE `favorites`.`user` = 4;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    /**
     * @throws GuzzleException
     */
    public function testNoAlbum() {
        $response = $this->http->request('POST', 'api/send-selected-images.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id is required", json_decode($response->getBody(), true)['err']);
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankAlbum() {
        $response = $this->http->request('POST', 'api/send-selected-images.php', [
            'form_params' => [
                'album' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id can not be blank", json_decode($response->getBody(), true)['err']);
    }

    /**
     * @throws GuzzleException
     */
    public function testLetterAlbum() {
        $response = $this->http->request('POST', 'api/send-selected-images.php', [
            'form_params' => [
                'album' => 'e'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", json_decode($response->getBody(), true)['err']);
    }

    /**
     * @throws GuzzleException
     */
    public function testBadAlbum() {
        $response = $this->http->request('POST', 'api/send-selected-images.php', [
            'form_params' => [
                'album' => '9999'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", json_decode($response->getBody(), true)['err']);
    }

    /**
     * @throws GuzzleException
     */
    public function testNoAlbumAccess() {
        try {
            $this->http->request('POST', 'api/send-selected-images.php', [
                'form_params' => [
                    'album' => '999'
                ]
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(403, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testNoWhat() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/send-selected-images.php', [
            'form_params' => [
                'album' => '999'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("What to select is required", json_decode($response->getBody(), true)['err']);
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankWhat() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/send-selected-images.php', [
            'form_params' => [
                'album' => '999',
                'what' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("What to select can not be blank", json_decode($response->getBody(), true)['err']);
    }

    /**
     * @throws GuzzleException
     */
    public function testNoFavorites() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/send-selected-images.php', [
            'form_params' => [
                'album' => '999',
                'what' => 'favorites'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("You have not selected any favorites", json_decode($response->getBody(), true)['err']);
    }

    /**
     * @throws GuzzleException
     */
    public function testFavorites() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $this->http->request('POST', 'api/set-favorite.php', [
            'form_params' => [
                'album' => 999,
                'image' => '1'
            ],
            'cookies' => $cookieJar
        ]);
        $this->http->request('POST', 'api/set-favorite.php', [
            'form_params' => [
                'album' => 999,
                'image' => '2'
            ],
            'cookies' => $cookieJar
        ]);
        $response = $this->http->request('POST', 'api/send-selected-images.php', [
            'form_params' => [
                'album' => '999',
                'what' => 'favorites'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", $response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBadImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/send-selected-images.php', [
            'form_params' => [
                'album' => '999',
                'what' => '98'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image id does not match any images", json_decode($response->getBody(), true)['err']);
    }

    /**
     * @throws GuzzleException
     * @throws ExceptionAlias
     */
    public function testGoodImageWithInfo() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/send-selected-images.php', [
            'form_params' => [
                'album' => '999',
                'what' => '2',
                'name' => 'Max',
                'email' => 'msaperst+sstest@gmail.com',
                'comment' => 'I want this one!'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", $response->getBody());
        CustomAsserts::assertEmailEquals('Thank You for Making Selects',
            'Thank you for making your selects. We\'ll start working on your images, and reach back out to you shortly with access to your final images.',
            '<html><body>Thank you for making your selects. We\'ll start working on your images, and reach back out to you shortly with access to your final images.</body></html>');
        CustomAsserts::assertEmailMatches('Selects Have Been Made',
            "This is an automatically generated message from Saperstone Studios\r
\r
Max has made a selection from the sample-album album at %s://%s/user/album.php?album=999.Their email address is msaperst+sstest@gmail.com\r
\r
file-2\r
\r
\t\tI want this one!",
            '<html><body><p>This is an automatically generated message from Saperstone Studios</p><p><a href=\'mailto:msaperst+sstest@gmail.com\'>Max</a> has made a selection from the <a href=\'%s://%s/user/album.php?album=999\' target=\'_blank\'>sample-album</a> album</p><p><ul><li>file-2</li></ul></p><br/><p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I want this one!</p></body></html>');
    }
}