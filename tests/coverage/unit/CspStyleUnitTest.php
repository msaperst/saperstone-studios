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

    public function testMigratedMarkupDoesNotContainDuplicateClassAttributes(): void {
        $root = dirname(__DIR__, 3);
        $files = [
            'public/b-nai-mitzvah/index.php',
            'public/b-nai-mitzvah/photobooth.php',
            'public/blog/new.php',
            'public/blog/post.php',
            'public/commercial/index.php',
            'public/contact.php',
            'public/leighAnn.php',
            'public/portrait/index.php',
            'public/portrait/studio.php',
            'public/wedding/index.php',
            'public/wedding/night.php',
            'public/wedding/photobooth.php',
        ];
        $pattern = "/<[A-Za-z][^<>]*\\bclass\\s*=\\s*(?:\"[^\"]*\"|'[^']*')[^<>]*\\bclass\\s*=/s";

        foreach ($files as $file) {
            $source = file_get_contents($root . DIRECTORY_SEPARATOR . $file);
            preg_match_all($pattern, $source, $matches);
            $this->assertSame([], $matches[0], $file . ': duplicate class attributes');
        }
    }

}
