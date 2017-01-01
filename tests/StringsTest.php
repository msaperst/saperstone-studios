<?php
require '../php/strings.php';
class StringsTest extends PHPUnit_Framework_TestCase {
    private $string;
    protected function setUp() {
        $this->string = new Strings ();
    }
    protected function tearDown() {
        $this->string = NULL;
    }
    public function testRandomNegativeLength() {
        $result = $this->string->randomString ( - 1 );
        $this->assertEquals ( 0, strlen ( $result ) );
    }
    public function testRandomBadInput() {
        $result = $this->string->randomString ( 'a' );
        $this->assertEquals ( 0, strlen ( $result ) );
    }
    public function testRandomNoInput() {
        $result = $this->string->randomString ();
        $this->assertEquals ( 10, strlen ( $result ) );
    }
    public function lengthRandomDataProvider() {
        return array (
                array (
                        0 
                ),
                array (
                        1 
                ),
                array (
                        9 
                ),
                array (
                        10 
                ),
                array (
                        11 
                ),
                array (
                        99 
                ),
                array (
                        1000 
                ),
                array (
                        99999 
                ) 
        );
    }
    
    /**
     * @dataProvider lengthRandomDataProvider
     */
    public function testRandomGoodInput($length) {
        $result = $this->string->randomString ( $length );
        $this->assertEquals ( $length, strlen ( $result ) );
    }
    public function testRandomOutputVal() {
        $result = $this->string->randomString ( 99999 );
        $this->assertEquals ( 1, preg_match ( '/^[a-zA-Z0-9]+$/', $result ) );
    }
    public function testHTMLEmpty() {
        $result = $this->string->textToHTML ( "" );
        $this->assertEquals ( "", $result );
    }
    public function testHTMLSpaces() {
        $result = $this->string->textToHTML ( "      " );
        $this->assertEquals ( "      ", $result );
    }
    public function testHTMLTab() {
        $result = $this->string->textToHTML ( "\t" );
        $this->assertEquals ( "&nbsp;&nbsp;&nbsp;&nbsp;", $result );
    }
    public function testHTMLTabAndTest() {
        $result = $this->string->textToHTML ( "Hello\tWorld" );
        $this->assertEquals ( "Hello&nbsp;&nbsp;&nbsp;&nbsp;World", $result );
    }
    public function testHTMLNewline() {
        $result = $this->string->textToHTML ( "\n" );
        $this->assertEquals ( "<br/>", $result );
    }
    public function testHTMLNewlineAndText() {
        $result = $this->string->textToHTML ( "Hello\nWorld" );
        $this->assertEquals ( "Hello<br/>World", $result );
    }
}