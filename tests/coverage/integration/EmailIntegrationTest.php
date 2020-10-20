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
        try {
            $email = new Email('msaperst+sstest@gmail.com', 'la@saperstonestudios.com', 'test');
            $email->setHtml($email->getUserInfoHtml());
            $email->setText($email->getUserInfoText());
            $email->sendEmail();
            $this->assertTrue(true);
        } finally {
            unset($_SERVER["HTTP_CLIENT_IP"]);
            unset($_SERVER["HTTP_USER_AGENT"]);
        }
    }

    public function testOtherCredsBadEmail() {
        $oldCreds = getenv('EMAIL_USER_X');
        try {
            putenv('EMAIL_USER_X=bar');
            $email = new Email('Webmaster <webmaster@saperstonestudios.com>', 'la@saperstonestudios.com', 'test');
            $email->sendEmail();
        } catch (Exception $e) {
            $this->assertNotNull($e->getMessage());
        } finally {
            putenv("EMAIL_USER_X=$oldCreds");
        }
    }

    public function testBasicUserInfoHtml() {
        try {
            $_SERVER["HTTP_USER_AGENT"] = '';
            $_SERVER["HTTP_CLIENT_IP"] = '8.8.8.8';
            $email = new Email('msaperst+sstest@gmail.com', 'la@saperstonestudios.com', 'test');
            $this->assertEquals('<strong>Location</strong>: Mountain View, California 94043 - US (estimated location based on IP: 8.8.8.8)<br/><strong>Hostname</strong>: dns.google<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: <br/>', $email->getUserInfoHtml());
        } finally {
            unset($_SERVER["HTTP_CLIENT_IP"]);
            unset($_SERVER["HTTP_USER_AGENT"]);
        }
    }

    public function testUserInfoHtmlResolution() {
        try {
            $_SERVER["HTTP_USER_AGENT"] = '';
            $_SERVER["HTTP_CLIENT_IP"] = '192.168.1.2';
            $_POST['resolution'] = '20x30';
            $email = new Email('msaperst+sstest@gmail.com', 'la@saperstonestudios.com', 'test');
            $this->assertEquals('<strong>Location</strong>: unknown (use 192.168.1.2 to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: 20x30<br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: <br/>', $email->getUserInfoHtml());
        } finally {
            unset($_SERVER["HTTP_CLIENT_IP"]);
            unset($_SERVER["HTTP_USER_AGENT"]);
            unset($_POST['resolution']);
        }
    }

    public function testUserInfoHtmlNoPostal() {
        try {
            $_SERVER["HTTP_USER_AGENT"] = '';
            $_SERVER["HTTP_CLIENT_IP"] = '5.82.134.1';
            $email = new Email('msaperst+sstest@gmail.com', 'la@saperstonestudios.com', 'test');
            $this->assertEquals('<strong>Location</strong>: Şāmitah, Jazan Region - SA (estimated location based on IP: 5.82.134.1)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: <br/>', $email->getUserInfoHtml());
        } finally {
            unset($_SERVER["HTTP_CLIENT_IP"]);
            unset($_SERVER["HTTP_USER_AGENT"]);
        }
    }

    public function testBasicUserInfoText() {
        try {
            $_SERVER["HTTP_USER_AGENT"] = '';
            $_SERVER["HTTP_CLIENT_IP"] = '8.8.8.8';
            $email = new Email('msaperst+sstest@gmail.com', 'la@saperstonestudios.com', 'test');
            $this->assertEquals('Location: Mountain View, California 94043 - US (estimated location based on IP: 8.8.8.8)
Hostname: dns.google
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: 
', $email->getUserInfoText());
        } finally {
            unset($_SERVER["HTTP_CLIENT_IP"]);
            unset($_SERVER["HTTP_USER_AGENT"]);
        }
    }

    public function testUserInfoTextResolution() {
        try {
            $_SERVER["HTTP_USER_AGENT"] = '';
            $_SERVER["HTTP_CLIENT_IP"] = '192.168.1.2';
            $_POST['resolution'] = '20x30';
            $email = new Email('msaperst+sstest@gmail.com', 'la@saperstonestudios.com', 'test');
            $this->assertEquals('Location: unknown (use 192.168.1.2 to manually lookup)
Browser: unknown unknown
Resolution: 20x30
OS: unknown
Full UA: 
', $email->getUserInfoText());
        } finally {
            unset($_SERVER["HTTP_CLIENT_IP"]);
            unset($_SERVER["HTTP_USER_AGENT"]);
            unset($_POST['resolution']);
        }
    }

    public function testUserInfoTextNoPostal() {
        try {
            $_SERVER["HTTP_USER_AGENT"] = '';
            $_SERVER["HTTP_CLIENT_IP"] = '5.82.134.1';
            $email = new Email('msaperst+sstest@gmail.com', 'la@saperstonestudios.com', 'test');
            $this->assertEquals('Location: Şāmitah, Jazan Region - SA (estimated location based on IP: 5.82.134.1)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: 
', $email->getUserInfoText());
        } finally {
            unset($_SERVER["HTTP_CLIENT_IP"]);
            unset($_SERVER["HTTP_USER_AGENT"]);
        }
    }
}