<?php

namespace api;

use CustomAsserts;
use Exception;
use Gmail;
use Google\Exception as ExceptionAlias;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Sql;
use ZipArchive;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Gmail.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class DownloadSelectedImagesTest extends TestCase {
    /**
     * @var Client
     */
    private $http;
    /**
     * @var Sql
     */
    private $sql;
    /**
     * @var string[]
     */
    private $files = array("file.0.png", "file.1.png", "file.2.png", "file.3.png", "file.4.png");

    /**
     * @throws Exception
     */
    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES (997, 'sample-album-download-all', 'sample album for testing', 'sample');");
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('0', 997, '*');");
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES (998, 'sample-album-download-some', 'sample album for testing', 'sample');");
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('0', 998, '9982');");
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('0', 998, '9983');");
        $this->sql->executeStatement("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('3', 998, '9981');");
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES (999, 'sample-album-no-access', 'sample album for testing without any download access', 'sample');");

        $oldMask = umask(0);
        mkdir(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample');
        chmod(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample', 0777);
        mkdir(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/full');
        chmod(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/full', 0777);
        $counter = 0;
        foreach ($this->files as $file) {
            $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `location`, `width`, `height`, `active`) VALUES ('997$counter', 997, '$file', $counter, '/albums/sample/$file', '600', '400', '1');");
            $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `location`, `width`, `height`, `active`) VALUES ('998$counter', 998, '$file', $counter, '/albums/sample/$file', '600', '400', '1');");
            $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `location`, `width`, `height`, `active`) VALUES ('999$counter', 999, '$file', $counter, '/albums/sample/$file', '600', '400', '1');");
            if ($counter != 4) {
                touch(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "content/albums/sample/$file");
                chmod(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "content/albums/sample/$file", 0777);
                touch(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "content/albums/sample/full/$file");
                chmod(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "content/albums/sample/full/$file", 0777);
            }
            $counter++;
        }
        umask($oldMask);
    }

    /**
     * @throws Exception
     */
    public function tearDown() {
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 997");
        $this->sql->executeStatement("DELETE FROM `download_rights` WHERE `download_rights`.`album` = '997'");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 997");
        $this->sql->executeStatement("DELETE FROM `favorites` WHERE `favorites`.`album` = 997");
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 998");
        $this->sql->executeStatement("DELETE FROM `download_rights` WHERE `download_rights`.`album` = '998'");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 998");
        $this->sql->executeStatement("DELETE FROM `favorites` WHERE `favorites`.`album` = 998");
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 999");
        $this->sql->executeStatement("DELETE FROM `download_rights` WHERE `download_rights`.`album` = '999'");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 999");
        $this->sql->executeStatement("DELETE FROM `favorites` WHERE `favorites`.`album` = 999");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `album_images` AUTO_INCREMENT = $count;");
        system("rm -rf " . escapeshellarg(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample'));
        $this->sql->disconnect();
    }

    /**
     * @throws GuzzleException
     */
    public function testNoWhat() {
        $response = $this->http->request('POST', 'api/download-selected-images.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("What to download is required", json_decode($response->getBody(), true)['error']);
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankWhat() {
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
            'form_params' => [
                'what' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("What to download can not be blank", json_decode($response->getBody(), true)['error']);
    }

    /**
     * @throws GuzzleException
     */
    public function testNoAlbum() {
        $response = $this->http->request('POST', 'api/download-selected-images.php', [
            'form_params' => [
                'what' => 'some-file'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id is required", json_decode($response->getBody(), true)['error']);
    }

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws GuzzleException
     * @throws ExceptionAlias
     */
    public function testUnAuthUserDownloadAllOpen() {
        try {
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'all',
                    'album' => 997
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $x = (string)$response->getBody();
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-all', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(4, $za->numFiles);
            for ($i = 0; $i < $za->numFiles; $i++) {
                $stat = $za->statIndex($i);
                $this->assertEquals("file.$i.png", $stat['name']);
            }
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-all album at %s://%s/user/album.php?album=997

file.0.png
file.1.png
file.2.png
file.3.png

Name: 
Email: 
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=997' target='_blank'>sample-album-download-all</a> album</p><p><ul><li>file.0.png</li><li>file.1.png</li><li>file.2.png</li><li>file.3.png</li></ul></p><br/><p><strong>Name</strong>: <br/><strong>Email</strong>: <a href='mailto:'></a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testUnAuthUserDownloadFavoritesOpen() {
        try {
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 997,
                    'image' => '0'
                ]
            ]);
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 997,
                    'image' => '1'
                ]
            ]);
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 997,
                    'image' => '2'
                ]
            ]);
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'favorites',
                    'album' => 997
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-all', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(3, $za->numFiles);
            for ($i = 0; $i < $za->numFiles; $i++) {
                $stat = $za->statIndex($i);
                $this->assertEquals("file.$i.png", $stat['name']);
            }
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-all album at %s://%s/user/album.php?album=997

file.0.png
file.1.png
file.2.png

Name: 
Email: 
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=997' target='_blank'>sample-album-download-all</a> album</p><p><ul><li>file.0.png</li><li>file.1.png</li><li>file.2.png</li></ul></p><br/><p><strong>Name</strong>: <br/><strong>Email</strong>: <a href='mailto:'></a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testUnAuthUserDownloadBadWhatOpen() {
        try {
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'abc1',
                    'album' => 997
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-all', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.0.png", $za->statIndex(0)['name']);
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-all album at %s://%s/user/album.php?album=997

file.0.png

Name: 
Email: 
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=997' target='_blank'>sample-album-download-all</a> album</p><p><ul><li>file.0.png</li></ul></p><br/><p><strong>Name</strong>: <br/><strong>Email</strong>: <a href='mailto:'></a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testUnAuthUserDownloadSingleOpen() {
        try {
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '1',
                    'album' => 997
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-all', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.1.png", $za->statIndex(0)['name']);
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-all album at %s://%s/user/album.php?album=997

file.1.png

Name: 
Email: 
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=997' target='_blank'>sample-album-download-all</a> album</p><p><ul><li>file.1.png</li></ul></p><br/><p><strong>Name</strong>: <br/><strong>Email</strong>: <a href='mailto:'></a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testUnAuthUserDownloadAllLimited() {
        try {
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'all',
                    'album' => 998
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $x = (string)$response->getBody();
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-some', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(2, $za->numFiles);
            $this->assertEquals("file.2.png", $za->statIndex(0)['name']);
            $this->assertEquals("file.3.png", $za->statIndex(1)['name']);
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-some album at %s://%s/user/album.php?album=998

file.2.png
file.3.png

Name: 
Email: 
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=998' target='_blank'>sample-album-download-some</a> album</p><p><ul><li>file.2.png</li><li>file.3.png</li></ul></p><br/><p><strong>Name</strong>: <br/><strong>Email</strong>: <a href='mailto:'></a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testUnAuthUserDownloadFavoritesLimited() {
        try {
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 998,
                    'image' => '0'
                ]
            ]);
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 998,
                    'image' => '1'
                ]
            ]);
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 998,
                    'image' => '2'
                ]
            ]);
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'favorites',
                    'album' => 998
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-some', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.2.png", $za->statIndex(0)['name']);
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-some album at %s://%s/user/album.php?album=998

