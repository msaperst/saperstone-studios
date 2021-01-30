<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class LineItem {

    private $contract;
    private $item;
    private $amount;
    private $unit;

    /**
     * LineItem constructor.
     * @param $contract
     * @param $item
     * @param $amount
     * @param $unit
     */
    function __construct($contract, $item, $amount, $unit) {
        $this->contract = $contract;
        $this->item = $item;
        $this->amount = $amount;
        $this->unit = $unit;
    }

    function setContract($contract) {
        $this->contract = $contract;
    }

    function getValues(): string {
        return "{$this->contract}, '{$this->item}', {$this->amount}, '{$this->unit}'";
    }

    /**
     * @throws SqlException
     */
    function create() {
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `contract_line_items` (`contract`, `item`, `amount`, `unit`) VALUES ({$this->getValues()});");
        $sql->disconnect();
    }
}