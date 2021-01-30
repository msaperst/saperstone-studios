<?php


namespace coverage\integration;

use LineItem;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class LineItemIntegrationTest extends TestCase {

    public function testBasicValues() {
        $lineItem = new LineItem(1, '2', '3', 'foo');
        $this->assertEquals("1, '2', 3, 'foo'", $lineItem->getValues());
    }

    public function testUpdateContract() {
        $lineItem = new LineItem(1, '2', '3', 'foo');
        $lineItem->setContract(3);
        $this->assertEquals("3, '2', 3, 'foo'", $lineItem->getValues());
    }

    public function testCreate() {
        $sql = new Sql();
        try {
            $lineItem = new LineItem(999, '2', '3', 'foo');
            $lineItem->create();

            $lineItems = $sql->getRows("SELECT * FROM contract_line_items WHERE contract = 999");
            $this->assertEquals(1, sizeOf($lineItems));
            $this->assertEquals(999, $lineItems[0]['contract']);
            $this->assertEquals(2, $lineItems[0]['item']);
            $this->assertEquals(3.00, $lineItems[0]['amount']);
            $this->assertEquals('foo', $lineItems[0]['unit']);
        } finally {
            $sql->executeStatement("DELETE FROM contract_line_items WHERE contract = 999");
            $sql->disconnect();
        }
    }
}