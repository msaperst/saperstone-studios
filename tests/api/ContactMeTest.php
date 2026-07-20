<?php

namespace api;

use CustomAsserts;
use Google\Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

/**
 * Class ContactMeTest
 * @package api
 */
class ContactMeTest extends TestCase {
    /**
     * @var Client
     */
    private $http;

    public function setUp(): void {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/']);
        // Ensure every single test starts with a completely clean mailbox slate!
        CustomAsserts::clearAllEmails();
    }

    public function tearDown(): void {
        $this->http = NULL;
    }

    /**
     * @throws GuzzleException
     */
    public function testNoTime() {
        $response = $this->http->request('POST', 'api/contact-me.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Load Time is required", (string)$response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankTime() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'loadtime' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Load Time can not be blank", (string)$response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    /**
     * @throws GuzzleException
     */
    public function testNoName() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'loadtime' => '1234567890'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Name is required", (string)$response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankName() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'loadtime' => '1234567890',
                'name' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Name can not be blank", (string)$response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    /**
     * @throws GuzzleException
     */
    public function testNoPhone() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'loadtime' => '1234567890',
                'name' => 'Max'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Phone number is required", (string)$response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankPhone() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'loadtime' => '1234567890',
                'name' => 'Max',
                'phone' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Phone number can not be blank", (string)$response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    /**
     * @throws GuzzleException
     */
    public function testNoEmail() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'loadtime' => '1234567890',
                'name' => 'Max',
                'phone' => '1234'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is required", (string)$response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankEmail() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'loadtime' => '1234567890',
                'name' => 'Max',
                'phone' => '1234',
                'email' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email can not be blank", (string)$response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    /**
     * @throws GuzzleException
     */
    public function testBadEmail() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'loadtime' => '1234567890',
                'name' => 'Max',
                'phone' => '1234',
                'email' => 'max@max'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is not valid", (string)$response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    /**
     * @throws GuzzleException
     */
    public function testNoMessage() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'loadtime' => '1234567890',
                'name' => 'Max',
                'phone' => '1234',
                'email' => 'msaperst+sstest@gmail.com'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Message is required", (string)$response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankMessage() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'loadtime' => '1234567890',
                'name' => 'Max',
                'phone' => '1234',
                'email' => 'msaperst+sstest@gmail.com',
                'message' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Message can not be blank", (string)$response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function testHoneyPot() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'loadtime' => '1234567890',
                'company' => 'Some Company',
                'name' => 'Max',
                'phone' => '571-245-3351',
                'email' => 'msaperst+sstest@gmail.com',
                'message' => 'Hi There! I am a test email'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function testTooFastLoadTime() {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'loadtime' => '12345678901234567890',
                'name' => 'Max',
                'phone' => '571-245-3351',
                'email' => 'msaperst+sstest@gmail.com',
                'message' => 'Hi There! I am a test email'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    public function testInvalidPhoneIsDropped(): void {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'loadtime' => '1234567890',
                'name' => 'Max',
                'phone' => 'ABCDEF',
                'email' => 'msaperst+sstest@gmail.com',
                'message' => 'Hi There! I am a test email'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    public function testGibberishMessageIsDropped(): void {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'loadtime' => '1234567890',
                'name' => 'Max',
                'phone' => '571-245-3351',
                'email' => 'msaperst+sstest@gmail.com',
                'message' => 'HiThereIamatestEmail'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    public function testGibberishNameIsDropped(): void {
        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'loadtime' => '1234567890',
                'name' => 'MaxSaperstoneIsHappy',
                'phone' => '571-245-3351',
                'email' => 'msaperst+sstest@gmail.com',
                'message' => 'Hi There! I am a test email'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    /**
     * @throws GuzzleException
     */
    public function testAll() {
        $senderEmail = 'msaperst@gmail.com';
        $senderName = 'Max';

        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'loadtime' => '1234567890',
                'name' => $senderName,
                'phone' => '571-245-3351',
                'email' => $senderEmail,
                'message' => 'Hi There! I am a test email'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Thank you for submitting your comment. We greatly appreciate your interest and feedback. Someone will get back to you within 24 hours.", (string)$response->getBody());

        // 1. Assert exactly two emails landed in Mailpit
        CustomAsserts::assertEmailCount(2);

        // 2. VERIFY EMAIL #1: The auto-response confirmation sent to the User
        CustomAsserts::assertEmailMatches(
            $senderEmail,
            'noreply@saperstonestudios.com',
            'Thank you for contacting Saperstone Studios',
            'Thank you for contacting Saperstone Studios. We will respond to your request as soon as we are able to. We are typically able to get back to you within 24 hours.',
            '<html><body>Thank you for contacting Saperstone Studios. We will respond to your request as soon as we are able to. We are typically able to get back to you within 24 hours.</body></html>'
        );

        // 3. VERIFY EMAIL #2: The notification alert sent to the Site Owner
        CustomAsserts::assertEmailMatches(
            'contact@saperstonestudios.com',
            $senderEmail,
            "Saperstone Studios Contact Form: {$senderName}",
            "This is an automatically generated message from Saperstone Studios
Name: {$senderName}
Phone: 571-245-3351
Email: {$senderEmail}
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7

\t\tHi There! I am a test email",
            "<html><body>This is an automatically generated message from Saperstone Studios<br/><strong>Name</strong>: {$senderName}<br/><strong>Phone</strong>: 571-245-3351<br/><strong>Email</strong>: <a href='mailto:{$senderEmail}'>{$senderEmail}</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hi There! I am a test email<br/><br/></body></html>",
            'saperstonestudios@gmail.com'
        );
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function testAllSS() {
        $senderEmail = 'msaperst@saperstonestudios.com';
        $senderName = 'Max';

        $response = $this->http->request('POST', 'api/contact-me.php', [
            'form_params' => [
                'loadtime' => '1234567890',
                'name' => $senderName,
                'phone' => '571-245-3351',
                'email' => $senderEmail,
                'message' => 'Hi There! I am a test email'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Thank you for submitting your comment. We greatly appreciate your interest and feedback. Someone will get back to you within 24 hours.", (string)$response->getBody());

        // 1. Assert exactly two emails landed in Mailpit
        CustomAsserts::assertEmailCount(2);

        // 2. VERIFY EMAIL #1: The auto-response confirmation sent to the User
        CustomAsserts::assertEmailMatches(
            $senderEmail,
            'noreply@saperstonestudios.com',
            'Thank you for contacting Saperstone Studios',
            'Thank you for contacting Saperstone Studios. We will respond to your request as soon as we are able to. We are typically able to get back to you within 24 hours.',
            '<html><body>Thank you for contacting Saperstone Studios. We will respond to your request as soon as we are able to. We are typically able to get back to you within 24 hours.</body></html>',
            'saperstonestudios@gmail.com'
        );

        // 3. VERIFY EMAIL #2: The notification alert sent to the Site Owner
        CustomAsserts::assertEmailMatches(
            'contact@saperstonestudios.com',
            $senderEmail,
            "Saperstone Studios Contact Form: {$senderName}",
            "This is an automatically generated message from Saperstone Studios
Name: {$senderName}
Phone: 571-245-3351
Email: {$senderEmail}
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: unknown unknown
Resolution: 
OS: unknown
Full UA: GuzzleHttp/7

\t\tHi There! I am a test email",
            "<html><body>This is an automatically generated message from Saperstone Studios<br/><strong>Name</strong>: {$senderName}<br/><strong>Phone</strong>: 571-245-3351<br/><strong>Email</strong>: <a href='mailto:{$senderEmail}'>{$senderEmail}</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: unknown unknown<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: unknown<br/><strong>Full UA</strong>: GuzzleHttp/7<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hi There! I am a test email<br/><br/></body></html>",
            'saperstonestudios@gmail.com'
        );
    }
}
