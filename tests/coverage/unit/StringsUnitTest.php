<?php

namespace coverage\unit;

use PHPUnit\Framework\TestCase;
use Strings;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class StringsUnitTest extends TestCase {

    public function testRandomNegativeLength() {
        $result = Strings::randomString(-1);
        $this->assertEquals(0, strlen($result));
    }

    public function testRandomNoInput() {
        $result = Strings::randomString();
        $this->assertEquals(10, strlen($result));
    }

    public static function lengthRandomDataProvider(): array {
        return array(
            array(
                0
            ),
            array(
                1
            ),
            array(
                9
            ),
            array(
                10
            ),
            array(
                11
            ),
            array(
                99
            ),
            array(
                1000
            ),
            array(
                99999
            )
        );
    }

    /**
     * @dataProvider lengthRandomDataProvider
     */
    public function testRandomGoodInput($length) {
        $result = Strings::randomString($length);
        $this->assertEquals($length, strlen($result));
    }

    public function testRandomOutputVal() {
        $result = Strings::randomString(99999);
        $this->assertEquals(1, preg_match('/^[a-zA-Z0-9]+$/', $result));
    }

    public function testAssetUrlAddsStableModificationTimeVersion() {
        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'asset-url-' . uniqid();
        mkdir($root . DIRECTORY_SEPARATOR . 'js', 0777, true);
        $file = $root . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'app.js';
        file_put_contents($file, 'test');
        touch($file, 1700000000);

        $this->assertEquals('/js/app.js?v=1700000000', Strings::assetUrl('/js/app.js', $root));
        $this->assertEquals('/js/app.js?v=1700000000', Strings::assetUrl('/js/app.js', $root));

        unlink($file);
        rmdir($root . DIRECTORY_SEPARATOR . 'js');
        rmdir($root);
    }

    public function testAssetUrlChangesWhenModificationTimeChanges() {
        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'asset-url-' . uniqid();
        mkdir($root . DIRECTORY_SEPARATOR . 'css', 0777, true);
        $file = $root . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'site.css';
        file_put_contents($file, 'test');
        touch($file, 1700000000);
        $first = Strings::assetUrl('/css/site.css', $root);
        touch($file, 1700000100);
        clearstatcache(true, $file);

        $this->assertEquals('/css/site.css?v=1700000000', $first);
        $this->assertEquals('/css/site.css?v=1700000100', Strings::assetUrl('/css/site.css', $root));

        unlink($file);
        rmdir($root . DIRECTORY_SEPARATOR . 'css');
        rmdir($root);
    }

    public function testAssetUrlPreservesExistingQueryStringAndFragment() {
        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'asset-url-' . uniqid();
        mkdir($root . DIRECTORY_SEPARATOR . 'js', 0777, true);
        $file = $root . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'app.js';
        file_put_contents($file, 'test');
        touch($file, 1700000000);

        $this->assertEquals(
            '/js/app.js?mode=admin&v=1700000000#settings',
            Strings::assetUrl('/js/app.js?mode=admin#settings', $root)
        );

        unlink($file);
        rmdir($root . DIRECTORY_SEPARATOR . 'js');
        rmdir($root);
    }

    public function testAssetUrlReturnsMissingFileUnchanged() {
        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'asset-url-' . uniqid();
        mkdir($root);
        $this->assertEquals('/js/missing.js', Strings::assetUrl('/js/missing.js', $root));
        rmdir($root);
    }

    public function testAssetUrlDoesNotVersionExternalAssets() {
        $this->assertEquals(
            'https://cdn.example.com/app.js',
            Strings::assetUrl('https://cdn.example.com/app.js', '/unused')
        );
        $this->assertEquals(
            '//cdn.example.com/app.css',
            Strings::assetUrl('//cdn.example.com/app.css', '/unused')
        );
    }

    public function testFirstPartyJavascriptAndCssIncludesUseAssetUrl() {
        $roots = array(
            dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public',
            dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'templates'
        );
        $unversioned = array();

        foreach ($roots as $root) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
            foreach ($iterator as $file) {
                if (!$file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $content = file_get_contents($file->getPathname());
                preg_match_all('/\\b(?:src|href)=(["\\'])(.*?)\\1/s', $content, $matches);
                foreach ($matches[2] as $value) {
                    if (preg_match('#^(?!https?:|//|data:)[^<>]+\\.(?:js|css)(?:[?#][^<>]*)?$#i', $value)) {
                        $unversioned[] = str_replace(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR, '', $file->getPathname())
                            . ': ' . $value;
                    }
                }
            }
        }

        $this->assertSame(array(), $unversioned, "Unversioned first-party assets:\n" . implode("\n", $unversioned));
    }

    public function testHTMLEmpty() {
        $result = Strings::textToHTML("");
        $this->assertEquals("", $result);
    }

    public function testHTMLSpaces() {
        $result = Strings::textToHTML("      ");
        $this->assertEquals("      ", $result);
    }

    public function testHTMLTab() {
        $result = Strings::textToHTML("\t");
        $this->assertEquals("&nbsp;&nbsp;&nbsp;&nbsp;", $result);
    }

    public function testHTMLTabAndTest() {
        $result = Strings::textToHTML("Hello\tWorld");
        $this->assertEquals("Hello&nbsp;&nbsp;&nbsp;&nbsp;World", $result);
    }

    public function testHTMLNewline() {
        $result = Strings::textToHTML("\n");
        $this->assertEquals("<br/>", $result);
    }

    public function testHTMLNewlineAndText() {
        $result = Strings::textToHTML("Hello\nWorld");
        $this->assertEquals("Hello<br/>World", $result);
    }

    public function testHtmlAttributeEscapesQuotesAndMarkup() {
        $this->assertEquals(
            'Max&quot; &amp; &lt;script&gt; &#039;test&#039;',
            Strings::escapeHtmlAttribute('Max" & <script> \'test\'')
        );
    }

    public function testHtmlAttributeHandlesNull() {
        $this->assertEquals('', Strings::escapeHtmlAttribute(null));
    }

    public function testCommaSingle() {
        $result = Strings::commaSeparate(array(
            "hello"
        ));
        $this->assertEquals("hello", $result);
    }

    public function testCommaTwo() {
        $result = Strings::commaSeparate(array(
            "hello",
            "world"
        ));
        $this->assertEquals("hello and world", $result);
    }

    public function testCommaThree() {
        $result = Strings::commaSeparate(array(
            "hello",
            "there",
            "world"
        ));
        $this->assertEquals("hello, there and world", $result);
    }

    public function testCommaFour() {
        $result = Strings::commaSeparate(array(
            "hello",
            "there",
            "my",
            "world"
        ));
        $this->assertEquals("hello, there, my and world", $result);
    }

    public function testStartsWithGood() {
        $result = Strings::startsWith("Max", "M");
        $this->assertTrue($result);
    }

    public function testStartsWithWholeWord() {
        $result = Strings::startsWith("Max", "Max");
        $this->assertTrue($result);
    }

    public function testStartsWithBad() {
        $result = Strings::startsWith("Max", "Leigh");
        $this->assertFalse($result);
    }

    public function testStartsWithBadCase() {
        $result = Strings::startsWith("Max", "m");
        $this->assertFalse($result);
    }

    public function testEndsWithGood() {
        $result = Strings::endsWith("Max", "x");
        $this->assertTrue($result);
    }

    public function testEndsWithWholeWord() {
        $result = Strings::endsWith("Max", "Max");
        $this->assertTrue($result);
    }

    public function testEndsWithEmail() {
        $result = Strings::endsWith("Max@saperstonestudios.com", "@saperstonestudios.com");
        $this->assertTrue($result);
    }

    public function testEndsWithBad() {
        $result = Strings::endsWith("Max", "Leigh");
        $this->assertFalse($result);
    }

    public function testEndsWithBadCase() {
        $result = Strings::endsWith("Max", "X");
        $this->assertFalse($result);
    }

    public function testEndsWithNoSearch() {
        $result = Strings::endsWith("Max", "");
        $this->assertTrue($result);
    }

    public function testIsDateFormatted() {
        $this->assertTrue(Strings::isDateFormatted('2000-01-01'));
        $this->assertFalse(Strings::isDateFormatted('200-01-01'));
        $this->assertFalse(Strings::isDateFormatted('aa67-01-01'));
    }
}