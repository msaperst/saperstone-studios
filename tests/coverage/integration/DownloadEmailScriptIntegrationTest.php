<?php

namespace coverage\integration;

use CustomAsserts;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';

class DownloadEmailScriptIntegrationTest extends TestCase {
    protected function setUp(): void {
        CustomAsserts::clearAllEmails();
    }

    public function testMissingDownloadTimesOutWithoutSendingReadyEmail(): void {
        $script = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'send-download-email.php';
        $missingFile = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'missing-download.zip';

        @unlink($missingFile);

        $command = sprintf(
            'DOWNLOAD_FILE_WAIT_SECONDS=0 %s -f %s %s %s 2>&1',
            escapeshellarg(PHP_BINARY),
            escapeshellarg($script),
            escapeshellarg('test@example.com'),
            escapeshellarg($missingFile)
        );

        $output = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);

        self::assertSame(1, $exitCode);
        self::assertFalse(file_exists($missingFile));
        CustomAsserts::assertEmailCount(0);
    }
}