file.2.png

Name: 
Email: 
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=998' target='_blank'>sample-album-download-some</a> album</p><p><ul><li>file.2.png</li></ul></p><br/><p><strong>Name</strong>: <br/><strong>Email</strong>: <a href='mailto:'></a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testUnAuthUserDownloadSingleGoodLimited() {
        try {
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '2',
                    'album' => 998
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-some', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.2.png", $za->statIndex(0)['name']);
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-some album at %s://%s/user/album.php?album=998

file.2.png

Name: 
Email: 
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=998' target='_blank'>sample-album-download-some</a> album</p><p><ul><li>file.2.png</li></ul></p><br/><p><strong>Name</strong>: <br/><strong>Email</strong>: <a href='mailto:'></a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws GuzzleException
     */
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

    /**
     * @throws GuzzleException
     */
    public function testUnAuthUserDownloadAllClosed() {
        try {
            $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'all',
                    'album' => 999
                ]
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testUnAuthUserDownloadFavoritesClosed() {
        try {
            $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'favorites',
                    'album' => 999
                ]
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testUnAuthUserDownloadSingleClosed() {
        try {
            $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '1',
                    'album' => 999
                ]
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testAuthUserDownloadAllOpen() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '5510b5e6fffd897c234cafe499f76146'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'all',
                    'album' => 997
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-all', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(4, $za->numFiles);
            for ($i = 0; $i < $za->numFiles; $i++) {
                $stat = $za->statIndex($i);
                $this->assertEquals("file.$i.png", $stat['name']);
            }
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-all album at %s://%s/user/album.php?album=997

file.0.png
file.1.png
file.2.png
file.3.png

Name: Download User
Email: email@example.org
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=997' target='_blank'>sample-album-download-all</a> album</p><p><ul><li>file.0.png</li><li>file.1.png</li><li>file.2.png</li><li>file.3.png</li></ul></p><br/><p><strong>Name</strong>: Download User<br/><strong>Email</strong>: <a href='mailto:email@example.org'>email@example.org</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testAuthUserDownloadFavoritesOpen() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '5510b5e6fffd897c234cafe499f76146'
            ], getenv('DB_HOST'));
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 997,
                    'image' => '0'
                ],
                'cookies' => $cookieJar
            ]);
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 997,
                    'image' => '1'
                ],
                'cookies' => $cookieJar
            ]);
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 997,
                    'image' => '2'
                ],
                'cookies' => $cookieJar
            ]);
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'favorites',
                    'album' => 997
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-all', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(3, $za->numFiles);
            for ($i = 0; $i < $za->numFiles; $i++) {
                $stat = $za->statIndex($i);
                $this->assertEquals("file.$i.png", $stat['name']);
            }
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-all album at %s://%s/user/album.php?album=997

