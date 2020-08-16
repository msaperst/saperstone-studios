<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class MakeThumbsTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`) VALUES ('998', 'sample-album', 'sample album for testing', 'sample', 5);");
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`) VALUES ('999', 'sample-album', 'sample album for testing', 'sample', 4);");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (NULL, '998', '', '1', '', '/albums/sample/flower1.jpeg', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (NULL, '998', '', '2', '', '/albums/sample/flower2.jpeg', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (NULL, '999', '', '1', '', '/albums/sample/flower1.jpeg', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (NULL, '999', '', '2', '', '/albums/sample/flower2.jpeg', '300', '400', '1');");
        $oldmask = umask(0);
        mkdir(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/full', 0777, true);
        copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg');
        copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg');
        chmod(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg', 0777);
        chmod(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg', 0777);
        umask($oldmask);
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 998;");
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 998;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 999;");
        $this->sql->executeStatement("DELETE FROM `user_logs` WHERE `user_logs`.`album` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `album_images` AUTO_INCREMENT = $count;");
        system("rm -rf " . escapeshellarg(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample'));
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/make-thumbs.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals('You must be logged in to perform this action', $e->getResponse()->getBody());
        }
    }

    public function testNoAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/make-thumbs.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id is required", (string)$response->getBody());
    }

    public function testBlankAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/make-thumbs.php', [
            'form_params' => [
                'id' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id can not be blank", (string)$response->getBody());
    }

    public function testLetterAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/make-thumbs.php', [
            'form_params' => [
                'id' => 'a'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string)$response->getBody());
    }

    public function testBadAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/make-thumbs.php', [
            'form_params' => [
                'id' => 9999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string)$response->getBody());
    }

    public function testUploaderCantThumbsOtherAlbum() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
            ], getenv('DB_HOST'));
            $this->http->request('POST', 'api/make-thumbs.php', [
                'form_params' => [
                    'id' => 998
                ],
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(403, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testNoMarkup() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/make-thumbs.php', [
            'form_params' => [
                'id' => 999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Markup is required", (string)$response->getBody());
    }

    public function testBlankMarkup() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/make-thumbs.php', [
            'form_params' => [
                'id' => 999,
                'markup' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Markup can not be blank", (string)$response->getBody());
    }

    public function testInvalidMarkup() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/make-thumbs.php', [
            'form_params' => [
                'id' => 999,
                'markup' => 'pants'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Markup is not valid", (string)$response->getBody());
    }

    public function testAdminCanThumbsAnyAlbum() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/make-thumbs.php', [
            'form_params' => [
                'id' => 998,
                'markup' => 'proof'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        sleep(1);   //waiting for process to complete - ugly, but unsure how to do this dynamically
        //ensure original files are in 'full' directory
        $this->assertTrue($this->files_are_equal(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/full/flower1.jpeg'));
        $this->assertTrue($this->files_are_equal(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/full/flower2.jpeg'));
        //checkout new files
        $images = $this->sql->getRows("SELECT * FROM `album_images` WHERE `album_images`.`album` = 998");
        $this->assertEquals(1000, getimagesize(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg')['0']);
        $this->assertEquals(750, getimagesize(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg')['1']);
        $this->assertEquals(1000, $images[0]['width']);
        $this->assertEquals(750, $images[0]['height']);
        $this->assertTrue($this->files_are_equal(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower-proof.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg'));
        $this->assertEquals(1000, getimagesize(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg')['0']);
        $this->assertEquals(750, getimagesize(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg')['1']);
        $this->assertEquals(1000, $images[1]['width']);
        $this->assertEquals(750, $images[1]['height']);
        $this->assertTrue($this->files_are_equal(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower-proof.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg'));
    }

    public function testUploaderCanThumbsOwnAlbum() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/make-thumbs.php', [
            'form_params' => [
                'id' => 999,
                'markup' => 'watermark'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        sleep(1);   //waiting for process to complete - ugly, but unsure how to do this dynamically
        //ensure original files are in 'full' directory
        $this->assertTrue($this->files_are_equal(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/full/flower1.jpeg'));
        $this->assertTrue($this->files_are_equal(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/full/flower2.jpeg'));
        //checkout new files
        $images = $this->sql->getRows("SELECT * FROM `album_images` WHERE `album_images`.`album` = 999");
        $this->assertEquals(1000, getimagesize(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg')['0']);
        $this->assertEquals(750, getimagesize(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg')['1']);
        $this->assertEquals(1000, $images[0]['width']);
        $this->assertEquals(750, $images[0]['height']);
        $this->assertTrue($this->files_are_equal(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower-watermark.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg'));
        $this->assertEquals(1000, getimagesize(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg')['0']);
        $this->assertEquals(750, getimagesize(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg')['1']);
        $this->assertEquals(1000, $images[0]['width']);
        $this->assertEquals(750, $images[0]['height']);
        $this->assertTrue($this->files_are_equal(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower-watermark.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg'));
        $userLogs = $this->sql->getRows("SELECT * FROM `user_logs` WHERE `user_logs`.`album` = 999;");
        $this->assertEquals(4, $userLogs[0]['user']);
        $this->assertEquals('Created Thumbs', $userLogs[0]['action']);
        $this->assertNull($userLogs[0]['what']);
        $this->assertEquals(999, $userLogs[0]['album']);
    }

    public function testAdminCanThumbsAnyAlbumNothing() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/make-thumbs.php', [
            'form_params' => [
                'id' => 998,
                'markup' => 'none'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        sleep(1);   //waiting for process to complete - ugly, but unsure how to do this dynamically
        //ensure original files are in 'full' directory
        $this->assertTrue($this->files_are_equal(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/full/flower1.jpeg'));
        $this->assertTrue($this->files_are_equal(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/full/flower2.jpeg'));
        //checkout new files
        $images = $this->sql->getRows("SELECT * FROM `album_images` WHERE `album_images`.`album` = 998");
        $this->assertEquals(1000, getimagesize(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg')['0']);
        $this->assertEquals(750, getimagesize(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg')['1']);
        $this->assertEquals(1000, $images[0]['width']);
        $this->assertEquals(750, $images[0]['height']);
        $this->assertTrue($this->files_are_equal(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower-thumbed.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg'));
        $this->assertEquals(1000, getimagesize(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg')['0']);
        $this->assertEquals(750, getimagesize(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg')['1']);
        $this->assertEquals(1000, $images[0]['width']);
        $this->assertEquals(750, $images[0]['height']);
        $this->assertTrue($this->files_are_equal(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower-thumbed.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg'));
    }

    function files_are_equal($a, $b) {
        // Check if filesize is different
        if (filesize($a) !== filesize($b)) {
            return false;
        }
        // Check if content is different
        $ah = fopen($a, 'rb');
        $bh = fopen($b, 'rb');
        $result = true;
        while (!feof($ah)) {
            if (fread($ah, 8192) != fread($bh, 8192)) {
                $result = false;
                break;
            }
        }
        fclose($ah);
        fclose($bh);
        return $result;
    }
}