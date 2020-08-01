<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$_SERVER ['DOCUMENT_ROOT'] = dirname ( __DIR__ );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";

class DownloadSelectedImagesTest extends TestCase {
    private $http;
    private $sql;
    private $files = array( "file.1.png", "file.2.png", "file.3.png", "file.4.png", "file.5.png" );

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement( "INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES (997, 'sample-album-download-all', 'sample album for testing', 'sample');" );
        $this->sql->executeStatement( "INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('0', 997, '*');" );
        $this->sql->executeStatement( "INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES (998, 'sample-album-download-some', 'sample album for testing', 'sample');" );
        $this->sql->executeStatement( "INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('0', 998, '2');" );
        $this->sql->executeStatement( "INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('0', 998, '3');" );
        $this->sql->executeStatement( "INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES (999, 'sample-album-no-access', 'sample album for testing without any download access', 'sample');");

        $oldmask = umask(0);
        mkdir( 'content/albums/sample' );
        chmod( 'content/albums/sample', 0777 );
        mkdir( 'content/albums/sample/full' );
        chmod( 'content/albums/sample/full', 0777 );
        $counter = 1;
        foreach ( $this->files as $file ) {
            $this->sql->executeStatement( "INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `location`, `width`, `height`, `active`) VALUES (NULL, 997, '$file', $counter, '/albums/sample/$file', '600', '400', '1');");
            $this->sql->executeStatement( "INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `location`, `width`, `height`, `active`) VALUES (NULL, 998, '$file', $counter, '/albums/sample/$file', '600', '400', '1');");
            touch( 'content/albums/sample/$file' );
            chmod( 'content/albums/sample/$file', 0777 );
            touch( 'content/albums/sample/full/$file' );
            chmod( 'content/albums/sample/full/$file', 0777 );
            $counter++;
        }
        umask($oldmask);
    }

    public function tearDown() {
        $this->sql->executeStatement( "DELETE FROM `albums` WHERE `albums`.`id` = 997");
        $this->sql->executeStatement( "DELETE FROM `download_rights` WHERE `download_rights`.`album` = 997");
        $this->sql->executeStatement( "DELETE FROM `album_images` WHERE `album_images`.`album` = 997");
        $this->sql->executeStatement( "DELETE FROM `favorites` WHERE `favorites`.`album` = 997");
        $this->sql->executeStatement( "DELETE FROM `albums` WHERE `albums`.`id` = 998");
        $this->sql->executeStatement( "DELETE FROM `download_rights` WHERE `download_rights`.`album` = 998");
        $this->sql->executeStatement( "DELETE FROM `album_images` WHERE `album_images`.`album` = 998");
        $this->sql->executeStatement( "DELETE FROM `favorites` WHERE `favorites`.`album` = 998");
        $this->sql->executeStatement( "DELETE FROM `albums` WHERE `albums`.`id` = 999");
        $count = $this->sql->getRow( "SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement( "ALTER TABLE `albums` AUTO_INCREMENT = $count;" );
        $count = $this->sql->getRow( "SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
        $count++;
        $this->sql->executeStatement( "ALTER TABLE `album_images` AUTO_INCREMENT = $count;" );
        system ( "rm -rf " . escapeshellarg ( 'content/albums/sample' ) );
        $this->sql->disconnect();
        // TODO - find and destroy zip
    }

    public function testNoWhat() {
        $response = $this->http->request('POST', 'api/download-selected-images.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("What to download is required", json_decode($response->getBody())['error'] );
    }

    public function testBlankWhat() {
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => ''
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("What to download can not be blank", json_decode($response->getBody())['error'] );
    }

    public function testNoAlbum() {
       $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'some-file'
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id is required", json_decode($response->getBody())['error'] );
    }

    public function testBlankAlbum() {
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'some-file',
                    'album' => ''
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id can not be blank", json_decode($response->getBody())['error'] );
    }

    public function testLetterAlbum() {
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'some-file',
                    'album' => 'a'
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", json_decode($response->getBody())['error'] );
    }

    public function testBadAlbumId() {
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'some-file',
                    'album' => 9999
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", json_decode($response->getBody())['error'] );
    }

//     public function testUnAuthUserDownloadAllOpen() {
//         $response = $this->http->request('POST', 'api/download-selected-images.php', [
//                 'form_params' => [
//                     'what' => 'all',
//                     'album' => 997
//                 ]
//         ]);
//         $this->assertEquals(200, $response->getStatusCode());
//         $this->assertEquals("", (string) $response->getBody());
//         //TODO - verify zip
//         //TODO - cleanup
//     }
//
//     public function testUnAuthUserDownloadFavoritesOpen() {
//     }
//
//     public function testUnAuthUserDownloadSingleOpen() {
//     }
//
//     public function testUnAuthUserDownloadAllLimited() {
//     }
//
//     public function testUnAuthUserDownloadFavoritesLimited() {
//         // have one work, and one not work
//     }
//
//     public function testUnAuthUserDownloadSingleGoodLimited() {
//     }
//
//     public function testUnAuthUserDownloadSingleBadLimited() {
//     }
//
//     public function testUnAuthUserDownloadAllClosed() {
//     }
//
//     public function testUnAuthUserDownloadFavoritesClosed() {
//     }
//
//     public function testUnAuthUserDownloadSingleClosed() {
//     }

    //TODO - repeat above for logged in user, and admin user


}
?>