file.0.png
file.1.png
file.2.png

Name: Download User
Email: email@example.org
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=997' target='_blank'>sample-album-download-all</a> album</p><p><ul><li>file.0.png</li><li>file.1.png</li><li>file.2.png</li></ul></p><br/><p><strong>Name</strong>: Download User<br/><strong>Email</strong>: <a href='mailto:email@example.org'>email@example.org</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testAuthUserDownloadSingleOpen() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '5510b5e6fffd897c234cafe499f76146'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '1',
                    'album' => 997
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-all', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.1.png", $za->statIndex(0)['name']);
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-all album at %s://%s/user/album.php?album=997

file.1.png

Name: Download User
Email: email@example.org
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=997' target='_blank'>sample-album-download-all</a> album</p><p><ul><li>file.1.png</li></ul></p><br/><p><strong>Name</strong>: Download User<br/><strong>Email</strong>: <a href='mailto:email@example.org'>email@example.org</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testAuthUserDownloadAllLimited() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '5510b5e6fffd897c234cafe499f76146'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'all',
                    'album' => 998
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $x = (string)$response->getBody();
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-some', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(3, $za->numFiles);
            $this->assertEquals("file.1.png", $za->statIndex(0)['name']);
            $this->assertEquals("file.2.png", $za->statIndex(1)['name']);
            $this->assertEquals("file.3.png", $za->statIndex(2)['name']);
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-some album at %s://%s/user/album.php?album=998

file.1.png
file.2.png
file.3.png

Name: Download User
Email: email@example.org
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=998' target='_blank'>sample-album-download-some</a> album</p><p><ul><li>file.1.png</li><li>file.2.png</li><li>file.3.png</li></ul></p><br/><p><strong>Name</strong>: Download User<br/><strong>Email</strong>: <a href='mailto:email@example.org'>email@example.org</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testAuthUserDownloadFavoritesLimited() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '5510b5e6fffd897c234cafe499f76146'
            ], getenv('DB_HOST'));
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 998,
                    'image' => '0'
                ],
                'cookies' => $cookieJar
            ]);
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 998,
                    'image' => '1'
                ],
                'cookies' => $cookieJar
            ]);
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 998,
                    'image' => '2'
                ],
                'cookies' => $cookieJar
            ]);
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'favorites',
                    'album' => 998
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-some', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(2, $za->numFiles);
            $this->assertEquals("file.1.png", $za->statIndex(0)['name']);
            $this->assertEquals("file.2.png", $za->statIndex(1)['name']);
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-some album at %s://%s/user/album.php?album=998

