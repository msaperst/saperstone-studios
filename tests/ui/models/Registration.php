<?php

namespace ui\models;

class Registration {
    private $driver;
    private $wait;

    public function __construct($driver, $wait) {
        $this->driver = $driver;
        $this->wait = $wait;
    }


}