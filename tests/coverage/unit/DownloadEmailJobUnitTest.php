<?php

namespace coverage\unit;

use DownloadEmailJob;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class DownloadEmailJobUnitTest extends TestCase {

    private const MISSING_TOKEN = '00000000000000000000000000000000';
    private const MALFORMED_TOKEN = '11111111111111111111111111111111';

    private array $tokens = [];

    protected function tearDown(): void {
        foreach ($this->tokens as $token) {
            DownloadEmailJob::delete($token);
        }

        foreach ([self::MISSING_TOKEN, self::MALFORMED_TOKEN] as $token) {
            @unlink($this->jobPath($token));
        }
    }

    public function testCreateAndLoadJob(): void {
        $token = DownloadEmailJob::generateToken();
        DownloadEmailJob::create(
            $token,
            'test@example.com',
            '/var/www/public/tmp/sample 1234.zip'
        );
        $this->tokens[] = $token;

        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/D', $token);
        self::assertSame([
            'email' => 'test@example.com',
            'file' => '/var/www/public/tmp/sample 1234.zip'
        ], DownloadEmailJob::load($token));
        self::assertSame(0600, fileperms($this->jobPath($token)) & 0777);
    }

    public function testCreateRejectsInvalidEmail(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Download email address is not valid');

        DownloadEmailJob::create(
            DownloadEmailJob::generateToken(),
            'not-an-email',
            '/tmp/sample.zip'
        );
    }

    public function testLoadRejectsInvalidToken(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Download email job token is not valid');

        DownloadEmailJob::load('../../etc/passwd');
    }

    public function testLoadRejectsMissingJob(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Download email job does not exist');

        DownloadEmailJob::load(self::MISSING_TOKEN);
    }

    public function testLoadRejectsMalformedJob(): void {
        $token = self::MALFORMED_TOKEN;
        $this->ensureJobDirectory();
        file_put_contents($this->jobPath($token), '{not json');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Download email job is not valid');

        DownloadEmailJob::load($token);
    }

    public function testDeleteRemovesJob(): void {
        $token = DownloadEmailJob::generateToken();
        DownloadEmailJob::create($token, 'test@example.com', '/tmp/sample.zip');

        DownloadEmailJob::delete($token);

        self::assertFileDoesNotExist($this->jobPath($token));
    }

    private function ensureJobDirectory(): void {
        $directory = dirname($this->jobPath(self::MISSING_TOKEN));
        if (!is_dir($directory)) {
            mkdir($directory, 0700, true);
        }
    }

    private function jobPath(string $token): string {
        return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . 'saperstone-download-email-jobs'
            . DIRECTORY_SEPARATOR . $token . '.json';
    }
}
