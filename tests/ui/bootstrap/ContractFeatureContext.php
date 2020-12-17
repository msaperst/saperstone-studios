<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use CustomAsserts;
use Exception;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use PHPUnit\Framework\Assert;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';

class ContractFeatureContext implements Context {

    /**
     * @var RemoteWebDriver
     */
    private $driver;
    /**
     * @var WebDriverWait
     */
    private $wait;
    private $contractIds = [];

    /** @BeforeScenario
     * @param BeforeScenarioScope $scope
     * @throws Exception
     */
    public function gatherContexts(BeforeScenarioScope $scope) {
        $environment = $scope->getEnvironment();
        $this->driver = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getDriver();
        $this->wait = new WebDriverWait($this->driver, 10);
    }

    /**
     * @AfterScenario
     * @throws Exception
     */
    public function cleanup() {
        $sql = new Sql();
        foreach ($this->contractIds as $contractId) {
            $contract = dirname(dirname(dirname(__DIR__))) . '/content/' . substr($sql->getRow("SELECT contracts.file FROM contracts WHERE contracts.id = $contractId")['file'], 6);
            if (file_exists("$contract") && !is_dir("$contract")) {
                unlink("$contract");
            }
            $sql->executeStatement("DELETE FROM `contracts` WHERE `contracts`.`id` = $contractId;");
        }
        $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `contracts`;")['count'];
        $count++;
        $sql->executeStatement("ALTER TABLE `contracts` AUTO_INCREMENT = $count;");
        $sql->disconnect();
    }

