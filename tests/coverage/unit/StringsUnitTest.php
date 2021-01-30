<?php

namespace coverage\unit;

use PHPUnit\Framework\TestCase;
use Strings;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class StringsUnitTest extends TestCase {

    public function testRandomNegativeLength() {
        $result = Strings::randomString(-1);
        $this->assertEquals(0, strlen($result));
    }

    public function testRandomBadInput() {
        $result = Strings::randomString('a');
        $this->assertEquals(0, strlen($result));
    }

    public function testRandomNoInput() {
        $result = Strings::randomString();
        $this->assertEquals(10, strlen($result));
    }

    public function lengthRandomDataProvider() {
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