<?php

namespace coverage\unit;

use Filesystem;
use FilesystemException;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class FilesystemUnitTest extends TestCase {

    private string $testRoot;

    protected function setUp(): void {
        $this->testRoot = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR . 'filesystem-test-'
            . bin2hex(random_bytes(8));
    }

    protected function tearDown(): void {
        $this->removeTree($this->testRoot);
    }

    public function testCreatesPublicDirectoryWithExpectedPermissionsAndRestoresUmask(): void {
        $originalMask = umask(0027);
        try {
            $path = $this->testRoot . DIRECTORY_SEPARATOR . 'public';
            Filesystem::createPublicDirectory($path);

            self::assertDirectoryExists($path);
            self::assertSame(0775, fileperms($path) & 0777);
            self::assertSame(0027, umask());
        } finally {
            umask($originalMask);
        }
    }

    public function testCreatesRecursivePublicDirectory(): void {
        $path = $this->testRoot
            . DIRECTORY_SEPARATOR . 'one'
            . DIRECTORY_SEPARATOR . 'two';

        Filesystem::createPublicDirectory($path, true);

        self::assertDirectoryExists($path);
        self::assertSame(0775, fileperms($path) & 0777);
    }

    public function testExistingDirectoryIsLeftAlone(): void {
        self::assertTrue(mkdir($this->testRoot, 0700, true));

        Filesystem::createPublicDirectory($this->testRoot);

        self::assertSame(0700, fileperms($this->testRoot) & 0777);
    }

    public function testCreationFailureRestoresUmask(): void {
        self::assertTrue(mkdir($this->testRoot, 0700, true));
        $blockedParent = $this->testRoot . DIRECTORY_SEPARATOR . 'blocked';
        self::assertNotFalse(file_put_contents($blockedParent, 'not a directory'));

        $originalMask = umask(0027);
        try {
            try {
                Filesystem::createPublicDirectory(
                    $blockedParent . DIRECTORY_SEPARATOR . 'child',
                    true
                );
                self::fail('Expected directory creation failure');
            } catch (FilesystemException $exception) {
                self::assertSame('Unable to create directory', $exception->getMessage());
                self::assertSame(0027, umask());
            }
        } finally {
            umask($originalMask);
        }
    }

    private function removeTree(string $path): void {
        if (!file_exists($path) && !is_link($path)) {
            return;
        }

        if (!is_dir($path) || is_link($path)) {
            @unlink($path);
            return;
        }

        foreach (scandir($path) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $this->removeTree($path . DIRECTORY_SEPARATOR . $entry);
        }
        @rmdir($path);
    }
}
