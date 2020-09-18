<?php

namespace coverage\integration;

use Exception;
use File;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class FileIntegrationTest extends TestCase {

    public function testNullConstructor() {
        try {
            new File(NULL);
        } catch (Exception $e) {
            $this->assertEquals('File(s) are required', $e->getMessage());
        }
    }

    public function testBlankConstructor() {
        try {
            new File('');
        } catch (Exception $e) {
            $this->assertEquals('File(s) can not be blank', $e->getMessage());
        }
    }

    public function testSomeErrorConstructor() {
        $params = [
            'error' => 'error, we failed!'
        ];
        try {
            new File($params);
        } catch (Exception $e) {
            $this->assertEquals('error, we failed!', $e->getMessage());
        }
    }

    public function testNoNameConstructor() {
        $params = [
            'error' => '0'
        ];
        try {
            new File($params);
        } catch (Exception $e) {
            $this->assertEquals('File name is required', $e->getMessage());
        }
    }

    public function testBlankNameConstructor() {
        $params = [
            'error' => '0',
            'name' => ''
        ];
        try {
            new File($params);
        } catch (Exception $e) {
            $this->assertEquals('File name can not be blank', $e->getMessage());
        }
    }

    public function testNoTmpNameConstructor() {
        $params = [
            'error' => '0',
            'name' => 'file'
        ];
        try {
            new File($params);
        } catch (Exception $e) {
            $this->assertEquals('File upload location is required', $e->getMessage());
        }
    }

    public function testBlankTmpNameConstructor() {
        $params = [
            'error' => '0',
            'name' => 'file',
            'tmp_name' => ''
        ];
        try {
            new File($params);
        } catch (Exception $e) {
            $this->assertEquals('File upload location can not be blank', $e->getMessage());
        }
    }

    public function testSingularFileConstructor() {
        $params = [
            'error' => '0',
            'name' => 'file',
            'tmp_name' => '/tmp/file'
        ];
        $file = new File($params);
        $files = $file->getFiles();
        $this->assertEquals(1, sizeOf($files));
        $this->assertEquals('file', $files[0]['name']);
        $this->assertEquals('/tmp/file', $files[0]['tmp_name']);
    }

    public function testMultipleFileConstructor() {
        $params = [
            'error' => '0',
            'name' => ['file1', 'file2'],
            'tmp_name' => ['/tmp/file1', '/tmp/file2']
        ];
        $file = new File($params);
        $files = $file->getFiles();
        $this->assertEquals(2, sizeOf($files));
        $this->assertEquals('file1', $files[0]['name']);
        $this->assertEquals('/tmp/file1', $files[0]['tmp_name']);
        $this->assertEquals('file2', $files[1]['name']);
        $this->assertEquals('/tmp/file2', $files[1]['tmp_name']);
    }

    public function testUploadSingleFile() {
        $params = [
            'error' => '0',
            'name' => 'sample.jpeg',
            'tmp_name' => dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg'
        ];
        $file = new File($params);
        $files = $file->upload(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR);
        $this->assertEquals(1, sizeOf($files));
        $this->assertEquals('sample.jpeg', $files[0]);
        $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'sample.jpeg'));
        unlink(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'sample.jpeg');
    }

    public function testUploadMultipleFile() {
        $params = [
            'error' => '0',
            'name' => ['sample1.jpeg', 'sample2.jpeg'],
            'tmp_name' => [dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg']
        ];
        $file = new File($params);
        $files = $file->upload(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR);
        $this->assertEquals(2, sizeOf($files));
        $this->assertEquals('sample1.jpeg', $files[0]);
        $this->assertEquals('sample2.jpeg', $files[1]);
        $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'sample1.jpeg'));
        unlink(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'sample1.jpeg');
        $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'sample2.jpeg'));
        unlink(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'sample2.jpeg');
    }

    public function testAddToDBSingleFileNoAlbumNoFile() {
        try {
            $sql = new Sql();
            $params = [
                'error' => '0',
                'name' => 'sample.jpeg',
                'tmp_name' => dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg'
            ];
            $file = new File($params);
            $file->upload(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR);
            $file->addToDatabase('album_images', 'albums', 899, 'album', '/albums/sample/');
            $images = $sql->getRows("SELECT * FROM album_images WHERE album = 899");
            $this->assertEquals(1, sizeof($images));
            $this->assertEquals(899, $images[0]['album']);
            $this->assertEquals('sample.jpeg', $images[0]['title']);
            $this->assertEquals(0, $images[0]['sequence']);
            $this->assertEquals('', $images[0]['caption']);
            $this->assertEquals('/albums/sample/sample.jpeg', $images[0]['location']);
            $this->assertEquals(1600, $images[0]['width']);
            $this->assertEquals(1200, $images[0]['height']);
            $this->assertEquals(1, $images[0]['active']);
            $logs = $sql->getRows("SELECT * FROM user_logs WHERE album = 899 ORDER BY time DESC");
            $this->assertEquals(0, sizeof($logs));
            $album = $sql->getRows("SELECT * FROM albums WHERE id = 899");
            $this->assertEquals(0, sizeof($album));
        } finally {
            unlink(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'sample.jpeg');
            $sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 899;");
            $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
            $count++;
            $sql->executeStatement("ALTER TABLE `album_images` AUTO_INCREMENT = $count;");
            $sql->disconnect();
        }
    }

    public function testAddToDBSingleFile() {
        try {
            $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'sample.jpeg');
            $sql = new Sql();
            $sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`, `images`) VALUES ('899', 'sample-album', 'sample album for testing', 'sample', 4, '123', 1);");
            $sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (NULL, '899', 'sample', '1', '', 'location', '100', 300, 1);");
            $params = [
                'error' => '0',
                'name' => 'sample.jpeg',
                'tmp_name' => dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg'
            ];
            $file = new File($params);
            $file->upload(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR);
            $file->addToDatabase('album_images', 'albums', 899, 'album', '/albums/sample/');
            $images = $sql->getRows("SELECT * FROM album_images WHERE album = 899");
            $this->assertEquals(2, sizeof($images));
            $this->assertEquals(899, $images[1]['album']);
            $this->assertEquals('sample.jpeg', $images[1]['title']);
            $this->assertEquals(2, $images[1]['sequence']);
            $this->assertEquals('', $images[1]['caption']);
            $this->assertEquals('/albums/sample/sample.jpeg', $images[1]['location']);
            $this->assertEquals(1600, $images[1]['width']);
            $this->assertEquals(1200, $images[1]['height']);
            $this->assertEquals(1, $images[1]['active']);
            $logs = $sql->getRows("SELECT * FROM user_logs WHERE album = 899 ORDER BY time DESC");
            $this->assertEquals(0, sizeof($logs));
            $album = $sql->getRow("SELECT * FROM albums WHERE id = 899");
            $this->assertEquals(2, $album['images']);
        } finally {
            unset($_SESSION['hash']);
            unlink(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'sample.jpeg');
            $sql->executeStatement("DELETE FROM `user_logs` WHERE `user_logs`.`album` = 899;");
            $sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 899;");
            $sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 899;");
            $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
            $count++;
            $sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
            $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
            $count++;
            $sql->executeStatement("ALTER TABLE `album_images` AUTO_INCREMENT = $count;");
            $sql->disconnect();
        }
    }

    public function testAddToDBMultipleFile() {
        try {
            $_SESSION ['hash'] = "5510b5e6fffd897c234cafe499f76146";
            copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'sample1.jpeg');
            copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'sample2.jpeg');
            $sql = new Sql();
            $sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('898', 'sample-album', 'sample album for testing', 'sample', 4, '123');");
            $params = [
                'error' => '0',
                'name' => ['sample1.jpeg', 'sample2.jpeg'],
                'tmp_name' => [dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg']
            ];
            $file = new File($params);
            $file->upload(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR);
            $file->addToDatabase('album_images', 'albums', 898, 'album', '/albums/sample/');
            $images = $sql->getRows("SELECT * FROM album_images WHERE album = 898");
            $this->assertEquals(2, sizeof($images));
            $this->assertEquals(898, $images[0]['album']);
            $this->assertEquals('sample1.jpeg', $images[0]['title']);
            $this->assertEquals(0, $images[0]['sequence']);
            $this->assertEquals('', $images[0]['caption']);
            $this->assertEquals('/albums/sample/sample1.jpeg', $images[0]['location']);
            $this->assertEquals(1600, $images[0]['width']);
            $this->assertEquals(1200, $images[0]['height']);
            $this->assertEquals(1, $images[0]['active']);
            $this->assertEquals(898, $images[1]['album']);
            $this->assertEquals('sample2.jpeg', $images[1]['title']);
            $this->assertEquals(1, $images[1]['sequence']);
            $this->assertEquals('', $images[1]['caption']);
            $this->assertEquals('/albums/sample/sample2.jpeg', $images[1]['location']);
            $this->assertEquals(1600, $images[1]['width']);
            $this->assertEquals(1200, $images[1]['height']);
            $this->assertEquals(1, $images[1]['active']);
            $logs = $sql->getRows("SELECT * FROM user_logs WHERE album = 898 ORDER BY time DESC");
            $this->assertEquals(3, $logs[0]['user']);
            $this->assertEquals('Added Image', $logs[0]['action']);
            $this->assertEquals(1, $logs[0]['what']);
            $this->assertEquals(898, $logs[0]['album']);
            $this->assertEquals(3, $logs[1]['user']);
            $this->assertEquals('Added Image', $logs[1]['action']);
            $this->assertEquals(0, $logs[1]['what']);
            $this->assertEquals(898, $logs[1]['album']);
            $album = $sql->getRow("SELECT * FROM albums WHERE id = 898");
            $this->assertEquals(2, $album['images']);
        } finally {
            unset($_SESSION['hash']);
            $sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 898;");
            $sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 898;");
            $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
            $count++;
            $sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
            $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
            $count++;
            $sql->executeStatement("ALTER TABLE `album_images` AUTO_INCREMENT = $count;");
            $sql->disconnect();
        }
    }
}