file.1.png
file.2.png

Name: Download User
Email: email@example.org
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=998' target='_blank'>sample-album-download-some</a> album</p><p><ul><li>file.1.png</li><li>file.2.png</li></ul></p><br/><p><strong>Name</strong>: Download User<br/><strong>Email</strong>: <a href='mailto:email@example.org'>email@example.org</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testAuthUserDownloadSingleGoodLimited() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '5510b5e6fffd897c234cafe499f76146'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '2',
                    'album' => 998
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-some', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.2.png", $za->statIndex(0)['name']);
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-some album at %s://%s/user/album.php?album=998

file.2.png

Name: Download User
Email: email@example.org
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=998' target='_blank'>sample-album-download-some</a> album</p><p><ul><li>file.2.png</li></ul></p><br/><p><strong>Name</strong>: Download User<br/><strong>Email</strong>: <a href='mailto:email@example.org'>email@example.org</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testAuthUserDownloadSingleGoodOtherLimited() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '5510b5e6fffd897c234cafe499f76146'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '1',
                    'album' => 998
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-some', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.1.png", $za->statIndex(0)['name']);
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-some album at %s://%s/user/album.php?album=998

file.1.png

Name: Download User
Email: email@example.org
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=998' target='_blank'>sample-album-download-some</a> album</p><p><ul><li>file.1.png</li></ul></p><br/><p><strong>Name</strong>: Download User<br/><strong>Email</strong>: <a href='mailto:email@example.org'>email@example.org</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testAuthUserDownloadSingleBadLimited() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
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

    /**
     * @throws GuzzleException
     */
    public function testAuthUserDownloadAllClosed() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
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

    /**
     * @throws GuzzleException
     */
    public function testAuthUserDownloadFavoritesClosed() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $this->http->request('POST', 'api/set-favorite.php', [
            'form_params' => [
                'album' => 999,
                'image' => '0'
            ],
            'cookies' => $cookieJar
        ]);
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

    /**
     * @throws GuzzleException
     */
    public function testAuthUserDownloadSingleClosed() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
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

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testAdminUserDownloadAllOpen() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'all',
                    'album' => 997
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-all', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(4, $za->numFiles);
            for ($i = 0; $i < $za->numFiles; $i++) {
                $stat = $za->statIndex($i);
                $this->assertEquals("file.$i.png", $stat['name']);
            }
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-all album at %s://%s/user/album.php?album=997

file.0.png
file.1.png
file.2.png
file.3.png

Name: Max Saperstone
Email: msaperst@gmail.com
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=997' target='_blank'>sample-album-download-all</a> album</p><p><ul><li>file.0.png</li><li>file.1.png</li><li>file.2.png</li><li>file.3.png</li></ul></p><br/><p><strong>Name</strong>: Max Saperstone<br/><strong>Email</strong>: <a href='mailto:msaperst@gmail.com'>msaperst@gmail.com</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testAdminUserDownloadFavoritesOpen() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 997,
                    'image' => '0'
                ],
                'cookies' => $cookieJar
            ]);
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 997,
                    'image' => '1'
                ],
                'cookies' => $cookieJar
            ]);
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 997,
                    'image' => '2'
                ],
                'cookies' => $cookieJar
            ]);
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'favorites',
                    'album' => 997
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-all', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(3, $za->numFiles);
            for ($i = 0; $i < $za->numFiles; $i++) {
                $stat = $za->statIndex($i);
                $this->assertEquals("file.$i.png", $stat['name']);
            }
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-all album at %s://%s/user/album.php?album=997

file.0.png
file.1.png
file.2.png

