<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$_SERVER ['DOCUMENT_ROOT'] = dirname ( __DIR__ );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";

class DeleteAlbumTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement( "INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`) VALUES ('998', 'sample-album', 'sample album for testing', 'sample', 5);" );
        $this->sql->executeStatement( "INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`) VALUES ('999', 'sample-album', 'sample album for testing', 'sample', 4);" );
        $this->sql->executeStatement( "INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (NULL, '998', '', '1', '', '', '300', '400', '1');" );
        $this->sql->executeStatement( "INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (NULL, '999', '', '1', '', '', '300', '400', '1');" );
        $this->sql->executeStatement( "INSERT INTO `albums_for_users` (`user`, `album`) VALUES (1, '998');" );
        $this->sql->executeStatement( "INSERT INTO `albums_for_users` (`user`, `album`) VALUES (1, '999');" );
        $oldmask = umask(0);
        mkdir( 'content/albums/sample' );
        chmod( 'content/albums/sample', 0777 );
        touch( 'content/albums/sample/sample.jpg' );
        chmod( 'content/albums/sample/sample.jpg', 0777 );
        umask($oldmask);
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
        system ( "rm -rf " . escapeshellarg ( 'content/albums/sample' ) );
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        $response;
        try {
            $response = $this->http->request('POST', 'api/delete-album.php');
        } catch ( GuzzleHttp\Exception\ClientException $e ) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody() );
        }
    }
    
    public function testNoAlbumId() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-album.php', [
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id is required", (string) $response->getBody());
    }

    public function testBlankAlbumId() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-album.php', [
                'form_params' => [
                    'id' => ''
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id can not be blank", (string) $response->getBody());
    }

    public function testLetterAlbumId() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-album.php', [
                'form_params' => [
                    'id' => 'a'
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string) $response->getBody());
    }

    public function testBadAlbumId() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-album.php', [
                'form_params' => [
                    'id' => 9999
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string) $response->getBody());
    }

    public function testUploaderCantDeleteOtherAlbum() {
        $response;
        try {
            $cookieJar = CookieJar::fromArray([
                        'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                    ], 'localhost');
            $response = $this->http->request('POST', 'api/delete-album.php', [
                    'form_params' => [
                        'id' => 998
                    ],
                    'cookies' => $cookieJar
            ]);
        } catch ( GuzzleHttp\Exception\ClientException $e ) {
            $this->assertEquals(403, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody() );
            $this->assertEquals( 1, $this->sql->getRowCount( "SELECT * FROM `albums` WHERE `albums`.`id` = 998;" ) );
            $this->assertEquals( 1, $this->sql->getRowCount( "SELECT * FROM `album_images` WHERE `album_images`.`album` = 998;" ) );
            $this->assertEquals( 1, $this->sql->getRowCount( "SELECT * FROM `albums_for_users` WHERE `albums_for_users`.`album` = 998;" ) );
            $this->assertTrue( file_exists( 'content/albums/sample/sample.jpg' ) );
        }
    }

    public function testAdminCanDeleteAnyAlbum() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-album.php', [
                'form_params' => [
                    'id' => 998
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string) $response->getBody() );
        $this->assertEquals( 0, $this->sql->getRowCount( "SELECT * FROM `albums` WHERE `albums`.`id` = 998;" ) );
        $this->assertEquals( 0, $this->sql->getRowCount( "SELECT * FROM `album_images` WHERE `album_images`.`album` = 998;" ) );
        $this->assertEquals( 0, $this->sql->getRowCount( "SELECT * FROM `albums_for_users` WHERE `albums_for_users`.`album` = 998;" ) );
        $this->assertFalse( file_exists( 'content/albums/sample/sample.jpg' ) );
    }

    public function testUploaderCanDeleteOwnAlbum() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-album.php', [
                'form_params' => [
                    'id' => 999
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string) $response->getBody() );
        $this->assertEquals( 0, $this->sql->getRowCount( "SELECT * FROM `albums` WHERE `albums`.`id` = 999;" ) );
        $this->assertEquals( 0, $this->sql->getRowCount( "SELECT * FROM `album_images` WHERE `album_images`.`album` = 999;" ) );
        $this->assertEquals( 0, $this->sql->getRowCount( "SELECT * FROM `albums_for_users` WHERE `albums_for_users`.`album` = 999;" ) );
        $this->assertFalse( file_exists( 'content/albums/sample/sample.jpg' ) );
    }
}
?>