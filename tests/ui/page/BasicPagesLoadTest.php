<?php

namespace ui\page;

use CustomAsserts;
use Exception;
use Facebook\WebDriver\WebDriverBy;
use Google\Exception as ExceptionAlias;
use Sql;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestBase.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class BasicPagesLoadTest extends TestBase {

    public function testMainPage() {
        $this->driver->get($this->baseUrl);
        $this->assertEquals('Photography Services', $this->driver->findElement(WebDriverBy::tagName('h2'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testAboutPage() {
        $this->driver->get($this->baseUrl . 'about.php');
        $this->assertEquals('About Saperstone Studios', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testContactPage() {
        $this->driver->get($this->baseUrl . 'contact.php');
        $this->assertEquals('Contact', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testContactPageNotLoggedIn() {
        $this->driver->get($this->baseUrl . 'contact.php');
        $this->assertEquals('', $this->driver->findElement(WebDriverBy::id('name'))->getAttribute('value'));
        $this->assertEquals('', $this->driver->findElement(WebDriverBy::id('email'))->getAttribute('value'));
    }

    public function testContactPageLoggedIn() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'contact.php');
        $this->assertEquals('Max Saperstone', $this->driver->findElement(WebDriverBy::id('name'))->getAttribute('value'));
        $this->assertEquals('msaperst@gmail.com', $this->driver->findElement(WebDriverBy::id('email'))->getAttribute('value'));
    }

    public function testLeighAnnPage() {
        $this->driver->get($this->baseUrl . 'leighAnn.php');
        $this->assertEquals('Meet Leigh Ann', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testPrivacyPolicyPage() {
        $this->driver->get($this->baseUrl . 'Privacy-Policy.php');
        $this->assertEquals('Privacy Policy', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testTermsOfUsePage() {
        $this->driver->get($this->baseUrl . 'Terms-of-Use.php');
        $this->assertEquals('Terms of Use', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    // pages with some logic in them

    /**
     * @throws ExceptionAlias
     */
    public function testContractPageNoC() {
        $this->driver->get($this->baseUrl . 'contract.php');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/contract.php\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/contract.php\' target=\'_blank\'>%s://%s/contract.php</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    /**
     * @throws ExceptionAlias
     */
    public function testContractPageBlankC() {
        $this->driver->get($this->baseUrl . 'contract.php?c=');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/contract.php?c=\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/contract.php?c=\' target=\'_blank\'>%s://%s/contract.php?c=</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    /**
     * @throws ExceptionAlias
     */
    public function testContractPageBadC() {
        $this->driver->get($this->baseUrl . 'contract.php?c=2');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/contract.php?c=2\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/contract.php?c=2\' target=\'_blank\'>%s://%s/contract.php?c=2</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    /**
     * @throws Exception
     */
    public function testContractPageGoodC() {
        $sql = new Sql();
        try {
            $sql->executeStatement("INSERT INTO `contracts` (`id`, `link`, `type`, `name`, `address`, `number`, `email`, `date`, `location`, `session`, `details`, `amount`, `deposit`, `invoice`, `content`, `signature`, `initial`, `file`) VALUES (999, '8e07fb32bf072e1825df8290a7bcdc57', 'commercial', 'MaxMaxMax', 'Address', '1234567890', 'email-address', '2021-10-13', '1234 Sesame Street', 'Some session', 'details', '0.00', '0.00', 'nope!', 'content', NULL, NULL, NULL)");
            $this->driver->get($this->baseUrl . 'contract.php?c=8e07fb32bf072e1825df8290a7bcdc57');
            $this->assertEquals('Saperstone Studios Contracts', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
            $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        } finally {
            $sql->executeStatement("DELETE FROM `contracts` WHERE id = 999");
            $sql->disconnect();
        }
    }

    /**
     * @throws Exception
     */
    public function testContractPageIdSet() {
        $sql = new Sql();
        try {
            $sql->executeStatement("INSERT INTO `contracts` (`id`, `link`, `type`, `name`, `address`, `number`, `email`, `date`, `location`, `session`, `details`, `amount`, `deposit`, `invoice`, `content`, `signature`, `initial`, `file`) VALUES (999, '8e07fb32bf072e1825df8290a7bcdc57', 'commercial', 'MaxMaxMax', 'Address', '1234567890', 'email-address', '2021-10-13', '1234 Sesame Street', 'Some session', 'details', '0.00', '0.00', 'nope!', 'content', NULL, NULL, NULL)");
            $this->driver->get($this->baseUrl . 'contract.php?c=8e07fb32bf072e1825df8290a7bcdc57');
            $this->assertEquals(999, $this->driver->findElement(WebDriverBy::id('contract-id'))->getAttribute('value'));
        } finally {
            $sql->executeStatement("DELETE FROM `contracts` WHERE id = 999");
            $sql->disconnect();
        }
    }

    /**
     * @throws Exception
     */
    public function testContractPageUnsignedLink() {
        $sql = new Sql();
        try {
            $sql->executeStatement("INSERT INTO `contracts` (`id`, `link`, `type`, `name`, `address`, `number`, `email`, `date`, `location`, `session`, `details`, `amount`, `deposit`, `invoice`, `content`, `signature`, `initial`, `file`) VALUES (999, '8e07fb32bf072e1825df8290a7bcdc57', 'commercial', 'MaxMaxMax', 'Address', '1234567890', 'email-address', '2021-10-13', '1234 Sesame Street', 'Some session', 'details', '0.00', '0.00', 'nope!', 'content', NULL, NULL, NULL)");
            $this->driver->get($this->baseUrl . 'contract.php?c=8e07fb32bf072e1825df8290a7bcdc57');
            $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::tagName('embed'))));
            $this->assertTrue($this->driver->findElement(WebDriverBy::id('contract'))->isDisplayed());
            $this->assertTrue($this->driver->findElement(WebDriverBy::id('contract-initial-holder'))->isDisplayed());
            $this->assertTrue($this->driver->findElement(WebDriverBy::id('contract-messages'))->isDisplayed());
            $this->assertEquals('Paypal Invoice Link', $this->driver->findElement(WebDriverBy::id('contract-messages'))->getText());
            $this->assertStringEndsWith('nope!', $this->driver->findElement(WebDriverBy::linkText('Paypal Invoice Link'))->getAttribute('href'));
            $this->assertTrue($this->driver->findElement(WebDriverBy::id('contract-submit'))->isDisplayed());
        } finally {
            $sql->executeStatement("DELETE FROM `contracts` WHERE id = 999");
            $sql->disconnect();
        }
    }

    /**
     * @throws Exception
     */
    public function testContractPageUnsignedNoLink() {
        $sql = new Sql();
        try {
            $sql->executeStatement("INSERT INTO `contracts` (`id`, `link`, `type`, `name`, `address`, `number`, `email`, `date`, `location`, `session`, `details`, `amount`, `deposit`, `invoice`, `content`, `signature`, `initial`, `file`) VALUES (999, '8e07fb32bf072e1825df8290a7bcdc57', 'commercial', 'MaxMaxMax', 'Address', '1234567890', 'email-address', '2021-10-13', '1234 Sesame Street', 'Some session', 'details', '0.00', '0.00', '', 'content', NULL, NULL, NULL)");
            $this->driver->get($this->baseUrl . 'contract.php?c=8e07fb32bf072e1825df8290a7bcdc57');
            $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::tagName('embed'))));
            $this->assertTrue($this->driver->findElement(WebDriverBy::id('contract'))->isDisplayed());
            $this->assertTrue($this->driver->findElement(WebDriverBy::id('contract-initial-holder'))->isDisplayed());
            $this->assertTrue($this->driver->findElement(WebDriverBy::id('contract-messages'))->isDisplayed());
            $this->assertEquals('', $this->driver->findElement(WebDriverBy::id('contract-messages'))->getText());
            $this->assertTrue($this->driver->findElement(WebDriverBy::id('contract-submit'))->isDisplayed());
        } finally {
            $sql->executeStatement("DELETE FROM `contracts` WHERE id = 999");
            $sql->disconnect();
        }
    }

    /**
     * @throws Exception
     */
    public function testContractPageSigned() {
        $sql = new Sql();
        try {
            $sql->executeStatement("INSERT INTO `contracts` (`id`, `link`, `type`, `name`, `address`, `number`, `email`, `date`, `location`, `session`, `details`, `amount`, `deposit`, `invoice`, `content`, `signature`, `initial`, `file`) VALUES (999, '8e07fb32bf072e1825df8290a7bcdc57', 'commercial', 'MaxMaxMax', 'Address', '1234567890', 'email-address', '2021-10-13', '1234 Sesame Street', 'Some session', 'details', '0.00', '0.00', '', 'content', 'x', 'y', '../user/contracts/My File.pdf')");
            $this->driver->get($this->baseUrl . 'contract.php?c=8e07fb32bf072e1825df8290a7bcdc57');
            $this->assertTrue($this->driver->findElement(WebDriverBy::tagName('embed'))->isDisplayed());
            $this->assertStringEndsWith('/user/contracts/My File.pdf', $this->driver->findElement(WebDriverBy::tagName('embed'))->getAttribute('src'));
            $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('contract'))));
            $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('contract-initial-holder'))));
            $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('contract-messages'))));
            $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('contract-submit'))));
            CustomAsserts::assertEmailMatches('404 Error',
                "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/user/contracts/My%20File.pdf\r
\t\tThey came from page %s://%s/contract.php?c=8e07fb32bf072e1825df8290a7bcdc57\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
                '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/user/contracts/My%20File.pdf\' target=\'_blank\'>%s://%s/user/contracts/My%20File.pdf</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'%s://%s/contract.php?c=8e07fb32bf072e1825df8290a7bcdc57\' target=\'_blank\'>%s://%s/contract.php?c=8e07fb32bf072e1825df8290a7bcdc57</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');

        } finally {
            $sql->executeStatement("DELETE FROM `contracts` WHERE id = 999");
            $sql->disconnect();
        }
    }

    public function testRegisterPage() {
        $this->driver->get($this->baseUrl . 'register.php');
        $this->assertEquals('Register', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testRegisterPageRedirect() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'register.php');
        $this->assertEquals($this->baseUrl . 'user/profile.php', $this->driver->getCurrentURL());
    }

    public function testReviewsPageNoC() {
        $this->driver->get($this->baseUrl . 'reviews.php');
        $this->assertEquals('Raves', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home Information Raves', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    /**
     * @throws ExceptionAlias
     */
    public function testReviewsPageBlankC() {
        $this->driver->get($this->baseUrl . 'reviews.php?c=');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/reviews.php?c=\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/reviews.php?c=\' target=\'_blank\'>%s://%s/reviews.php?c=</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    /**
     * @throws ExceptionAlias
     */
    public function testReviewsPageBadC() {
        $this->driver->get($this->baseUrl . 'reviews.php?c=abc');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/reviews.php?c=abc\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/reviews.php?c=abc\' target=\'_blank\'>%s://%s/reviews.php?c=abc</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    public function testReviewsPagePortrait() {
        $this->driver->get($this->baseUrl . 'reviews.php?c=1');
        $this->assertEquals('Portrait Raves', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home Portrait Raves', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testReviewsPageWedding() {
        $this->driver->get($this->baseUrl . 'reviews.php?c=2');
        $this->assertEquals('Wedding Raves', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home Wedding Raves', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testReviewsPageCommercial() {
        $this->driver->get($this->baseUrl . 'reviews.php?c=3');
        $this->assertEquals('Commercial Raves', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home Commercial Raves', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }
}