Name: Max Saperstone
Email: msaperst@gmail.com
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=997' target='_blank'>sample-album-download-all</a> album</p><p><ul><li>file.0.png</li><li>file.1.png</li><li>file.2.png</li></ul></p><br/><p><strong>Name</strong>: Max Saperstone<br/><strong>Email</strong>: <a href='mailto:msaperst@gmail.com'>msaperst@gmail.com</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testAdminUserDownloadSingleOpen() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '1',
                    'album' => 997
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-all', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.1.png", $za->statIndex(0)['name']);
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-all album at %s://%s/user/album.php?album=997

file.1.png

Name: Max Saperstone
Email: msaperst@gmail.com
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=997' target='_blank'>sample-album-download-all</a> album</p><p><ul><li>file.1.png</li></ul></p><br/><p><strong>Name</strong>: Max Saperstone<br/><strong>Email</strong>: <a href='mailto:msaperst@gmail.com'>msaperst@gmail.com</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testAdminUserDownloadAllLimited() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'all',
                    'album' => 998
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-some', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(4, $za->numFiles);
            $this->assertEquals("file.0.png", $za->statIndex(0)['name']);
            $this->assertEquals("file.1.png", $za->statIndex(1)['name']);
            $this->assertEquals("file.2.png", $za->statIndex(2)['name']);
            $this->assertEquals("file.3.png", $za->statIndex(3)['name']);
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-some album at %s://%s/user/album.php?album=998

file.0.png
file.1.png
file.2.png
file.3.png

Name: Max Saperstone
Email: msaperst@gmail.com
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=998' target='_blank'>sample-album-download-some</a> album</p><p><ul><li>file.0.png</li><li>file.1.png</li><li>file.2.png</li><li>file.3.png</li></ul></p><br/><p><strong>Name</strong>: Max Saperstone<br/><strong>Email</strong>: <a href='mailto:msaperst@gmail.com'>msaperst@gmail.com</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testAdminUserDownloadFavoritesLimited() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 998,
                    'image' => '0'
                ],
                'cookies' => $cookieJar
            ]);
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 998,
                    'image' => '1'
                ],
                'cookies' => $cookieJar
            ]);
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 998,
                    'image' => '2'
                ],
                'cookies' => $cookieJar
            ]);
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'favorites',
                    'album' => 998
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-some', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(3, $za->numFiles);
            $this->assertEquals("file.0.png", $za->statIndex(0)['name']);
            $this->assertEquals("file.1.png", $za->statIndex(1)['name']);
            $this->assertEquals("file.2.png", $za->statIndex(2)['name']);
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-some album at %s://%s/user/album.php?album=998

file.0.png
file.1.png
file.2.png

Name: Max Saperstone
Email: msaperst@gmail.com
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=998' target='_blank'>sample-album-download-some</a> album</p><p><ul><li>file.0.png</li><li>file.1.png</li><li>file.2.png</li></ul></p><br/><p><strong>Name</strong>: Max Saperstone<br/><strong>Email</strong>: <a href='mailto:msaperst@gmail.com'>msaperst@gmail.com</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testAdminUserDownloadSingleGoodLimited() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '2',
                    'album' => 998
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-some', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.2.png", $za->statIndex(0)['name']);
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-some album at %s://%s/user/album.php?album=998

file.2.png

Name: Max Saperstone
Email: msaperst@gmail.com
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=998' target='_blank'>sample-album-download-some</a> album</p><p><ul><li>file.2.png</li></ul></p><br/><p><strong>Name</strong>: Max Saperstone<br/><strong>Email</strong>: <a href='mailto:msaperst@gmail.com'>msaperst@gmail.com</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testAdminUserDownloadSingleGoodOtherLimited() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '1',
                    'album' => 998
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-download-some', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.1.png", $za->statIndex(0)['name']);
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-download-some album at %s://%s/user/album.php?album=998

file.1.png

