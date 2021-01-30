<?php


namespace coverage\unit;

use LineItem;
use PHPUnit\Framework\TestCase;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class LineItemUnitTest extends TestCase {

    public function testBasicValues() {
        $lineItem = new LineItem(1, '2', '3', 'foo');
        $this->assertEquals("1, '2', 3, 'foo'", $lineItem->getValues());
    }

    public function testUpdateContract() {
        $lineItem = new LineItem(1, '2', '3', 'foo');
        $lineItem->setContract(3);
        $this->assertEquals("3, '2', 3, 'foo'", $lineItem->getValues());
    }
}