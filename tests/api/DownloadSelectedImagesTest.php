<?php

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;

$_SERVER ['DOCUMENT_ROOT'] = dirname(__DIR__);
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "src/sql.php";

class DownloadSelectedImagesTest extends TestCase {
    private $http;
    private $sql;
    private $files = array("file.0.png", "file.1.png", "file.2.png", "file.3.png", "file.4.png");

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES (997, 'sample-album-download-all', 'sample album for testing', 'sample');");
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('0', 997, '*');");
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES (998, 'sample-album-download-some', 'sample album for testing', 'sample');");
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('0', 998, '2');");
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('0', 998, '3');");
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('3', 998, '1');");
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES (999, 'sample-album-no-access', 'sample album for testing without any download access', 'sample');");

        $oldmask = umask(0);
        mkdir('content/albums/sample');
        chmod('content/albums/sample', 0777);
        mkdir('content/albums/sample/full');
        chmod('content/albums/sample/full', 0777);
        $counter = 0;
        foreach ($this->files as $file) {
            $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `location`, `width`, `height`, `active`) VALUES (NULL, 997, '$file', $counter, '/albums/sample/$file', '600', '400', '1');");
            $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `location`, `width`, `height`, `active`) VALUES (NULL, 998, '$file', $counter, '/albums/sample/$file', '600', '400', '1');");
            $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `location`, `width`, `height`, `active`) VALUES (NULL, 999, '$file', $counter, '/albums/sample/$file', '600', '400', '1');");
            if ($counter != 4) {
                touch("content/albums/sample/$file");
                chmod("content/albums/sample/$file", 0777);
                touch("content/albums/sample/full/$file");
                chmod("content/albums/sample/full/$file", 0777);
            }
            $counter++;
        }
        umask($oldmask);
    }

    public function tearDown() {
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 997");
        $this->sql->executeStatement("DELETE FROM `download_rights` WHERE `download_rights`.`album` = 997");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 997");
        $this->sql->executeStatement("DELETE FROM `favorites` WHERE `favorites`.`album` = 997");
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 998");
        $this->sql->executeStatement("DELETE FROM `download_rights` WHERE `download_rights`.`album` = 998");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 998");
        $this->sql->executeStatement("DELETE FROM `favorites` WHERE `favorites`.`album` = 998");
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 999");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `album_images` AUTO_INCREMENT = $count;");
        system("rm -rf " . escapeshellarg('content/albums/sample'));
        $this->sql->disconnect();
    }

    public function testNoWhat() {
        $response = $this->http->request('POST', 'api/download-selected-images.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("What to download is required", json_decode($response->getBody(), true)['error']);
    }

    public function testBlankWhat() {
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
            'form_params' => [
                'what' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("What to download can not be blank", json_decode($response->getBody(), true)['error']);
    }

    public function testNoAlbum() {
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
            'form_params' => [
                'what' => 'some-file'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id is required", json_decode($response->getBody(), true)['error']);
    }

    public function testBlankAlbum() {
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
            'form_params' => [
                'what' => 'some-file',
                'album' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id can not be blank", json_decode($response->getBody(), true)['error']);
    }

    public function testLetterAlbum() {
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
            'form_params' => [
                'what' => 'some-file',
                'album' => 'a'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", json_decode($response->getBody(), true)['error']);
    }

    public function testBadAlbumId() {
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
            'form_params' => [
                'what' => 'some-file',
                'album' => 9999
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", json_decode($response->getBody(), true)['error']);
    }

    public function testUnAuthUserDownloadAllOpen() {
        try {
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-all $dateTime.zip";
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'all',
                    'album' => 997
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(4, $za->numFiles);
            for ($i = 0; $i < $za->numFiles; $i++) {
                $stat = $za->statIndex($i);
                $this->assertEquals("file.$i.png", $stat['name']);
            }
        } finally {
            unlink('download.zip');
        }
    }

    public function testUnAuthUserDownloadFavoritesOpen() {
        try {
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 997,
                    'image' => '0'
                ]
            ]);
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 997,
                    'image' => '1'
                ]
            ]);
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 997,
                    'image' => '2'
                ]
            ]);
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-all $dateTime.zip";
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'favorites',
                    'album' => 997
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(3, $za->numFiles);
            for ($i = 0; $i < $za->numFiles; $i++) {
                $stat = $za->statIndex($i);
                $this->assertEquals("file.$i.png", $stat['name']);
            }
        } finally {
            unlink('download.zip');
        }
    }

    public function testUnAuthUserDownloadBadWhatOpen() {
        try {
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-all $dateTime.zip";
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'abc1',
                    'album' => 997
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.0.png", $za->statIndex(0)['name']);
        } finally {
            unlink('download.zip');
        }
    }

    public function testUnAuthUserDownloadSingleOpen() {
        try {
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-all $dateTime.zip";
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '1',
                    'album' => 997
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.1.png", $za->statIndex(0)['name']);
        } finally {
            unlink('download.zip');
        }
    }

    public function testUnAuthUserDownloadSingleMissingOpen() {
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
            'form_params' => [
                'what' => '4',
                'album' => 997
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("No files exist for you to download. Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>contact our System Administrators</a>.", json_decode($response->getBody(), true)['error']);
    }

    public function testUnAuthUserDownloadSingleBadOpen() {
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
            'form_params' => [
                'what' => '5',
                'album' => 997
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('There are no files available for you to download. Please purchase rights to the images you tried to download, and try again.', json_decode($response->getBody(), true)['error']);
    }

    public function testUnAuthUserDownloadAllLimited() {
        try {
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-some $dateTime.zip";
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'all',
                    'album' => 998
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(2, $za->numFiles);
            $this->assertEquals("file.2.png", $za->statIndex(0)['name']);
            $this->assertEquals("file.3.png", $za->statIndex(1)['name']);
        } finally {
            unlink('download.zip');
        }
    }

    public function testUnAuthUserDownloadFavoritesLimited() {
        try {
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 998,
                    'image' => '0'
                ]
            ]);
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 998,
                    'image' => '1'
                ]
            ]);
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 998,
                    'image' => '2'
                ]
            ]);
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-some $dateTime.zip";
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'favorites',
                    'album' => 998
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.2.png", $za->statIndex(0)['name']);
        } finally {
            unlink('download.zip');
        }
    }

    public function testUnAuthUserDownloadSingleGoodLimited() {
        try {
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-some $dateTime.zip";
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '2',
                    'album' => 998
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.2.png", $za->statIndex(0)['name']);
        } finally {
            unlink('download.zip');
        }
    }

    public function testUnAuthUserDownloadSingleBadLimited() {
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
            'form_params' => [
                'what' => '1',
                'album' => 998
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('There are no files available for you to download. Please purchase rights to the images you tried to download, and try again.', json_decode($response->getBody(), true)['error']);
    }

    public function testUnAuthUserDownloadAllClosed() {
        try {
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'all',
                    'album' => 999
                ]
            ]);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testUnAuthUserDownloadFavoritesClosed() {
        try {
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'favorites',
                    'album' => 999
                ]
            ]);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testUnAuthUserDownloadSingleClosed() {
        try {
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '1',
                    'album' => 999
                ]
            ]);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testAuthUserDownloadAllOpen() {
        try {
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-all $dateTime.zip";
            $cookieJar = CookieJar::fromArray([
                'hash' => '5510b5e6fffd897c234cafe499f76146'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'all',
                    'album' => 997
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(4, $za->numFiles);
            for ($i = 0; $i < $za->numFiles; $i++) {
                $stat = $za->statIndex($i);
                $this->assertEquals("file.$i.png", $stat['name']);
            }
        } finally {
            unlink('download.zip');
        }
    }

    public function testAuthUserDownloadFavoritesOpen() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '5510b5e6fffd897c234cafe499f76146'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 997,
                    'image' => '0'
                ],
                'cookies' => $cookieJar
            ]);
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 997,
                    'image' => '1'
                ],
                'cookies' => $cookieJar
            ]);
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 997,
                    'image' => '2'
                ],
                'cookies' => $cookieJar
            ]);
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-all $dateTime.zip";
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'favorites',
                    'album' => 997
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(3, $za->numFiles);
            for ($i = 0; $i < $za->numFiles; $i++) {
                $stat = $za->statIndex($i);
                $this->assertEquals("file.$i.png", $stat['name']);
            }
        } finally {
            unlink('download.zip');
        }
    }

    public function testAuthUserDownloadSingleOpen() {
        try {
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-all $dateTime.zip";
            $cookieJar = CookieJar::fromArray([
                'hash' => '5510b5e6fffd897c234cafe499f76146'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '1',
                    'album' => 997
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.1.png", $za->statIndex(0)['name']);
        } finally {
            unlink('download.zip');
        }
    }

    public function testAuthUserDownloadAllLimited() {
        try {
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-some $dateTime.zip";
            $cookieJar = CookieJar::fromArray([
                'hash' => '5510b5e6fffd897c234cafe499f76146'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'all',
                    'album' => 998
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(3, $za->numFiles);
            $this->assertEquals("file.1.png", $za->statIndex(0)['name']);
            $this->assertEquals("file.2.png", $za->statIndex(1)['name']);
            $this->assertEquals("file.3.png", $za->statIndex(2)['name']);
        } finally {
            unlink('download.zip');
        }
    }

    public function testAuthUserDownloadFavoritesLimited() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '5510b5e6fffd897c234cafe499f76146'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 998,
                    'image' => '0'
                ],
                'cookies' => $cookieJar
            ]);
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 998,
                    'image' => '1'
                ],
                'cookies' => $cookieJar
            ]);
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 998,
                    'image' => '2'
                ],
                'cookies' => $cookieJar
            ]);
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-some $dateTime.zip";
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'favorites',
                    'album' => 998
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(2, $za->numFiles);
            $this->assertEquals("file.1.png", $za->statIndex(0)['name']);
            $this->assertEquals("file.2.png", $za->statIndex(1)['name']);
        } finally {
            unlink('download.zip');
        }
    }

    public function testAuthUserDownloadSingleGoodLimited() {
        try {
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-some $dateTime.zip";
            $cookieJar = CookieJar::fromArray([
                'hash' => '5510b5e6fffd897c234cafe499f76146'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '2',
                    'album' => 998
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.2.png", $za->statIndex(0)['name']);
        } finally {
            unlink('download.zip');
        }
    }

    public function testAuthUserDownloadSingleGoodOtherLimited() {
        try {
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-some $dateTime.zip";
            $cookieJar = CookieJar::fromArray([
                'hash' => '5510b5e6fffd897c234cafe499f76146'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '1',
                    'album' => 998
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.1.png", $za->statIndex(0)['name']);
        } finally {
            unlink('download.zip');
        }
    }

    public function testAuthUserDownloadSingleBadLimited() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
            'form_params' => [
                'what' => '4',
                'album' => 998
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('There are no files available for you to download. Please purchase rights to the images you tried to download, and try again.', json_decode($response->getBody(), true)['error']);
    }

    public function testAuthUserDownloadAllClosed() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
            'form_params' => [
                'what' => 'all',
                'album' => 999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('There are no files available for you to download. Please purchase rights to the images you tried to download, and try again.', json_decode($response->getBody(), true)['error']);
    }

    public function testAuthUserDownloadFavoritesClosed() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/set-favorite.php', [
            'form_params' => [
                'album' => 999,
                'image' => '0'
            ],
            'cookies' => $cookieJar
        ]);
        $response = $this->http->request('POST', 'api/set-favorite.php', [
            'form_params' => [
                'album' => 999,
                'image' => '1'
            ],
            'cookies' => $cookieJar
        ]);
        $response = $this->http->request('POST', 'api/set-favorite.php', [
            'form_params' => [
                'album' => 999,
                'image' => '2'
            ],
            'cookies' => $cookieJar
        ]);
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
            'form_params' => [
                'what' => 'favorites',
                'album' => 999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('There are no files available for you to download. Please purchase rights to the images you tried to download, and try again.', json_decode($response->getBody(), true)['error']);
    }

    public function testAuthUserDownloadSingleClosed() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
            'form_params' => [
                'what' => '1',
                'album' => 999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('There are no files available for you to download. Please purchase rights to the images you tried to download, and try again.', json_decode($response->getBody(), true)['error']);
    }

    public function testAdminUserDownloadAllOpen() {
        try {
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-all $dateTime.zip";
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'all',
                    'album' => 997
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(4, $za->numFiles);
            for ($i = 0; $i < $za->numFiles; $i++) {
                $stat = $za->statIndex($i);
                $this->assertEquals("file.$i.png", $stat['name']);
            }
        } finally {
            unlink('download.zip');
        }
    }

    public function testAdminUserDownloadFavoritesOpen() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 997,
                    'image' => '0'
                ],
                'cookies' => $cookieJar
            ]);
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 997,
                    'image' => '1'
                ],
                'cookies' => $cookieJar
            ]);
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 997,
                    'image' => '2'
                ],
                'cookies' => $cookieJar
            ]);
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-all $dateTime.zip";
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'favorites',
                    'album' => 997
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(3, $za->numFiles);
            for ($i = 0; $i < $za->numFiles; $i++) {
                $stat = $za->statIndex($i);
                $this->assertEquals("file.$i.png", $stat['name']);
            }
        } finally {
            unlink('download.zip');
        }
    }

    public function testAdminUserDownloadSingleOpen() {
        try {
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-all $dateTime.zip";
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '1',
                    'album' => 997
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.1.png", $za->statIndex(0)['name']);
        } finally {
            unlink('download.zip');
        }
    }

    public function testAdminUserDownloadAllLimited() {
        try {
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-some $dateTime.zip";
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'all',
                    'album' => 998
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(4, $za->numFiles);
            $this->assertEquals("file.0.png", $za->statIndex(0)['name']);
            $this->assertEquals("file.1.png", $za->statIndex(1)['name']);
            $this->assertEquals("file.2.png", $za->statIndex(2)['name']);
            $this->assertEquals("file.3.png", $za->statIndex(3)['name']);
        } finally {
            unlink('download.zip');
        }
    }

    public function testAdminUserDownloadFavoritesLimited() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 998,
                    'image' => '0'
                ],
                'cookies' => $cookieJar
            ]);
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 998,
                    'image' => '1'
                ],
                'cookies' => $cookieJar
            ]);
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 998,
                    'image' => '2'
                ],
                'cookies' => $cookieJar
            ]);
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-some $dateTime.zip";
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'favorites',
                    'album' => 998
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(3, $za->numFiles);
            $this->assertEquals("file.0.png", $za->statIndex(0)['name']);
            $this->assertEquals("file.1.png", $za->statIndex(1)['name']);
            $this->assertEquals("file.2.png", $za->statIndex(2)['name']);
        } finally {
            unlink('download.zip');
        }
    }

    public function testAdminUserDownloadSingleGoodLimited() {
        try {
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-some $dateTime.zip";
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '2',
                    'album' => 998
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.2.png", $za->statIndex(0)['name']);
        } finally {
            unlink('download.zip');
        }
    }

    public function testAdminUserDownloadSingleGoodOtherLimited() {
        try {
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-download-some $dateTime.zip";
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '1',
                    'album' => 998
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.1.png", $za->statIndex(0)['name']);
        } finally {
            unlink('download.zip');
        }
    }

    public function testAdminUserDownloadSingleBadLimited() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
            'form_params' => [
                'what' => '4',
                'album' => 998
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("No files exist for you to download. Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>contact our System Administrators</a>.", json_decode($response->getBody(), true)['error']);
    }

    public function testAdminUserDownloadAllClosed() {
        try {
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-no-access $dateTime.zip";
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'all',
                    'album' => 999
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(4, $za->numFiles);
            for ($i = 0; $i < $za->numFiles; $i++) {
                $stat = $za->statIndex($i);
                $this->assertEquals("file.$i.png", $stat['name']);
            }
        } finally {
            unlink('download.zip');
        }
    }

    public function testAdminUserDownloadFavoritesClosed() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 999,
                    'image' => '0'
                ],
                'cookies' => $cookieJar
            ]);
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 999,
                    'image' => '1'
                ],
                'cookies' => $cookieJar
            ]);
            $response = $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 999,
                    'image' => '2'
                ],
                'cookies' => $cookieJar
            ]);
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-no-access $dateTime.zip";
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'favorites',
                    'album' => 999
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(3, $za->numFiles);
            $this->assertEquals("file.0.png", $za->statIndex(0)['name']);
            $this->assertEquals("file.1.png", $za->statIndex(1)['name']);
            $this->assertEquals("file.2.png", $za->statIndex(2)['name']);
        } finally {
            unlink('download.zip');
        }
    }

    public function testAdminUserDownloadSingleClosed() {
        try {
            $dateTime = date("Y-m-d H-i-s");
            $zipFile = "../tmp/sample-album-no-access $dateTime.zip";
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '1',
                    'album' => 999
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals($zipFile, json_decode($response->getBody(), true)['file']);
            system("wget -q 'http://localhost:90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.1.png", $za->statIndex(0)['name']);
        } finally {
            unlink('download.zip');
        }
    }
}

?>