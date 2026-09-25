<?php

namespace api;

use CustomAsserts;
use Exception;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';

class MakeThumbsTest extends TestCase {
    /**
     * @var Client
     */
    private $http;
    /**
     * @var Sql
     */
    private $sql;

    private function waitForThumbnailStatus(int $albumId, int $expectedStatus, int $timeoutSeconds = 10): void {
        $start = microtime(true);
        do {
            $status = (int)$this->sql->getRow("SELECT thumbsCreated FROM albums WHERE id = ?", [$albumId])['thumbsCreated'];
            if ($status === $expectedStatus) {
                return;
            }
            usleep(100000);
        } while (microtime(true) - $start < $timeoutSeconds);

        $this->fail("Album $albumId thumbnail status did not become $expectedStatus within $timeoutSeconds seconds");
    }

    /**
     * @throws Exception
     */
    public function setUp(): void {
        $this->http = new ApiTestClient(['base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`) VALUES ('998', 'sample-album', 'sample album for testing', 'sample', 5);");
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`) VALUES ('999', 'sample-album', 'sample album for testing', 'sample', 4);");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (NULL, '998', '', '1', '', '/albums/sample/flower1.jpeg', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (NULL, '998', '', '2', '', '/albums/sample/flower2.jpeg', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (NULL, '999', '', '1', '', '/albums/sample/flower1.jpeg', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (NULL, '999', '', '2', '', '/albums/sample/flower2.jpeg', '300', '400', '1');");
        $oldMask = umask(0);
        mkdir(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/full', 0777, true);
        copy(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg');
        copy(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg');
        chmod(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg', 0777);
        chmod(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg', 0777);
        umask($oldMask);
    }

    /**
     * @throws Exception
     */
    public function tearDown(): void {
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
        system("rm -rf " . escapeshellarg(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample'));
        $this->sql->disconnect();
    }

    /**
     * @throws GuzzleException
     */
    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/make-thumbs.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals('You must be logged in to perform this action', $e->getResponse()->getBody());
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testNoAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/make-thumbs.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("Album id is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
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
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("Album id can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
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
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
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
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws GuzzleException
     */
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
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("Markup is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
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
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("Markup can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
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
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("Markup is not valid", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testInvalidMode() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/make-thumbs.php', [
            'form_params' => [
                'id' => 998,
                'markup' => 'proof',
                'mode' => 'pants'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("Thumbnail mode is not valid", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testRecreateAllReappliesTreatmentFromOriginals() {
        copy(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/full/flower1.jpeg');
        copy(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/full/flower2.jpeg');
        copy(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower-proof.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg');
        copy(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower-proof.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg');

        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/make-thumbs.php', [
            'form_params' => [
                'id' => 998,
                'markup' => 'watermark',
                'mode' => 'all'
            ],
            'cookies' => $cookieJar
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        sleep(5);

        CustomAsserts::filesAreEqual(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/full/flower1.jpeg');
        CustomAsserts::filesAreEqual(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower-watermark.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg');
        CustomAsserts::filesAreEqual(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower-watermark.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg');
    }

    /**
     * @throws GuzzleException
     */
    public function testAdminCreatingThumbsUpdatesAlbumStatus() {
        $this->sql->executeStatement("UPDATE albums SET thumbsCreated = FALSE WHERE id = 998");
        $this->assertEquals(0, $this->sql->getRow("SELECT thumbsCreated FROM albums WHERE id = 998")['thumbsCreated']);

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
        $this->waitForThumbnailStatus(998, 1);
        $this->assertEquals(1, $this->sql->getRow("SELECT thumbsCreated FROM albums WHERE id = 998")['thumbsCreated']);
    }

    /**
     * @throws GuzzleException
     */
    public function testUploaderCreatingThumbsUpdatesAlbumStatus() {
        $this->sql->executeStatement("UPDATE albums SET thumbsCreated = FALSE WHERE id = 999");
        $this->assertEquals(0, $this->sql->getRow("SELECT thumbsCreated FROM albums WHERE id = 999")['thumbsCreated']);

        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/make-thumbs.php', [
            'form_params' => [
                'id' => 999,
                'markup' => 'none'
            ],
            'cookies' => $cookieJar
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $this->waitForThumbnailStatus(999, 1);
        $this->assertEquals(1, $this->sql->getRow("SELECT thumbsCreated FROM albums WHERE id = 999")['thumbsCreated']);
    }

    private function assertResponsiveDerivatives(string $filename): void {
        $base = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/';
        $full = $base . 'full/' . $filename;
        $small = $base . 'thumbs/400/' . $filename;
        $medium = $base . 'thumbs/800/' . $filename;
        $large = $base . $filename;

        $this->assertFileExists($full);
        $this->assertFileExists($small);
        $this->assertFileExists($medium);
        $this->assertFileExists($large);
        CustomAsserts::filesAreEqual(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', $full);

        $smallSize = getimagesize($small);
        $mediumSize = getimagesize($medium);
        $largeSize = getimagesize($large);
        $this->assertLessThanOrEqual(400, max($smallSize[0], $smallSize[1]));
        $this->assertLessThanOrEqual(800, max($mediumSize[0], $mediumSize[1]));
        $this->assertLessThanOrEqual(1600, max($largeSize[0], $largeSize[1]));
    }

    /**
     * @throws GuzzleException
     */
    public function testFirstThumbnailGenerationPreservesRootOriginalAndCreatesResponsiveDerivatives() {
        $base = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/';
        $this->assertFileExists($base . 'flower1.jpeg');
        $this->assertFileDoesNotExist($base . 'full/flower1.jpeg');
        $this->assertFileDoesNotExist($base . 'thumbs/400/flower1.jpeg');
        $this->assertFileDoesNotExist($base . 'thumbs/800/flower1.jpeg');

        $originalHash = hash_file('sha256', $base . 'flower1.jpeg');

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
        $this->waitForThumbnailStatus(998, 1);

        $this->assertResponsiveDerivatives('flower1.jpeg');
        $this->assertResponsiveDerivatives('flower2.jpeg');
        $this->assertEquals($originalHash, hash_file('sha256', $base . 'full/flower1.jpeg'));
        $this->assertNotEquals($originalHash, hash_file('sha256', $base . 'flower1.jpeg'));

        $images = $this->sql->getRows("SELECT * FROM album_images WHERE album = 998 ORDER BY sequence");
        $largeSize = getimagesize($base . 'flower1.jpeg');
        $this->assertEquals($largeSize[0], (int)$images[0]['width']);
        $this->assertEquals($largeSize[1], (int)$images[0]['height']);
    }

    /**
     * @throws GuzzleException
     */
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
        sleep(5);   //waiting for process to complete - ugly, but unsure how to do this dynamically
        //ensure original files are in 'full' directory
        CustomAsserts::filesAreEqual(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/full/flower1.jpeg');
        CustomAsserts::filesAreEqual(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/full/flower2.jpeg');
        //checkout new files
        $images = $this->sql->getRows("SELECT * FROM `album_images` WHERE `album_images`.`album` = 998");
        $this->assertEquals(1000, getimagesize(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg')['0']);
        $this->assertEquals(750, getimagesize(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg')['1']);
        $this->assertEquals(1000, $images[0]['width']);
        $this->assertEquals(750, $images[0]['height']);
        CustomAsserts::filesAreEqual(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower-proof.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg');
        $this->assertEquals(1000, getimagesize(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg')['0']);
        $this->assertEquals(750, getimagesize(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg')['1']);
        $this->assertEquals(1000, $images[1]['width']);
        $this->assertEquals(750, $images[1]['height']);
        CustomAsserts::filesAreEqual(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower-proof.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg');
    }

    /**
     * @throws GuzzleException
     */
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
        sleep(5);   //waiting for process to complete - ugly, but unsure how to do this dynamically
        //ensure original files are in 'full' directory
        CustomAsserts::filesAreEqual(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/full/flower1.jpeg');
        CustomAsserts::filesAreEqual(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/full/flower2.jpeg');
        //checkout new files
        $images = $this->sql->getRows("SELECT * FROM `album_images` WHERE `album_images`.`album` = 999");
        $this->assertEquals(1000, getimagesize(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg')['0']);
        $this->assertEquals(750, getimagesize(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg')['1']);
        $this->assertEquals(1000, $images[0]['width']);
        $this->assertEquals(750, $images[0]['height']);
        CustomAsserts::filesAreEqual(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower-watermark.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg');
        $this->assertEquals(1000, getimagesize(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg')['0']);
        $this->assertEquals(750, getimagesize(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg')['1']);
        $this->assertEquals(1000, $images[0]['width']);
        $this->assertEquals(750, $images[0]['height']);
        CustomAsserts::filesAreEqual(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower-watermark.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg');
        $userLogs = $this->sql->getRows("SELECT * FROM `user_logs` WHERE `user_logs`.`album` = 999;");
        $this->assertEquals(4, $userLogs[0]['user']);
        $this->assertEquals('Created Thumbs', $userLogs[0]['action']);
        $this->assertNull($userLogs[0]['what']);
        $this->assertEquals(999, $userLogs[0]['album']);
    }

    /**
     * @throws GuzzleException
     */
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
        CustomAsserts::filesAreEqual(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/full/flower1.jpeg');
        CustomAsserts::filesAreEqual(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/full/flower2.jpeg');
        //checkout new files
        $images = $this->sql->getRows("SELECT * FROM `album_images` WHERE `album_images`.`album` = 998");
        $this->assertEquals(1000, getimagesize(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg')['0']);
        $this->assertEquals(750, getimagesize(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg')['1']);
        $this->assertEquals(1000, $images[0]['width']);
        $this->assertEquals(750, $images[0]['height']);
        CustomAsserts::filesAreEqual(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower-thumbed.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower1.jpeg');
        $this->assertEquals(1000, getimagesize(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg')['0']);
        $this->assertEquals(750, getimagesize(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg')['1']);
        $this->assertEquals(1000, $images[0]['width']);
        $this->assertEquals(750, $images[0]['height']);
        CustomAsserts::filesAreEqual(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests/resources/flower-thumbed.jpeg', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower2.jpeg');
    }
}
