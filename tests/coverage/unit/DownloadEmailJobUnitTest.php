<?php

namespace coverage\unit;

use DownloadEmailJob;
use DownloadEmailJobException;
use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class DownloadEmailJobUnitTest extends TestCase {

    private const MISSING_TOKEN = '00000000000000000000000000000000';
    private const MALFORMED_TOKEN = '11111111111111111111111111111111';
    private const UNWRITABLE_TOKEN = '22222222222222222222222222222222';
    private const INVALID_JOB_TOKEN = '33333333333333333333333333333333';

    private string $testRoot;
    private string $jobDirectory;
    private DownloadEmailJob $jobStore;

    protected function setUp(): void {
        $this->testRoot = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR . 'download-email-job-test-'
            . bin2hex(random_bytes(8));
        $this->jobDirectory = $this->testRoot . DIRECTORY_SEPARATOR . 'jobs';
        $this->jobStore = new DownloadEmailJob($this->jobDirectory);
    }

    protected function tearDown(): void {
        $this->removeTree($this->testRoot);
    }

    public function testCreateAndLoadJob(): void {
        $token = DownloadEmailJob::generateToken();
        $this->jobStore->create(
            $token,
            'test@example.com',
            '/var/www/public/tmp/sample 1234.zip'
        );

        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/D', $token);
        self::assertSame([
            'email' => 'test@example.com',
            'file' => '/var/www/public/tmp/sample 1234.zip'
        ], $this->jobStore->load($token));
        self::assertSame(0600, fileperms($this->jobPath($token)) & 0777);
    }

    public function testCreateRejectsInvalidEmail(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Download email address is not valid');

        $this->jobStore->create(
            DownloadEmailJob::generateToken(),
            'not-an-email',
            '/tmp/sample.zip'
        );
    }

    public function testCreateReportsDirectoryCreationFailure(): void {
        self::assertTrue(mkdir($this->testRoot, 0700, true));
        $blockedParent = $this->testRoot . DIRECTORY_SEPARATOR . 'blocked';
        self::assertNotFalse(file_put_contents($blockedParent, 'not a directory'));

        $jobStore = new DownloadEmailJob($blockedParent . DIRECTORY_SEPARATOR . 'jobs');

        $this->expectException(DownloadEmailJobException::class);
        $this->expectExceptionMessage('Unable to create download email job directory');

        $jobStore->create(
            DownloadEmailJob::generateToken(),
            'test@example.com',
            '/tmp/sample.zip'
        );
    }

    public function testCreateReportsJobWriteFailure(): void {
        self::assertTrue(mkdir($this->jobDirectory, 0700, true));
        self::assertTrue(mkdir($this->jobPath(self::UNWRITABLE_TOKEN), 0700));

        $this->expectException(DownloadEmailJobException::class);
        $this->expectExceptionMessage('Unable to create download email job');

        $this->jobStore->create(
            self::UNWRITABLE_TOKEN,
            'test@example.com',
            '/tmp/sample.zip'
        );
    }

    public function testLoadRejectsInvalidToken(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Download email job token is not valid');

        $this->jobStore->load('../../etc/passwd');
    }

    public function testLoadRejectsMissingJob(): void {
        $this->expectException(DownloadEmailJobException::class);
        $this->expectExceptionMessage('Download email job does not exist');

        $this->jobStore->load(self::MISSING_TOKEN);
    }

    public function testLoadRejectsMalformedJson(): void {
        self::assertTrue(mkdir($this->jobDirectory, 0700, true));
        self::assertNotFalse(file_put_contents($this->jobPath(self::MALFORMED_TOKEN), '{not json'));

        try {
            $this->jobStore->load(self::MALFORMED_TOKEN);
            self::fail('Expected malformed job JSON to be rejected');
        } catch (DownloadEmailJobException $exception) {
            self::assertSame('Download email job is not valid', $exception->getMessage());
            self::assertInstanceOf(JsonException::class, $exception->getPrevious());
        }
    }

    public function testLoadRejectsInvalidJobStructure(): void {
        self::assertTrue(mkdir($this->jobDirectory, 0700, true));
        self::assertNotFalse(file_put_contents(
            $this->jobPath(self::INVALID_JOB_TOKEN),
            json_encode(['email' => 'not-an-email', 'file' => '/tmp/sample.zip'], JSON_THROW_ON_ERROR)
        ));

        $this->expectException(DownloadEmailJobException::class);
        $this->expectExceptionMessage('Download email job is not valid');

        $this->jobStore->load(self::INVALID_JOB_TOKEN);
    }

    public function testDeleteRemovesJob(): void {
        $token = DownloadEmailJob::generateToken();
        $this->jobStore->create($token, 'test@example.com', '/tmp/sample.zip');

        $this->jobStore->delete($token);

        self::assertFileDoesNotExist($this->jobPath($token));
    }

    public function testDeleteIgnoresMissingJob(): void {
        $this->jobStore->delete(self::MISSING_TOKEN);

        self::assertFileDoesNotExist($this->jobPath(self::MISSING_TOKEN));
    }

    private function jobPath(string $token): string {
        return $this->jobDirectory . DIRECTORY_SEPARATOR . $token . '.json';
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