    /**
     * @Given /^contract (\d+) exists$/
     * @param $contractId
     * @throws Exception
     */
    public function contractExists($contractId) {
        $this->contractIds[] = $contractId;
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `contracts` (`id`, `link`, `type`, `name`, `address`, `number`, `email`, `date`, `location`, `session`, `details`, `amount`, `deposit`, `invoice`, `content`, `signature`, `initial`, `file`) VALUES ($contractId, '8e07fb32bf072e1825df8290a7bcdc57', 'commercial', 'MaxMaxMax', '', '', '', '2021-10-13', '1234 Sesame Street', 'Some session', 'Up to one hour photo session to include:\r\n\r\nBusiness Headshots:\r\n- Web gallery for viewing/making selects\r\n- One file of your choice, color corrected/retouched per person\r\n- Additional files can be purchased for $80/file\r\n- Print release for marketing/web needs for purchased/included files\r\n\r\nOffice Photos:\r\n- All images delivered via web gallery for download\r\n- Print release for marketing/web needs', '0.00', '0.00', 'nope!', '    <h2>Saperstone Studios LLC. Commercial Contract</h2>\n    \n    <p>\n        <strong>This Contract</strong> is made by and between <u>&nbsp;Saperstone\n            Studios&nbsp;</u> (the \"Photographer\") and <u>&nbsp;MaxMaxMax&nbsp;\n        </u>(the \"Client\").<br> <strong>Whereas</strong>, Client wishes to\n        engage Photographer to provide certain photography services and\n        Photographer is willing to accept such engagement, all on the terms\n        and conditions set forth herein.<br> <strong>Now therefore</strong>,\n        in consideration of the mutual promises contained herein and other\n        good and valuable consideration, the receipt and sufficiency of which\n        is hereby acknowledged, the parties agree as follows:\n    </p>\n    <ol>\n        <li><strong>Services.</strong> Photographer hereby agrees to provide\n            the photography services set forth on the attached Statement of\n            Services (the \"Services\") to the best of her abilities.</li>\n        <li><strong>Compensation.</strong> In consideration of the Services,\n            Client agrees to pay Photographer the following amounts as follows:\n            <p>\n                            <span class=\"contract-line-item\"> Hugs: $20 / Kiss\n                    <br>\n                </span>\n                            <span class=\"contract-line-item\"><br>Kisses: $50 / Unit <br></span>\n                <br>  <br>Checks should\n                be made payable to <em>Saperstone Studios</em> and mailed to <em>5012\n                    Whisper Willow Dr, Fairfax VA 22030</em>. Final balance is due with\n                delivered invoice, paid no later than 30 days of delivery, in\n                compliance with Terms and Conditions.\n            </p></li>\n        <li><strong>Session Details.</strong> The above session with take\n            place at the below location on 2021-10-13 <br> 1234 Sesame Street</li>\n        <li><strong>Term.</strong> The initial term of this Contract shall\n            commence on the date hereof and terminate upon completion of the\n            services. Client may terminate this Contract at any time upon 30 days\n            advance written notice. Client shall be liable for the expenses\n            enumerated upon termination. Photographer shall have the right to\n            terminate this Agreement upon 30 days advance written notice and upon\n            return of amounts paid for remaining Services not provided, less\n            expenses.</li>\n        <li><strong>Standard Terms.</strong> Attached hereto is a statement of\n            Standard Terms and Conditions which will apply to the relationship\n            between Photographer and Client; such terms and conditions are\n            incorporated herein by this reference.</li>\n    </ol>\n    <p>\n        <strong>In witness whereof</strong>, the undersigned have caused this\n        Contract to be executed as of the date first above written.\n    \n    \n    </p><h4>Client:</h4>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Name:</strong>\n        </div>\n        <div class=\"col-md-9\">\n            <input id=\"contract-name-signature\" class=\"form-control keep\" type=\"text\" placeholder=\"Client Name\" value=\"\">\n        </div>\n    </div>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Signature:</strong>\n        </div>\n        <div class=\"col-md-9\">\n            <div id=\"contract-signature\" class=\"signature\"></div>\n        </div>\n    </div>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Address:</strong>\n        </div>\n        <div class=\"col-md-9\">\n            <textarea id=\"contract-address\" class=\"form-control keep\" type=\"text\" placeholder=\"Client Address\" value=\"\"></textarea>\n        </div>\n    </div>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Phone Number:</strong>\n        </div>\n        <div class=\"col-md-9\">\n            <input id=\"contract-number\" class=\"form-control keep\" type=\"tel\" placeholder=\"Client Phone Number\" value=\"\">\n        </div>\n    </div>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Email: </strong>\n        </div>\n        <div class=\"col-md-9\">\n            <input id=\"contract-email\" class=\"form-control keep\" type=\"email\" placeholder=\"Client Email\" value=\"\">\n        </div>\n    </div>\n    <h4>Photographer:</h4>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Name:</strong>\n        </div>\n        <div class=\"col-md-9\">\n            Saperstone Studios<br> Leigh Ann Saperstone\n        </div>\n    </div>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Address: </strong>\n        </div>\n        <div class=\"col-md-9\">\n            5012 Whisper Willow Dr.<br>Fairfax, VA 22030\n        </div>\n    </div>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Phone Number:</strong>\n        </div>\n        <div class=\"col-md-9\">\n            <a target=\"_blank\" href=\"tel:5712660004\">(571) 266-0004</a>\n        </div>\n    </div>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Email:</strong>\n        </div>\n        <div class=\"col-md-9\">\n            <a target=\"_blank\" href=\"mailto:contracts@saperstonestudios.com\">contracts@saperstonestudios.com</a>\n        </div>\n    </div>\n    <p></p>\n    <h3>Exhibit A: Statement Of Services</h3>\n    <div class=\"row\">\n        <div class=\"col-md-3\">\n            <strong>Project/Assignment:</strong>\n        </div>\n        <div class=\"col-md-9\">\n            Some session\n        </div>\n    </div>\n    Up to one hour photo session to include:<br><br>Business Headshots:<br>- Web gallery for viewing/making selects<br>- One file of your choice, color corrected/retouched per person<br>- Additional files can be purchased for $80/file<br>- Print release for marketing/web needs for purchased/included files<br><br>Office Photos:<br>- All images delivered via web gallery for download<br>- Print release for marketing/web needs\n    <h3>Standard Terms and Conditions</h3>\n    <p>\n        <strong>Copyright.</strong> The photographs produced by Photographer\n        shall be the intellectual property and copyrighted works of\n        Photographer, and shall not be deemed to be works made for hire.\n        Photographer agrees to give, upon payment in full, a limited usage\n        license to Client for reproduction and display of selected\n        photographs, including private or internal use by Client or its\n        employees and reasonable use in business communications by Client\n        (such as website, newsletter or annual report). Additionally, the\n        files delivered may only be reproduced for Clientâ€™s use and cannot\n        under any circumstance be sold to, licensed to, or used by a\n        third-party without written authorization from Photographer.\n        Photographer retains creative control over the images produced and all\n        decisions made by Photographer with regards to edits are deemed final.\n    </p>\n    <p>\n        <strong>Credit.</strong> In any publication, display, exhibit or other\n        permitted use of the photographs of Photographer, Client agrees to\n        include photo credit to Photographer and include a copyright notice\n        evidence of Photographerâ€™s ownership thereof. In addition, in any\n        publication in which the artists are recognized, Client shall include\n        a brief biography of Photographer approved by Photographer of\n        comparable position and prominence to other artists whose work is\n        included.\n    </p>\n    <p>\n        <strong>Retouch.</strong> Retouching is included but at the\n        Photographers discretion. Any additional retouch requests are handled\n        on a case by case basis and a separate fee may be negotiated.\n    </p>\n    <p>\n        <strong>Scheduling.</strong> Client will be responsible for insuring\n        access and authority to enable Photographer to shoot on the dates set\n        forth. If Photographer is unable to shoot on the dates scheduled for\n        reasons beyond Photographerâ€™s or Clientâ€™s reasonable control, the\n        parties will endeavor to agree on mutually acceptable alternative\n        dates, subject to Photographer availability.\n    </p>\n    <p>\n        <strong>Color Changes.</strong> Client recognizes that color dyes in\n        photography may fade or discolor over time due to the inherent\n        qualities of dyes; Photographer shall have no liability for any claims\n        based upon fading or discoloration due to such inherent qualities.\n    </p>\n    <p>\n        <strong>Advertising.</strong> Client gives Photographer permission to\n        use photographs for advertising (example: website).\n    </p>\n    <p>\n        <strong>Payment &amp; Charges.</strong> If any payment due Photographer is\n        not be made within 30 days of its due date, Photographer shall have\n        the options to assess a service charge equal 1.5% of the outstanding\n        amount each month (or fraction thereof) thereafter that payment is\n        late. If Photographer institutes any proceeding to obtain payment from\n        Client, Client will pay any costs and expenses (including reasonable\n        attorney\'s fees) incurred by Photographer in connection therewith. If\n        check payment does not process, client is liable for all processing,\n        late and overdraft charges.\n    </p>\n    <p>\n        <strong>Limit on Liability.</strong> Absent willful misconduct or\n        infringement by Photographer of intellectual property rights of any\n        third party, the liability of Photographer to Client arising out of\n        the Services shall not exceed the fees paid to Photographer hereunder.\n        In no event shall Photographer have any liability for special,\n        incidental, indirect, consequential or punitive damages hereunder.\n    </p>\n    <p>\n        <strong>Permissions.</strong> Client shall be solely responsible for\n        obtaining all permissions and consent of persons whose photographs are\n        taken and of owners of property and indemnify Photographer from any\n        claims, costs and expenses arising from any failure to do so.\n    </p>\n    <p>\n        <strong>Relationship.</strong> The relationship between Photographer\n        and Client is one of independent contractor. Photographer shall be\n        responsible for payment of all federal, state and local income,\n        payroll and withholding taxes and assessments arising from or\n        attributable to receipt of payments hereunder.\n    </p>\n    <p>\n        <strong>Miscellaneous.</strong> (a) The provisions hereof shall\n        survive termination of this Agreement; (b) this Agreement will be\n        governed by the laws of the State of Virginia (c) this Agreement\n        contains the entire agreement between the parties with respect to the\n        subject matter hereof and supersedes all prior understandings among\n        the parties with respect thereto; this Agreement may be changed only\n        in writing signed by the party against whom enforcement is sought; (d)\n        this Agreement may be executed in counterpart originals, each of which\n        shall constitute an original and all of which together shall\n        constitute a single document and shall be effective upon execution by\n        both parties; and (e) this Agreement shall be binding upon the parties\n        hereto and their respective successors and assigns; no party shall\n        assign its duties or obligations hereunder without the prior written\n        consent of the other party hereto.\n    </p>\n', NULL, NULL, NULL)");
        $sql->disconnect();
    }

