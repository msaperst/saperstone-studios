<?php

namespace coverage\integration;

use CustomAsserts;
use Email;
use Exception;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class EmailIntegrationTest extends TestCase {

    private string $httpUserAgent;

    private string $httpClientId;

    public function setUp(): void {
        if (isset($_SERVER ['HTTP_USER_AGENT'])) {
            $this->httpUserAgent = $_SERVER ['HTTP_USER_AGENT'];
        }
        if (isset($_SERVER ['HTTP_CLIENT_IP'])) {
            $this->httpClientId = $_SERVER ['HTTP_CLIENT_IP'];
        }
    }

    public function tearDown(): void {
        if (isset($this->httpUserAgent)) {
            $_SERVER ['HTTP_USER_AGENT'] = $this->httpUserAgent;
        } else {
            unset($_SERVER ['HTTP_USER_AGENT']);
        }
        if (isset($this->httpClientId)) {
            $_SERVER ['HTTP_CLIENT_IP'] = $this->httpClientId;
        } else {
            unset($_SERVER ['HTTP_CLIENT_IP']);
        }
        unset($_POST['resolution']);

        // wipe out all emails to keep things clear
        CustomAsserts::clearAllEmails();
    }

    public function testSendEmailMakeDirectory() {
        system('mv /var/www/logs /var/www/logs-bkp');
        try {
            new Email('msaperst@gmail.com', 'la@saperstonestudios.com', 'test');
            $this->assertTrue(true);
        } finally {
            system('rm -rf /var/www/logs');
            system('mv /var/www/logs-bkp /var/www/logs');
        }
    }

    /**
     * @throws Exception
     */
    public function testSendEmailWithAttachment() {
        $email = new Email('msaperst@gmail.com', 'la@saperstonestudios.com', 'test');
        $email->setHtml("<b>Test</b> Email");
        $email->setText("Test Email");
        $email->addAttachment(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'uiTestResultTemplate.html');
        $email->sendEmail();
        // commenting out assertion until I can figure out email credential verification
        CustomAsserts::assertEmailMatches(
            'msaperst@gmail.com',
            'la@saperstonestudios.com',
            'test',
            'Test Email',
            '<b>Test</b> Email',
            null,
            dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'uiTestResultTemplate.html');
    }

    /**
     * @throws Exception
     */
    public function testSendEmail() {
        $email = new Email('msaperst@gmail.com', 'la@saperstonestudios.com', 'test');
        $email->setHtml("<b>Test</b> Email");
        $email->setText("Test Email");
        $email->sendEmail();
        // commenting out assertion until I can figure out email credential verification
        CustomAsserts::assertEmailMatches(
            'msaperst@gmail.com',
            'la@saperstonestudios.com',
            'test',
            'Test Email',
            '<b>Test</b> Email');
    }

    /**
     * @throws Exception
     */
    public function testSendEmailDetails() {
        $_SERVER["HTTP_USER_AGENT"] = '';
        $_SERVER["HTTP_CLIENT_IP"] = '8.8.8.8';
        $email = new Email('msaperst@gmail.com', 'la@saperstonestudios.com', 'test');
        $email->setHtml($email->getUserInfoHtml());
        $email->setText($email->getUserInfoText());
        $email->sendEmail();
        // commenting out assertion until I can figure out email credential verification
        CustomAsserts::assertEmailMatches(
            'msaperst@gmail.com',
            'la@saperstonestudios.com',
            'test',
            "Location: Mountain View, California 94043 - US (estimated location based on IP: 8.8.8.8)\r
Hostname: dns.google\r
Browser: unknown unknown\r
Resolution: \r
OS: unknown\r
Full UA: \r
", '<strong>Location</strong>: Mountain View, California 94043 - US (estimated location based on IP: 8.8.8.8)<br/><strong>Hostname</strong>: dns.google<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: <br/>');
    }

    public function testOtherCredentialsBadEmail() {
        $oldCredentials = getenv('EMAIL_USER_X');
        try {
            putenv('EMAIL_USER_X=bar');
            $email = new Email('Webmaster <webmaster@saperstonestudios.com>', 'la@saperstonestudios.com', 'test');
            $email->sendEmail();
        } catch (Exception $e) {
            $this->assertNotNull($e->getMessage());
        } finally {
            putenv("EMAIL_USER_X=$oldCredentials");
        }
    }

    public function testBasicUserInfoHtml() {
        $_SERVER["HTTP_USER_AGENT"] = '';
        $_SERVER["HTTP_CLIENT_IP"] = '8.8.8.8';
        $email = new Email('msaperst@gmail.com', 'la@saperstonestudios.com', 'test');
        $this->assertEquals('<strong>Location</strong>: Mountain View, California 94043 - US (estimated location based on IP: 8.8.8.8)<br/><strong>Hostname</strong>: dns.google<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: <br/>', $email->getUserInfoHtml());
    }

    public function testUserInfoHtmlResolution() {
        $_SERVER["HTTP_USER_AGENT"] = '';
        $_SERVER["HTTP_CLIENT_IP"] = '192.168.1.2';
        $_POST['resolution'] = '20x30';
        $email = new Email('msaperst@gmail.com', 'la@saperstonestudios.com', 'test');
        $this->assertEquals('<strong>Location</strong>: unknown (use 192.168.1.2 to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: 20x30<br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: <br/>', $email->getUserInfoHtml());
    }

    public function testUserInfoHtmlNoPostal() {
        $_SERVER["HTTP_USER_AGENT"] = '';
        $_SERVER["HTTP_CLIENT_IP"] = '5.82.134.1';
        $email = new Email('msaperst@gmail.com', 'la@saperstonestudios.com', 'test');
        $this->assertEquals('<strong>Location</strong>: Jeddah, Mecca Region - SA (estimated location based on IP: 5.82.134.1)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: <br/>', $email->getUserInfoHtml());
    }

    public function testBasicUserInfoText() {
        $_SERVER["HTTP_USER_AGENT"] = '';
        $_SERVER["HTTP_CLIENT_IP"] = '8.8.8.8';
        $email = new Email('msaperst@gmail.com', 'la@saperstonestudios.com', 'test');
        $this->assertEquals('Location: Mountain View, California 94043 - US (estimated location based on IP: 8.8.8.8)
Hostname: dns.google
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: 
', $email->getUserInfoText());
    }

    public function testUserInfoTextResolution() {
        $_SERVER["HTTP_USER_AGENT"] = '';
        $_SERVER["HTTP_CLIENT_IP"] = '192.168.1.2';
        $_POST['resolution'] = '20x30';
        $email = new Email('msaperst@gmail.com', 'la@saperstonestudios.com', 'test');
        $this->assertEquals('Location: unknown (use 192.168.1.2 to manually lookup)
Browser: unknown unknown
Resolution: 20x30
OS: unknown
Full UA: 
', $email->getUserInfoText());
    }

    public function testUserInfoTextNoPostal() {
        $_SERVER["HTTP_USER_AGENT"] = '';
        $_SERVER["HTTP_CLIENT_IP"] = '5.82.134.1';
        $email = new Email('msaperst@gmail.com', 'la@saperstonestudios.com', 'test');
        $this->assertEquals('Location: Jeddah, Mecca Region - SA (estimated location based on IP: 5.82.134.1)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: 
', $email->getUserInfoText());
    }
}