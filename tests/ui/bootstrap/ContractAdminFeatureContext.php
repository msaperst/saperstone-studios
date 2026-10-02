<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;
use Facebook\WebDriver\WebDriverWait;
use PHPUnit\Framework\Assert;
use Sql;

class ContractAdminFeatureContext implements Context {

    private const UNSIGNED_ID = 98980;
    private const UNSIGNED_LINK = 'behat-contract-admin-unsigned';
    private const SIGNED_ID = 98981;
    private const SIGNED_LINK = 'behat-contract-admin-signed';
    private const CREATED_NAME = 'Created Contract Client';

    private RemoteWebDriver $driver;
    private WebDriverWait $wait;
    private array $expectedUnsigned = [];
    private ?array $expectedCreated = null;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void {
        $environment = $scope->getEnvironment();
        $this->driver = $environment->getContext('ui\\bootstrap\\BaseFeatureContext')->getDriver();
        $this->wait = new WebDriverWait($this->driver, 15);
        $this->expectedUnsigned = $this->defaultUnsignedExpectation();
        $this->expectedCreated = null;
    }

    /**
     * @AfterScenario
     */
    public function cleanupFixtures(): void {
        $sql = new Sql();

        $ids = [self::UNSIGNED_ID, self::SIGNED_ID];
        $createdRows = $sql->getRows(
            'SELECT id FROM contracts WHERE name = ?',
            [self::CREATED_NAME]
        );
        foreach ($createdRows as $row) {
            $ids[] = (int)$row['id'];
        }

        foreach (array_unique($ids) as $id) {
            $sql->executeStatement('DELETE FROM contract_line_items WHERE contract = ?', [$id]);
            $sql->executeStatement('DELETE FROM contracts WHERE id = ?', [$id]);
        }

        $max = $sql->getRow('SELECT MAX(id) AS maxId FROM contracts');
        $nextId = ((int)($max['maxId'] ?? 0)) + 1;
        $sql->executeStatement("ALTER TABLE contracts AUTO_INCREMENT = $nextId");
        $sql->disconnect();
    }

    /**
     * @Given /^a contract administration unsigned contract exists$/
     */
    public function aContractAdministrationUnsignedContractExists(): void {
        $sql = new Sql();
        $this->deleteContractFixture($sql, self::UNSIGNED_ID, self::UNSIGNED_LINK);
        $sql->executeStatement(
            'INSERT INTO contracts (id, link, type, name, address, number, email, date, location, session, details, amount, deposit, invoice, content, signature, initial, file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                self::UNSIGNED_ID,
                self::UNSIGNED_LINK,
                'commercial',
                'Contract Admin Client',
                '123 Test Street',
                '1234567890',
                'contract-admin@example.org',
                '2029-01-01',
                'Test Location',
                'Admin Session',
                'Test contract details',
                0,
                0,
                '',
                '<div id="contract-admin-public-content">Contract Admin Test Content</div>',
                null,
                null,
                null,
            ]
        );
        $sql->disconnect();

