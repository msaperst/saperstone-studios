<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$_SERVER ['DOCUMENT_ROOT'] = dirname ( __DIR__ );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";

class GetAlbumImagesTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement( "INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`) VALUES ('998', 'sample-album', 'sample album for testing', 'sample', 5);" );
        $this->sql->executeStatement( "INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('999', 'sample-album', 'sample album for testing', 'sample', 4, '1234');" );
        $this->sql->executeStatement( "INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (996, '998', '', '1', '', '', '300', '400', '1');" );
        $this->sql->executeStatement( "INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (997, '999', '', '1', '', '', '300', '400', '1');" );
        $this->sql->executeStatement( "INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (998, '999', '', '2', '', '', '300', '400', '1');" );
        $this->sql->executeStatement( "INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (999, '999', '', '3', '', '', '300', '400', '1');" );
        $this->sql->executeStatement( "INSERT INTO `albums_for_users` (`user`, `album`) VALUES (1, '998');" );
        $this->sql->executeStatement( "INSERT INTO `albums_for_users` (`user`, `album`) VALUES (4, '999');" );
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement( "DELETE FROM `albums` WHERE `albums`.`id` = 998;" );
        $this->sql->executeStatement( "DELETE FROM `albums` WHERE `albums`.`id` = 999;" );
        $this->sql->executeStatement( "DELETE FROM `album_images` WHERE `album_images`.`album` = 998;" );
        $this->sql->executeStatement( "DELETE FROM `album_images` WHERE `album_images`.`album` = 999;" );
        $this->sql->executeStatement( "DELETE FROM `albums_for_users` WHERE `albums_for_users`.`album` = 998;" );
        $this->sql->executeStatement( "DELETE FROM `albums_for_users` WHERE `albums_for_users`.`album` = 999;" );
        $count = $this->sql->getRow( "SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement( "ALTER TABLE `albums` AUTO_INCREMENT = $count;" );
        $count = $this->sql->getRow( "SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
        $count++;
        $this->sql->executeStatement( "ALTER TABLE `album_images` AUTO_INCREMENT = $count;" );
        $this->sql->disconnect();
    }

    public function testNoAlbumId() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album-images.php', [
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id is required", json_decode($response->getBody(), true)['error']);
    }

    public function testBlankAlbumId() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album-images.php', [
                'query' => [
                    'albumId' => ''
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id can not be blank", json_decode($response->getBody(), true)['error']);
    }

    public function testLetterAlbumId() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album-images.php', [
                'query' => [
                    'albumId' => 'a'
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", json_decode($response->getBody(), true)['error']);
    }

    public function testBadAlbumId() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album-images.php', [
                'query' => [
                    'albumId' => 9999
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", json_decode($response->getBody(), true)['error']);
    }

    public function testAdminFullView() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album-images.php', [
                'query' => [
                    'albumId' => 998,
                    'start' => 0,
                    'howMany' => 3,
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $albumImages = json_decode($response->getBody(), true);
        $this->assertEquals(1, sizeOf( $albumImages ) );
        $this->assertEquals(996, $albumImages[0]['id'] );
        $this->assertEquals(998, $albumImages[0]['album'] );
        $this->assertEquals('', $albumImages[0]['title'] );
        $this->assertEquals(1, $albumImages[0]['sequence'] );
        $this->assertEquals('', $albumImages[0]['caption'] );
        $this->assertEquals('', $albumImages[0]['location'] );
        $this->assertEquals(300, $albumImages[0]['width'] );
        $this->assertEquals(400, $albumImages[0]['height'] );
        $this->assertEquals(1, $albumImages[0]['active'] );
    }

    public function testViewAll() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album-images.php', [
                'query' => [
                    'albumId' => 999
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $albumImages = json_decode($response->getBody(), true);
        $this->assertEquals(3, sizeOf( $albumImages ) );
        $this->assertEquals(997, $albumImages[0]['id'] );
        $this->assertEquals(999, $albumImages[0]['album'] );
        $this->assertEquals('', $albumImages[0]['title'] );
        $this->assertEquals(1, $albumImages[0]['sequence'] );
        $this->assertEquals('', $albumImages[0]['caption'] );
        $this->assertEquals('', $albumImages[0]['location'] );
        $this->assertEquals(300, $albumImages[0]['width'] );
        $this->assertEquals(400, $albumImages[0]['height'] );
        $this->assertEquals(1, $albumImages[0]['active'] );
        $this->assertEquals(998, $albumImages[1]['id'] );
        $this->assertEquals(999, $albumImages[1]['album'] );
        $this->assertEquals('', $albumImages[1]['title'] );
        $this->assertEquals(2, $albumImages[1]['sequence'] );
        $this->assertEquals('', $albumImages[1]['caption'] );
        $this->assertEquals('', $albumImages[1]['location'] );
        $this->assertEquals(300, $albumImages[1]['width'] );
        $this->assertEquals(400, $albumImages[1]['height'] );
        $this->assertEquals(1, $albumImages[1]['active'] );
        $this->assertEquals(999, $albumImages[2]['id'] );
        $this->assertEquals(999, $albumImages[2]['album'] );
        $this->assertEquals('', $albumImages[2]['title'] );
        $this->assertEquals(3, $albumImages[2]['sequence'] );
        $this->assertEquals('', $albumImages[2]['caption'] );
        $this->assertEquals('', $albumImages[2]['location'] );
        $this->assertEquals(300, $albumImages[2]['width'] );
        $this->assertEquals(400, $albumImages[2]['height'] );
        $this->assertEquals(1, $albumImages[2]['active'] );
    }

    public function testViewSecond() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album-images.php', [
                'query' => [
                    'albumId' => 999,
                    'start' => 2,
                    'howMany' => 1,
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $albumImages = json_decode($response->getBody(), true);
        $this->assertEquals(1, sizeOf( $albumImages ) );
        $this->assertEquals(999, $albumImages[0]['id'] );
        $this->assertEquals(999, $albumImages[0]['album'] );
        $this->assertEquals('', $albumImages[0]['title'] );
        $this->assertEquals(3, $albumImages[0]['sequence'] );
        $this->assertEquals('', $albumImages[0]['caption'] );
        $this->assertEquals('', $albumImages[0]['location'] );
        $this->assertEquals(300, $albumImages[0]['width'] );
        $this->assertEquals(400, $albumImages[0]['height'] );
        $this->assertEquals(1, $albumImages[0]['active'] );
    }

    public function testUnAuthCorrectCode() {
        $searched = array ();
        $searched [999] = md5( "album1234" );
        $cookieJar = CookieJar::fromArray([
                    'searched' => json_encode( $searched )
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album-images.php', [
                'query' => [
                    'albumId' => 999,
                    'start' => 0,
                    'howMany' => 1,
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, sizeOf( json_decode($response->getBody(), true) ) );
    }

    public function testUnAuthIncorrectCode() {
        try {
            $searched = array ();
            $searched [999] = md5( "album123" );
            $cookieJar = CookieJar::fromArray([
                        'searched' => json_encode( $searched )
                    ], 'localhost');
            $response = $this->http->request('GET', 'api/get-album-images.php', [
                    'query' => [
                        'albumId' => 999,
                        'start' => 0,
                        'howMany' => 1,
                    ],
                    'cookies' => $cookieJar
            ]);
        } catch ( GuzzleHttp\Exception\ClientException $e ) {
            $this->assertEquals(403, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody() );
        }
    }

    public function testAuthCorrectCode() {
        $searched = array ();
        $searched [999] = md5( "album1234" );
        $cookieJar = CookieJar::fromArray([
                    'hash' => '5510b5e6fffd897c234cafe499f76146',
                    'searched' => json_encode( $searched )
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album-images.php', [
                'query' => [
                    'albumId' => 999,
                    'start' => 0,
                    'howMany' => 1,
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, sizeOf( json_decode($response->getBody(), true) ) );
    }

    public function testAuthAccessCorrectCode() {
        $searched = array ();
        $searched [999] = md5( "album1234" );
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7',
                    'searched' => json_encode( $searched )
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album-images.php', [
                'query' => [
                    'albumId' => 999,
                    'start' => 0,
                    'howMany' => 1,
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, sizeOf( json_decode($response->getBody(), true) ) );
    }

    public function testAuthWithAccess() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album-images.php', [
                'query' => [
                    'albumId' => 999,
                    'start' => 0,
                    'howMany' => 1,
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, sizeOf( json_decode($response->getBody(), true) ) );
    }

    public function testAuthIncorrectCodeWithAccess() {
        $searched = array ();
        $searched [999] = md5( "album123" );
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7',
                    'searched' => json_encode( $searched )
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album-images.php', [
                'query' => [
                    'albumId' => 999,
                    'start' => 0,
                    'howMany' => 1,
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, sizeOf( json_decode($response->getBody(), true) ) );
    }

    public function testAuthIncorrectCodeWithoutAccess() {
        try {
            $searched = array ();
            $searched [999] = md5( "album123" );
            $cookieJar = CookieJar::fromArray([
                        'hash' => '5510b5e6fffd897c234cafe499f76146',
                        'searched' => json_encode( $searched )
                    ], 'localhost');
            $response = $this->http->request('GET', 'api/get-album-images.php', [
                    'query' => [
                        'albumId' => 999,
                        'start' => 0,
                        'howMany' => 1,
                    ],
                    'cookies' => $cookieJar
            ]);
        } catch ( GuzzleHttp\Exception\ClientException $e ) {
            $this->assertEquals(403, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody() );
        }
    }
}
?>