Name: Max Saperstone
Email: msaperst@gmail.com
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=998' target='_blank'>sample-album-download-some</a> album</p><p><ul><li>file.1.png</li></ul></p><br/><p><strong>Name</strong>: Max Saperstone<br/><strong>Email</strong>: <a href='mailto:msaperst@gmail.com'>msaperst@gmail.com</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testAdminUserDownloadSingleBadLimited() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
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

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testAdminUserDownloadAllClosed() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'all',
                    'album' => 999
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-no-access', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(4, $za->numFiles);
            for ($i = 0; $i < $za->numFiles; $i++) {
                $stat = $za->statIndex($i);
                $this->assertEquals("file.$i.png", $stat['name']);
            }
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-no-access album at %s://%s/user/album.php?album=999

file.0.png
file.1.png
file.2.png
file.3.png

Name: Max Saperstone
Email: msaperst@gmail.com
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=999' target='_blank'>sample-album-no-access</a> album</p><p><ul><li>file.0.png</li><li>file.1.png</li><li>file.2.png</li><li>file.3.png</li></ul></p><br/><p><strong>Name</strong>: Max Saperstone<br/><strong>Email</strong>: <a href='mailto:msaperst@gmail.com'>msaperst@gmail.com</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testAdminUserDownloadFavoritesClosed() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $this->http->request('POST', 'api/set-favorite.php', [
                'form_params' => [
                    'album' => 999,
                    'image' => '0'
                ],
                'cookies' => $cookieJar
            ]);
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
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => 'favorites',
                    'album' => 999
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-no-access', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(3, $za->numFiles);
            $this->assertEquals("file.0.png", $za->statIndex(0)['name']);
            $this->assertEquals("file.1.png", $za->statIndex(1)['name']);
            $this->assertEquals("file.2.png", $za->statIndex(2)['name']);
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-no-access album at %s://%s/user/album.php?album=999

file.0.png
file.1.png
file.2.png

Name: Max Saperstone
Email: msaperst@gmail.com
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=999' target='_blank'>sample-album-no-access</a> album</p><p><ul><li>file.0.png</li><li>file.1.png</li><li>file.2.png</li></ul></p><br/><p><strong>Name</strong>: Max Saperstone<br/><strong>Email</strong>: <a href='mailto:msaperst@gmail.com'>msaperst@gmail.com</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testAdminUserDownloadSingleClosed() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '1',
                    'album' => 999
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            $this->assertStringStartsWith('../tmp/sample-album-no-access', $zipFile);
            CustomAsserts::dashedTimeWithin(10, explode('.', explode(' ', $zipFile, 2)[1])[0]);
            system("wget -q 'http://" . getenv('DB_HOST') . ":90/$zipFile' -O download.zip");
            $this->assertTrue(file_exists('download.zip'));
            $za = new ZipArchive();
            $za->open('download.zip');
            $this->assertEquals(1, $za->numFiles);
            $this->assertEquals("file.1.png", $za->statIndex(0)['name']);
            CustomAsserts::assertEmailMatches('Someone Downloaded Something', 'This is an automatically generated message from Saperstone Studios

Downloads have been made from the sample-album-no-access album at %s://%s/user/album.php?album=999

file.1.png

Name: Max Saperstone
Email: msaperst@gmail.com
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7', "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=999' target='_blank'>sample-album-no-access</a> album</p><p><ul><li>file.1.png</li></ul></p><br/><p><strong>Name</strong>: Max Saperstone<br/><strong>Email</strong>: <a href='mailto:msaperst@gmail.com'>msaperst@gmail.com</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/></body></html>");
        } finally {
            unlink('download.zip');
        }
    }

    /**
     * @throws ExceptionAlias
     * @throws GuzzleException
     */
    public function testFileDeletedAfter() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/download-selected-images.php', [
                'form_params' => [
                    'what' => '1',
                    'album' => 999
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $zipFile = json_decode($response->getBody(), true)['file'];
            CustomAsserts::httpCodeEquals('http://' . getenv('DB_HOST') . ":90/$zipFile", 200);
            sleep(65);
            CustomAsserts::httpCodeEquals('http://' . getenv('DB_HOST') . ":90/$zipFile", 404);
        } finally {
            $gmail = new Gmail('Someone Downloaded Something');
            $gmail->deleteEmail();
        }
    }
}