        $this->expectedUnsigned = $this->defaultUnsignedExpectation();
    }

    /**
     * @Given /^a contract administration signed contract exists$/
     */
    public function aContractAdministrationSignedContractExists(): void {
        $sql = new Sql();
        $this->deleteContractFixture($sql, self::SIGNED_ID, self::SIGNED_LINK);
        $sql->executeStatement(
            'INSERT INTO contracts (id, link, type, name, address, number, email, date, location, session, details, amount, deposit, invoice, content, signature, initial, file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                self::SIGNED_ID,
                self::SIGNED_LINK,
                'commercial',
                'Signed Contract Client',
                '456 Signed Street',
                '1234567890',
                'signed-contract@example.org',
                '2028-01-01',
                'Signed Location',
                'Signed Session',
                'Signed contract details',
                100,
                0,
                '',
                '<div>Signed Contract Content</div>',
                'signed',
                'initialed',
                '/contracts/behat-signed-contract.pdf',
            ]
        );
        $sql->disconnect();
    }

    /**
     * @When /^I update the administered contract name to "([^"]*)", session to "([^"]*)", and date to "([^"]*)"$/
     */
    public function iUpdateTheAdministeredContract(
        string $name,
        string $session,
        string $date
    ): void {
        $this->openUnsignedContractEditor();
        $this->setInputValue('contract-name', $name);
        $this->setInputValue('contract-session', $session);
        $this->setInputValue('contract-date', $date);

        $this->expectedUnsigned['name'] = $name;
        $this->expectedUnsigned['session'] = $session;
        $this->expectedUnsigned['date'] = $date;

        $this->saveOpenContract();
    }

    /**
     * @Then /^I see the updated administered contract in the contracts table$/
     */
    public function iSeeTheUpdatedAdministeredContractInTheContractsTable(): void {
        $this->assertContractRowById(self::UNSIGNED_ID, $this->expectedUnsigned);
    }

    /**
     * @When /^I create a commercial contract for "([^"]*)" with session "([^"]*)" and date "([^"]*)"$/
     */
    public function iCreateACommercialContract(
        string $name,
        string $session,
        string $date
    ): void {
        $this->startCreatingCommercialContract();
        $this->setInputValue('contract-name', $name);
        $this->setInputValue('contract-session', $session);
        $this->setInputValue('contract-date', $date);

        $this->expectedCreated = [
            'name' => $name,
            'type' => 'Commercial',
            'session' => $session,
            'date' => $date,
            'signed' => 'false',
        ];

        $this->saveOpenContract();
    }

    /**
     * @Then /^I see the created contract in the contracts table$/
     */
    public function iSeeTheCreatedContractInTheContractsTable(): void {
        Assert::assertNotNull($this->expectedCreated);
        $row = $this->waitForContractRowByName($this->expectedCreated['name']);
        $this->assertContractRow($row, $this->expectedCreated);
    }

    /**
     * @When /^I start creating a commercial contract$/
     */
    public function iStartCreatingACommercialContract(): void {
        $this->startCreatingCommercialContract();
    }

    /**
     * @When /^I provide "([^"]*)" for the administered contract session$/
     */
    public function iProvideForTheAdministeredContractSession(string $session): void {
        $this->setInputValue('contract-session', $session);
    }

    /**
     * @When /^I try to save the administered contract$/
     */
    public function iTryToSaveTheAdministeredContract(): void {
        $this->clickContractSaveButton();
    }

    /**
     * @Then /^I see a contract administration error indicating a client name is required$/
     */
    public function iSeeAContractAdministrationErrorIndicatingAClientNameIsRequired(): void {
        $alert = WebDriverBy::cssSelector(
            '.bootstrap-dialog.modal.in .bootstrap-dialog-body .alert-danger'
        );
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($alert));
        Assert::assertStringContainsString(
            'Please enter a Client Name',
            $this->driver->findElement($alert)->getText()
        );
    }

    /**
     * @When /^I view the administered unsigned contract$/
     */
    public function iViewTheAdministeredUnsignedContract(): void {
        $row = $this->contractRowById(self::UNSIGNED_ID);
        $button = $row->findElement(WebDriverBy::className('view-contract-btn'));
        $button->click();

        $this->wait->until(function () {
            return str_contains(
                $this->driver->getCurrentURL(),
                '/contract.php?c=' . self::UNSIGNED_LINK
            );
        });
    }

    /**
     * @Then /^I see the administered contract on the public contract page$/
     */
    public function iSeeTheAdministeredContractOnThePublicContractPage(): void {
        Assert::assertStringContainsString(
            '/contract.php?c=' . self::UNSIGNED_LINK,
            $this->driver->getCurrentURL()
        );

        $content = WebDriverBy::id('contract-admin-public-content');
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($content));
        Assert::assertSame(
            'Contract Admin Test Content',
            trim($this->driver->findElement($content)->getText())
        );
        Assert::assertCount(
            1,
            $this->driver->findElements(WebDriverBy::id('contract-submit'))
        );
    }

    /**
     * @Then /^I see signed-only actions for the administered signed contract$/
     */
    public function iSeeSignedOnlyActionsForTheAdministeredSignedContract(): void {
        $row = $this->contractRowById(self::SIGNED_ID);

        Assert::assertCount(0, $row->findElements(WebDriverBy::className('edit-contract-btn')));
        Assert::assertCount(1, $row->findElements(WebDriverBy::className('dl-contract-btn')));
        Assert::assertCount(1, $row->findElements(WebDriverBy::className('view-contract-btn')));
        Assert::assertSame(
            'true',
            strtolower(trim($row->findElement(WebDriverBy::className('contract-signed'))->getText()))
        );
    }

    private function startCreatingCommercialContract(): void {
        $add = WebDriverBy::id('add-contract-btn');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($add));
        $this->driver->findElement($add)->click();

        $typeSelector = WebDriverBy::cssSelector('.bootstrap-dialog.modal.in #contract-type');
        $this->wait->until(function () use ($typeSelector) {
            $selects = $this->driver->findElements($typeSelector);
            if (count($selects) !== 1) {
                return false;
            }

            foreach ($selects[0]->findElements(WebDriverBy::tagName('option')) as $option) {
                if (trim($option->getText()) === 'Commercial') {
                    return true;
                }
            }
            return false;
        });

        $type = new WebDriverSelect($this->driver->findElement($typeSelector));
        $type->selectByVisibleText('Commercial');

        $create = WebDriverBy::xpath(
            "//div[contains(@class,'bootstrap-dialog') and contains(@class,'modal') and contains(@class,'in')]" .
            "[.//*[@id='contract-type']]//button[contains(@class,'btn-success')][contains(normalize-space(.),'Create Contract')]"
        );
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($create));
        $this->driver->findElement($create)->click();

        $name = WebDriverBy::id('contract-name');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($name));
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('contract-session')));
    }

    private function openUnsignedContractEditor(): void {
        $row = $this->contractRowById(self::UNSIGNED_ID);
        $row->findElement(WebDriverBy::className('edit-contract-btn'))->click();

        $this->wait->until(function () {
            $names = $this->driver->findElements(WebDriverBy::id('contract-name'));
            return count($names) === 1
                && $names[0]->isDisplayed()
                && $names[0]->getAttribute('value') === $this->expectedUnsigned['name'];
        });
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('contract-session')));
    }

    private function saveOpenContract(): void {
        $this->clickContractSaveButton();

        $this->wait->until(function () {
            return count($this->driver->findElements(WebDriverBy::id('contract-name'))) === 0;
        });
    }

    private function clickContractSaveButton(): void {
        $save = WebDriverBy::xpath(
            "//div[contains(@class,'bootstrap-dialog') and contains(@class,'modal') and contains(@class,'in')]" .
            "[.//*[@id='contract-name']]//button[contains(@class,'btn-success')][contains(normalize-space(.),'Save')]"
        );
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($save));
        $this->driver->findElement($save)->click();
    }

    private function setInputValue(string $id, string $value): void {
        $selector = WebDriverBy::id($id);
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated($selector));
        $element = $this->driver->findElement($selector);

        $this->driver->executeScript(
            "arguments[0].value = arguments[1];" .
            "arguments[0].dispatchEvent(new Event('input', {bubbles: true}));" .
            "arguments[0].dispatchEvent(new Event('change', {bubbles: true}));",
            [$element, $value]
        );

        $this->wait->until(function () use ($selector, $value) {
            return (string)$this->driver->findElement($selector)->getAttribute('value') === $value;
        });
    }

    private function assertContractRowById(int $id, array $expected): void {
        $this->wait->until(function () use ($id, $expected) {
            $rows = $this->driver->findElements(
                WebDriverBy::cssSelector("#contracts tbody tr[contract-id='$id']")
            );
            return count($rows) === 1 && $this->contractRowMatches($rows[0], $expected);
        });

        $this->assertContractRow($this->contractRowById($id), $expected);
    }

    private function assertContractRow(RemoteWebElement $row, array $expected): void {
        Assert::assertTrue($this->contractRowMatches($row, $expected));
        Assert::assertSame($expected['name'], trim($row->findElement(WebDriverBy::className('contract-name'))->getText()));
        Assert::assertSame($expected['type'], trim($row->findElement(WebDriverBy::className('contract-type'))->getText()));
        Assert::assertSame($expected['session'], trim($row->findElement(WebDriverBy::className('contract-session'))->getText()));
        Assert::assertSame($expected['date'], trim($row->findElement(WebDriverBy::className('contract-date'))->getText()));
        Assert::assertSame(
            $expected['signed'],
            strtolower(trim($row->findElement(WebDriverBy::className('contract-signed'))->getText()))
        );
    }

    private function contractRowMatches(RemoteWebElement $row, array $expected): bool {
        return trim($row->findElement(WebDriverBy::className('contract-name'))->getText()) === $expected['name']
            && trim($row->findElement(WebDriverBy::className('contract-type'))->getText()) === $expected['type']
            && trim($row->findElement(WebDriverBy::className('contract-session'))->getText()) === $expected['session']
            && trim($row->findElement(WebDriverBy::className('contract-date'))->getText()) === $expected['date']
            && strtolower(trim($row->findElement(WebDriverBy::className('contract-signed'))->getText())) === $expected['signed'];
    }

    private function contractRowById(int $id): RemoteWebElement {
        $selector = WebDriverBy::cssSelector("#contracts tbody tr[contract-id='$id']");
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated($selector));
        return $this->driver->findElement($selector);
    }

    private function waitForContractRowByName(string $name): RemoteWebElement {
        $this->wait->until(function () use ($name) {
            return count($this->contractRowsByName($name)) === 1;
        });
        return $this->contractRowsByName($name)[0];
    }

    private function contractRowsByName(string $name): array {
        return $this->driver->findElements(
            WebDriverBy::xpath(
                "//table[@id='contracts']//tbody//tr" .
                "[td[contains(concat(' ', normalize-space(@class), ' '), ' contract-name ')]" .
                "[normalize-space(.)=" . $this->xpathLiteral($name) . "]]"
            )
        );
    }

    private function defaultUnsignedExpectation(): array {
        return [
            'name' => 'Contract Admin Client',
            'type' => 'Commercial',
            'session' => 'Admin Session',
            'date' => '2029-01-01',
            'signed' => 'false',
        ];
    }

    private function deleteContractFixture(Sql $sql, int $id, string $link): void {
        $sql->executeStatement('DELETE FROM contract_line_items WHERE contract = ?', [$id]);
        $sql->executeStatement('DELETE FROM contracts WHERE id = ? OR link = ?', [$id, $link]);
    }

    private function xpathLiteral(string $value): string {
        if (!str_contains($value, "'")) {
            return "'$value'";
        }
        if (!str_contains($value, '"')) {
            return '"' . $value . '"';
        }

        $parts = explode("'", $value);
        return 'concat(' . implode(', "\'", ', array_map(fn($part) => "'$part'", $parts)) . ')';
    }
}
