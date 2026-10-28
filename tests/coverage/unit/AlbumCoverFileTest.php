<?php

namespace coverage\unit;

use AlbumCoverFile;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/src/autoloader.php';

class AlbumCoverFileTest extends TestCase {
    private string $root;

    protected function setUp(): void {
        $this->root = sys_get_temp_dir() . '/saperstone-cover-' . bin2hex(random_bytes(6));
        self::assertTrue(mkdir($this->root . '/album', 0775, true));
    }

    protected function tearDown(): void {
        foreach ([
            $this->root . '/album/thumbs/400/sample.gif',
            $this->root . '/album/thumbs/800/sample.gif',
            $this->root . '/album/full/sample.gif',
            $this->root . '/album/sample.gif',
        ] as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        foreach ([
            $this->root . '/album/thumbs/400',
            $this->root . '/album/thumbs/800',
            $this->root . '/album/thumbs',
            $this->root . '/album/full',
            $this->root . '/album',
            $this->root
        ] as $folder) {
            if (is_dir($folder)) {
                rmdir($folder);
            }
        }
    }

    private function image(string $relative): void {
        $target = $this->root . '/album/' . $relative;
        if (!is_dir(dirname($target))) {
            self::assertTrue(mkdir(dirname($target), 0775, true));
        }
        self::assertNotFalse(file_put_contents(
            $target,
            base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==')
        ));
    }

    public function testExisting400DerivativeIsPreferredAndMimeIsVerified(): void {
        $this->image('thumbs/400/sample.gif');
        $this->image('thumbs/800/sample.gif');
        $file = AlbumCoverFile::resolve('album', '/albums/album/sample.gif', false, $this->root);
        self::assertNotNull($file);
        self::assertSame($this->root . '/album/thumbs/400/sample.gif', $file['path']);
        self::assertSame('image/gif', $file['mime']);
    }

    public function testOnlyAnOriginalReturnsNoFile(): void {
        $this->image('sample.gif');
        self::assertNull(AlbumCoverFile::resolve('album', '/albums/album/sample.gif', false, $this->root));
    }

    public function testCompletedMigrationCanUseProtected1600DerivativeWithFullBackup(): void {
        $this->image('sample.gif');
        $this->image('full/sample.gif');
        $file = AlbumCoverFile::resolve('album', '/albums/album/sample.gif', true, $this->root);
        self::assertNotNull($file);
        self::assertSame($this->root . '/album/sample.gif', $file['path']);
    }

    public function testFullBackupAloneDoesNotAuthorizeAnUnfinishedMigration(): void {
        $this->image('sample.gif');
        $this->image('full/sample.gif');
        self::assertNull(AlbumCoverFile::resolve('album', '/albums/album/sample.gif', false, $this->root));
    }

    public function testTraversalAndUnknownPathsAreRejected(): void {
        $this->image('thumbs/400/sample.gif');
        self::assertNull(AlbumCoverFile::resolve('../', '/albums/album/sample.gif', true, $this->root));
        self::assertNull(AlbumCoverFile::resolve('missing', '/albums/missing/sample.gif', true, $this->root));
        self::assertNull(AlbumCoverFile::resolve('', '/albums/album/sample.gif', true, $this->root));
    }

    public function testNonImagesInThumbFolderAreRejected(): void {
        self::assertTrue(mkdir($this->root . '/album/thumbs/400', 0775, true));
        self::assertNotFalse(file_put_contents($this->root . '/album/thumbs/400/sample.gif', 'not an image'));
        self::assertNull(AlbumCoverFile::resolve('album', '/albums/album/sample.gif', true, $this->root));
    }
}