    /**
     * @When /^I provide "([^"]*)" for the contract "([^"]*)"$/
     * @param $value
     * @param $field
     */
    public function iProvideForTheContract($value, $field) {
        $this->driver->findElement(WebDriverBy::id('contract-' . $field))->clear()->sendKeys($value);
    }

    /**
     * @When /^I initial the contract$/
     */
    public function iInitialTheContract() {
        $initialCanvas = $this->driver->findElement(WebDriverBy::cssSelector('#contract-initial > canvas'));
        $draw = new WebDriverActions($this->driver);
        $draw->clickAndHold($initialCanvas)->moveByOffset(-5, -10)->moveByOffset(20, 20)->moveByOffset(10, -5)->release()->perform();
    }

    /**
     * @When /^I sign the contract$/
     */
    public function iSignTheContract() {
        $initialCanvas = $this->driver->findElement(WebDriverBy::cssSelector('#contract-signature > canvas'));
        $draw = new WebDriverActions($this->driver);
        $draw->clickAndHold($initialCanvas)->moveByOffset(-20, -10)->moveByOffset(20, 30)->moveByOffset(30, -15)->release()->perform();
    }

    /**
     * @Given /^I submit the contract$/
     * @throws Exception
     */
    public function iSubmitTheContract() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('contract-submit')));
        $this->driver->findElement(WebDriverBy::id('contract-submit'))->click();
    }

    /**
     * @Then /^the submit contract button is disabled$/
     * @throws Exception
     */
    public function theSubmitContractButtonIsDisabled() {
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('contract-submit'))));
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('contract-submit'))->isEnabled());
    }

    /**
     * @Then /^the submit contract button is not present$/
     * @throws Exception
     */
    public function theSubmitContractButtonIsNotPresent() {
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('contract-submit'))));
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('contract-submit'))));
    }

    /**
     * @Then /^I see a success message indicating my contract will be emailed to me$/
     */
    public function iSeeASuccessMessageIndicatingMyContractWillBeEmailedToMe() {
        CustomAsserts::successMessage($this->driver, 'Thank you for signing the contract. You will receive a confirmation email with the final contract attached shortly.');
    }

    /**
     * @Then /^I see the signed contract displayed$/
     * @throws Exception
     */
    public function iSeeTheSignedContractDisplayed() {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::tagName('embed')));
        Assert::assertTrue($this->driver->findElement(WebDriverBy::tagName('embed'))->isDisplayed());
    }

    /**
     * @Then /^I see an error message indicating an invalid email$/
     */
    public function iSeeAnErrorMessageIndicatingAnInvalidEmail() {
        CustomAsserts::errorMessage($this->driver, 'Contract contact email is not valid');
    }

    /**
     * @Given /^I the signed contract exists for (\d+)$/
     * @param $contractId
     */
    public function iTheSignedContractExistsFor($contractId) {
        $sql = new Sql();
        $contract = dirname(dirname(dirname(__DIR__))) . '/content/' . substr($sql->getRow("SELECT contracts.file FROM contracts WHERE contracts.id = $contractId")['file'], 6);
        $sql->disconnect();
        Assert::assertTrue(file_exists("$contract"));
    }
}