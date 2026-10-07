<?php

namespace coverage\integration;

use CustomAsserts;
use DownloadEmailJob;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';

class DownloadEmailScriptIntegrationTest extends TestCase {
    protected function setUp(): void {
        CustomAsserts::clearAllEmails();
    }

    public function testMissingDownloadTimesOutWithoutSendingReadyEmail(): void {
        $script = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'send-download-email.php';
        $missingFile = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'missing-download.zip';

        @unlink($missingFile);
        $token = DownloadEmailJob::create('test@example.com', $missingFile);

        $command = sprintf(
            'SEND_EMAIL_AFTER=0 DOWNLOAD_FILE_WAIT_SECONDS=0 %s -f %s %s 2>&1',
            escapeshellarg(PHP_BINARY),
            escapeshellarg($script),
            escapeshellarg($token)
        );

        $output = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);

        self::assertSame(1, $exitCode);
        self::assertFalse(file_exists($missingFile));
        CustomAsserts::assertEmailCount(0);
    }

    public function testExistingDownloadSendsReadyEmail(): void {
        $script = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'send-download-email.php';
        $file = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'worker-download.zip';

        try {
            touch($file);
            $token = DownloadEmailJob::create('test@example.com', $file);

            $command = sprintf(
                'SEND_EMAIL_AFTER=0 DOWNLOAD_FILE_WAIT_SECONDS=0 %s -f %s %s 2>&1',
                escapeshellarg(PHP_BINARY),
                escapeshellarg($script),
                escapeshellarg($token)
            );

            $output = [];
            $exitCode = 0;
            exec($command, $output, $exitCode);

            self::assertSame(0, $exitCode);
            CustomAsserts::assertEmailCount(1);
            CustomAsserts::assertEmailMatches(
                'test@example.com',
                'noreply@saperstonestudios.com',
                'Your Download Is Ready',
                'You can access your photos at https://saperstonestudios.com/tmp/worker-download.zip

This download will be available for the next 48 hours',
                "<html><body><p>Your download is ready</p><p>You can access your photos at <a href='https://saperstonestudios.com/tmp/worker-download.zip'>https://saperstonestudios.com/tmp/worker-download.zip</a></p><p>This download will be available for the next 48 hours</p></body></html>"
            );
        } finally {
            @unlink($file);
        }
    }
}
