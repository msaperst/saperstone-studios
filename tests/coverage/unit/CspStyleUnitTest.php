<?php

namespace coverage\unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CspStyleUnitTest extends TestCase {
    public function testBrowserSourceDoesNotContainCspBlockedInlineStyles(): void {
        $root = dirname(__DIR__, 3);
        $violations = [];

        foreach (['public', 'templates'] as $directory) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root . DIRECTORY_SEPARATOR . $directory)
            );

            foreach ($iterator as $file) {
                if (!$file->isFile() || !in_array($file->getExtension(), ['php', 'js', 'html'], true)) {
                    continue;
                }

                $content = file_get_contents($file->getPathname());
                $relativePath = substr($file->getPathname(), strlen($root) + 1);

                if (preg_match('/\\sstyle\\s*=\\s*[\\\"\\\']/', $content)) {
                    $violations[] = $relativePath . ': inline style attribute';
                }
                if (preg_match('/<style\\b/i', $content)) {
                    $violations[] = $relativePath . ': inline style block';
                }
                if (preg_match('/\\.attr\\(\\s*[\\\"\\\']style[\\\"\\\']/', $content)
                    || preg_match('/setAttribute\\(\\s*[\\\"\\\']style[\\\"\\\']/', $content)
                    || preg_match('/\\.style\\.cssText\\s*=/', $content)) {
                    $violations[] = $relativePath . ': direct style-attribute mutation';
                }
            }
        }

        $this->assertSame([], $violations, implode(PHP_EOL, $violations));
    }
}
