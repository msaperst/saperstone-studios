<?php

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;

$_SERVER ['DOCUMENT_ROOT'] = dirname(__DIR__);
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "src/sql.php";

class DeleteAlbumImageTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`) VALUES ('999', 'sample-album', 'sample album for testing', 'sample', 4);");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (NULL, '999', '', '0', '', '/albums/sample/sample1.jpg', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (NULL, '999', '', '1', '', '/albums/sample/sample2.jpg', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('*', '999', '1');");
        $oldmask = umask(0);
        mkdir('content/albums/sample');
        chmod('content/albums/sample', 0777);
        mkdir('content/albums/sample/full');
        chmod('content/albums/sample/full', 0777);
        touch('content/albums/sample/sample1.jpg');
        chmod('content/albums/sample/sample1.jpg', 0777);
        touch('content/albums/sample/full/sample1.jpg');
        chmod('content/albums/sample/full/sample1.jpg', 0777);
        touch('content/albums/sample/sample2.jpg');
        chmod('content/albums/sample/sample2.jpg', 0777);
        touch('content/albums/sample/full/sample2.jpg');
        chmod('content/albums/sample/full/sample2.jpg', 0777);
        umask($oldmask);
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 999;");
        $this->sql->executeStatement("DELETE FROM `download_rights` WHERE `download_rights`.`album` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `album_images` AUTO_INCREMENT = $count;");
        system("rm -rf " . escapeshellarg('content/albums/sample'));
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        $response;
        try {
            $response = $this->http->request('POST', 'api/delete-album-image.php');
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
            $response = $this->http->request('POST', 'api/delete-album-image.php', [
                'cookies' => $cookieJar
            ]);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testNoAlbum() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-album-image.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id is required", (string)$response->getBody());
    }

    public function testBlankAlbum() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-album-image.php', [
            'form_params' => [
                'album' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id can not be blank", (string)$response->getBody());
    }

    public function testLetterAlbum() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-album-image.php', [
            'form_params' => [
                'album' => 'a'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string)$response->getBody());
    }

    public function testBadAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-album-image.php', [
            'form_params' => [
                'album' => 9999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string)$response->getBody());
    }

    public function testNoImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-album-image.php', [
            'form_params' => [
                'album' => 999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image id is required", (string)$response->getBody());
    }

    public function testBlankImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-album-image.php', [
            'form_params' => [
                'album' => 999,
                'image' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image id can not be blank", (string)$response->getBody());
    }

    public function testDeleteImage1() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-album-image.php', [
            'form_params' => [
                'album' => 999,
                'image' => '0'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $images = $this->sql->getRows("SELECT * FROM `album_images` WHERE `album_images`.`album` = 999;");
        $this->assertEquals(1, sizeOf($images));
        $this->assertEquals(1, preg_match("/^([\d]+)$/", $images[0]['id']));
        $this->assertEquals(999, $images[0]['album']);
        $this->assertEquals('', $images[0]['title']);
        $this->assertEquals(0, $images[0]['sequence']);
        $this->assertEquals('', $images[0]['caption']);
        $this->assertEquals('/albums/sample/sample2.jpg', $images[0]['location']);
        $this->assertEquals(300, $images[0]['width']);
        $this->assertEquals(400, $images[0]['height']);
        $this->assertEquals(1, $images[0]['active']);
        $downloadRights = $this->sql->getRow("SELECT * FROM `download_rights` WHERE `download_rights`.`album` = 999;");
        $this->assertEquals('*', $downloadRights['user']);
        $this->assertEquals(999, $downloadRights['album']);
        $this->assertEquals(0, $downloadRights['image']);
        $this->assertFalse(file_exists('content/albums/sample/sample1.jpg'));
        $this->assertFalse(file_exists('content/albums/sample/full/sample1.jpg'));
    }
}

?>