<?php

namespace coverage\integration;

use Email;
use Exception;
use PHPUnit\Framework\TestCase;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class EmailIntegrationTest extends TestCase {

    public function testSendEmailWithAttachment() {
        $email = new Email('msaperst+sstest@gmail.com', 'la@saperstonestudios.com', 'test');
        $email->setHtml("<b>Test</b> Email");
        $email->setText("Test Email");
        $email->addAttachment(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'flower.jpeg');
        $email->sendEmail();
        $this->assertTrue(true);
    }

    public function testSendEmail() {
        $email = new Email('msaperst+sstest@gmail.com', 'la@saperstonestudios.com', 'test');
        $email->setHtml("<b>Test</b> Email");
        $email->setText("Test Email");
        $email->sendEmail();
        $this->assertTrue(true);
    }

    public function testSendEmailDetails() {
        $_SERVER["HTTP_USER_AGENT"] = '';
        $_SERVER["HTTP_CLIENT_IP"] = '8.8.8.8';
        $email = new Email('msaperst+sstest@gmail.com', 'la@saperstonestudios.com', 'test');
        $email->setHtml($email->getUserInfoHtml());
        $email->setText($email->getUserInfoText());
        $email->sendEmail();
        unset($_SERVER["HTTP_CLIENT_IP"]);
        unset($_SERVER["HTTP_USER_AGENT"]);
        $this->assertTrue(true);
    }
}