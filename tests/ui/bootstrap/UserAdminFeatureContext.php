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
use ui\models\Login;

class UserAdminFeatureContext implements Context {

    private const USER_ID = 9898;
    private const USERNAME = 'behat_admin_user';
    private const USER_EMAIL = 'behat-admin-user@example.org';
    private const ALBUM_ID = 98989;
    private const ALBUM_NAME = 'User Admin Test Album';

    private RemoteWebDriver $driver;
    private WebDriverWait $wait;
    private array $expectedUser = [];
    private ?array $createdUser = null;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void {
        $environment = $scope->getEnvironment();
        $this->driver = $environment->getContext('ui\\bootstrap\\BaseFeatureContext')->getDriver();
        $this->wait = new WebDriverWait($this->driver, 15);
        $this->expectedUser = $this->defaultUserExpectation();
        $this->createdUser = null;
    }

    /**
     * @AfterScenario
     */
    public function cleanupFixtures(): void {
        $sql = new Sql();

        $userIds = [self::USER_ID];
        if ($this->createdUser !== null && isset($this->createdUser['id'])) {
            $userIds[] = (int)$this->createdUser['id'];
        }

        foreach (array_unique($userIds) as $userId) {
            $sql->executeStatement('DELETE FROM albums_for_users WHERE user = ?', [$userId]);
            $sql->executeStatement('DELETE FROM user_logs WHERE user = ?', [$userId]);
            $sql->executeStatement('DELETE FROM users WHERE id = ?', [$userId]);
        }

        // A create scenario can fail before its generated id is captured.
        $created = $sql->getRow('SELECT id FROM users WHERE usr = ?', ['behat_created_user']);
        if ($created !== null) {
            $createdId = (int)$created['id'];
            $sql->executeStatement('DELETE FROM albums_for_users WHERE user = ?', [$createdId]);
            $sql->executeStatement('DELETE FROM user_logs WHERE user = ?', [$createdId]);
            $sql->executeStatement('DELETE FROM users WHERE id = ?', [$createdId]);
        }

        $sql->executeStatement('DELETE FROM albums_for_users WHERE album = ?', [self::ALBUM_ID]);
        $sql->executeStatement('DELETE FROM user_logs WHERE album = ?', [self::ALBUM_ID]);
        $sql->executeStatement('DELETE FROM albums WHERE id = ?', [self::ALBUM_ID]);

        $nextUserId = ((int)$sql->getRow('SELECT MAX(id) AS maxId FROM users')['maxId']) + 1;
        $sql->executeStatement("ALTER TABLE users AUTO_INCREMENT = $nextUserId");
        $nextAlbumId = ((int)$sql->getRow('SELECT MAX(id) AS maxId FROM albums')['maxId']) + 1;
        $sql->executeStatement("ALTER TABLE albums AUTO_INCREMENT = $nextAlbumId");
        $sql->disconnect();
    }

    /**
     * @Given /^a user administration test user exists$/
     */
    public function aUserAdministrationTestUserExists(): void {
        $sql = new Sql();
        $this->removeFixtureUser($sql);

        $sql->executeStatement(
            'INSERT INTO users (id, usr, pass, firstName, lastName, email, role, hash, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                self::USER_ID,
                self::USERNAME,
                md5('old-password'),
                'Behat',
                'Admin User',
                self::USER_EMAIL,
                'downloader',
                '98989898989898989898989898989898',
                1,
            ]
        );
        $sql->disconnect();

        $this->expectedUser = $this->defaultUserExpectation();
    }

    /**
     * @Given /^a user administration test album exists$/
     */
    public function aUserAdministrationTestAlbumExists(): void {
        $sql = new Sql();
        $sql->executeStatement('DELETE FROM albums_for_users WHERE album = ?', [self::ALBUM_ID]);
        $sql->executeStatement('DELETE FROM user_logs WHERE album = ?', [self::ALBUM_ID]);
        $sql->executeStatement('DELETE FROM albums WHERE id = ?', [self::ALBUM_ID]);
        $sql->executeStatement(
            'INSERT INTO albums (id, name, description, date, location, code, owner) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                self::ALBUM_ID,
                self::ALBUM_NAME,
                'User administration test album',
                '2020-01-01 00:00:00',
                'user-admin-test',
                'user-admin-test-album',
                1,
            ]
        );
        $sql->disconnect();
    }

    /**
     * @Given /^the administered user has the user administration test album$/
     */
    public function theAdministeredUserHasTheUserAdministrationTestAlbum(): void {
        $sql = new Sql();
        $sql->executeStatement(
            'DELETE FROM albums_for_users WHERE user = ? AND album = ?',
            [self::USER_ID, self::ALBUM_ID]
        );
        $sql->executeStatement(
            'INSERT INTO albums_for_users (user, album) VALUES (?, ?)',
            [self::USER_ID, self::ALBUM_ID]
        );
        $sql->disconnect();
    }

    /**
     * @Given /^the administered user has a test activity log$/
     */
    public function theAdministeredUserHasATestActivityLog(): void {
        $sql = new Sql();
        $sql->executeStatement(
            'INSERT INTO user_logs (user, time, action, what, album) VALUES (?, CURRENT_TIMESTAMP, ?, ?, ?)',
            [self::USER_ID, 'Downloaded Image', 'sample0.jpg', self::ALBUM_ID]
        );
        $sql->disconnect();
    }

    /**
     * @When /^I update the administered user to "([^"]*)" with email "([^"]*)", role "([^"]*)", and inactive status$/
     */
    public function iUpdateTheAdministeredUser(
        string $name,
        string $email,
        string $role
    ): void {
        [$firstName, $lastName] = $this->splitName($name);
        $this->openUserEditor(self::USERNAME);

        $this->setInputValue('user-first-name', $firstName);
        $this->setInputValue('user-last-name', $lastName);
        $this->setInputValue('user-email', $email);
        $this->selectRole($role);
        $this->setCheckbox('user-active', false);

        $this->expectedUser = [
            'username' => self::USERNAME,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'role' => $role,
            'active' => '0',
        ];

        $this->clickVisibleButtonById('user-update-btn');
        $this->wait->until(function () {
            return count($this->driver->findElements(WebDriverBy::id('user-update-btn'))) === 0;
        });
    }

    /**
     * @Then /^I see the updated administered user in the users table$/
     */
    public function iSeeTheUpdatedAdministeredUserInTheUsersTable(): void {
        $this->assertUserRow($this->expectedUser);
    }

    /**
     * @When /^I create an active downloader user "([^"]*)" named "([^"]*)" with email "([^"]*)"$/
     */
    public function iCreateAnActiveDownloaderUser(string $username, string $name, string $email): void {
        $this->submitNewUser($username, $name, $email, true, true);
    }

    /**
     * @When /^I try to create an active downloader user "([^"]*)" named "([^"]*)" with email "([^"]*)"$/
     */
    public function iTryToCreateAnActiveDownloaderUser(string $username, string $name, string $email): void {
        $this->submitNewUser($username, $name, $email, true, false);
    }

    /**
     * @Then /^I see the created user's edit dialog$/
     */
    public function iSeeTheCreatedUsersEditDialog(): void {
        Assert::assertNotNull($this->createdUser);
        $this->wait->until(function () {
            $username = $this->driver->findElements(WebDriverBy::id('user-username'));
            return count($username) === 1
                && $username[0]->isDisplayed()
                && $username[0]->getAttribute('value') === $this->createdUser['username']
                && count($this->driver->findElements(WebDriverBy::id('user-update-btn'))) === 1;
        });
    }

    /**
     * @Then /^I see the created user in the users table$/
     */
    public function iSeeTheCreatedUserInTheUsersTable(): void {
        Assert::assertNotNull($this->createdUser);
        $this->assertUserRow($this->createdUser);
    }

    /**
     * @Then /^I see a user administration error indicating the username already exists$/
     */
    public function iSeeAUserAdministrationErrorIndicatingTheUsernameAlreadyExists(): void {
        $alert = WebDriverBy::cssSelector('.bootstrap-dialog.modal.in .alert-danger');
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($alert));
        Assert::assertStringContainsString(
            'username already exists',
            strtolower($this->driver->findElement($alert)->getText())
        );
    }

    /**
     * @When /^I delete the administered user$/
     */
    public function iDeleteTheAdministeredUser(): void {
        $this->openUserEditor(self::USERNAME);
        $this->clickVisibleButtonById('user-delete-btn');

        $confirm = WebDriverBy::xpath(
            "//div[contains(@class,'bootstrap-dialog') and contains(@class,'modal') and contains(@class,'in')]" .
            "[.//div[contains(@class,'bootstrap-dialog-title')][normalize-space(.)='Are You Sure?']]" .
            "//button[contains(@class,'btn-danger')][contains(normalize-space(.),'Delete')]"
        );
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($confirm));
        $this->driver->findElement($confirm)->click();

        $this->wait->until(function () {
            return count($this->driver->findElements(
                WebDriverBy::xpath(
                    "//div[contains(@class,'bootstrap-dialog') and contains(@class,'modal') and contains(@class,'in')]" .
                    "[.//div[contains(@class,'bootstrap-dialog-title')][normalize-space(.)='Are You Sure?']]"
                )
            )) === 0;
        });
    }

    /**
     * @Then /^I do not see the administered user in the users table$/
     */
    public function iDoNotSeeTheAdministeredUserInTheUsersTable(): void {
        $this->wait->until(function () {
            return count($this->userRows(self::USERNAME)) === 0;
        });
        Assert::assertCount(0, $this->userRows(self::USERNAME));
    }

    /**
     * @When /^I open album access for the administered user$/
     */
    public function iOpenAlbumAccessForTheAdministeredUser(): void {
        $this->openUserEditor(self::USERNAME);
        $this->clickVisibleButtonById('user-albums-btn');

        $search = WebDriverBy::cssSelector(
            ".bootstrap-dialog.modal.in #album-search"
        );
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($search));
        $this->wait->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('user-albums'))
        );

        // Existing assignments are loaded asynchronously after the dialog opens.
        $this->wait->until(function () {
            $loadingDialogs = $this->driver->findElements(
                WebDriverBy::xpath(
                    "//div[contains(@class,'bootstrap-dialog') and contains(@class,'modal') and contains(@class,'in')]" .
                    "[.//div[contains(@class,'bootstrap-dialog-title')][contains(normalize-space(.),'Albums for User')]]"
                )
            );
            return count($loadingDialogs) === 1;
        });
    }

    /**
     * @When /^I add the user administration test album$/
     */
    public function iAddTheUserAdministrationTestAlbum(): void {
        $search = $this->driver->findElement(WebDriverBy::id('album-search'));
        $search->clear();
        $search->sendKeys(self::ALBUM_NAME);

        $result = WebDriverBy::cssSelector(
            ".search-results a[album-id='" . self::ALBUM_ID . "']"
        );
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($result));
        $this->driver->findElement($result)->click();

        $this->waitForSelectedAlbum(true);
    }

    /**
     * @When /^I remove the user administration test album$/
     */
    public function iRemoveTheUserAdministrationTestAlbum(): void {
        $this->waitForSelectedAlbum(true);
        $selected = WebDriverBy::cssSelector(
            "#user-albums .selected-album[album-id='" . self::ALBUM_ID . "']"
        );
        $this->driver->findElement($selected)->click();
        $this->waitForSelectedAlbum(false);
    }

    /**
     * @Then /^I see the test album selected for the administered user$/
     */
    public function iSeeTheTestAlbumSelectedForTheAdministeredUser(): void {
        $this->waitForSelectedAlbum(true);
        $selected = $this->driver->findElement(
            WebDriverBy::cssSelector(
                "#user-albums .selected-album[album-id='" . self::ALBUM_ID . "']"
            )
        );
        Assert::assertSame(self::ALBUM_NAME, trim($selected->getText()));
    }

    /**
     * @Then /^I do not see the test album selected for the administered user$/
     */
    public function iDoNotSeeTheTestAlbumSelectedForTheAdministeredUser(): void {
        $this->waitForSelectedAlbum(false);
        Assert::assertCount(
            0,
            $this->driver->findElements(
                WebDriverBy::cssSelector(
                    "#user-albums .selected-album[album-id='" . self::ALBUM_ID . "']"
                )
            )
        );
    }

    /**
     * @When /^I save administered user album access$/
     */
    public function iSaveAdministeredUserAlbumAccess(): void {
        $save = WebDriverBy::xpath(
            "//div[contains(@class,'bootstrap-dialog') and contains(@class,'modal') and contains(@class,'in')]" .
            "[.//div[contains(@class,'bootstrap-dialog-title')][contains(normalize-space(.),'Albums for User')]]" .
            "//button[contains(@class,'btn-success')][contains(normalize-space(.),'Update')]"
        );
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($save));
        $this->driver->findElement($save)->click();

        $this->wait->until(function () {
            return count($this->driver->findElements(WebDriverBy::id('album-search'))) === 0;
        });
    }

    /**
     * @When /^I change the administered user's password to "([^"]*)"$/
     */
    public function iChangeTheAdministeredUsersPasswordTo(string $password): void {
        $this->openUserEditor(self::USERNAME);
        $this->clickVisibleButtonById('user-update-password-btn');

        $passwordInput = WebDriverBy::id('user-password');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($passwordInput));
        $this->setInputValue('user-password', $password);
        $this->setInputValue('user-password-confirm', $password);

        $update = WebDriverBy::xpath(
            "//div[contains(@class,'bootstrap-dialog') and contains(@class,'modal') and contains(@class,'in')]" .
            "[.//div[contains(@class,'bootstrap-dialog-title')][normalize-space(.)='Change User Password']]" .
            "//button[contains(@class,'btn-success')][contains(normalize-space(.),'Update')]"
        );
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($update));
        $this->driver->findElement($update)->click();

        $this->wait->until(function () {
            return count($this->driver->findElements(WebDriverBy::id('user-password'))) === 0;
        });
    }

    /**
     * @When /^I close the administered user dialog$/
     */
    public function iCloseTheAdministeredUserDialog(): void {
        $close = WebDriverBy::xpath(
            "//div[contains(@class,'bootstrap-dialog') and contains(@class,'modal') and contains(@class,'in')]" .
            "[.//*[@id='user-update-btn']]//button[normalize-space(.)='Close']"
        );
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($close));
        $this->driver->findElement($close)->click();
        $this->wait->until(function () {
            return count($this->driver->findElements(WebDriverBy::id('user-update-btn'))) === 0;
        });
    }

    /**
     * @When /^I log out of the admin session$/
     */
    public function iLogOutOfTheAdminSession(): void {
        $login = new Login($this->driver, $this->wait);
        $login->logout('msaperst');
    }

    /**
     * @When /^I log in as the administered user with password "([^"]*)"$/
     */
    public function iLogInAsTheAdministeredUserWithPassword(string $password): void {
        $login = new Login($this->driver, $this->wait);
        $login->login(self::USERNAME, $password, false);
    }

    /**
     * @Then /^I see the administered user name displayed$/
     */
    public function iSeeTheAdministeredUserNameDisplayed(): void {
        $username = WebDriverBy::linkText(self::USERNAME);
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($username));
        Assert::assertTrue($this->driver->findElement($username)->isDisplayed());
    }

    /**
     * @When /^I view activity for the administered user$/
     */
    public function iViewActivityForTheAdministeredUser(): void {
        $row = $this->userRow(self::USERNAME);
        $button = $row->findElement(WebDriverBy::className('view-user-log-btn'));
        $button->click();

        $dialog = WebDriverBy::xpath(
            "//div[contains(@class,'bootstrap-dialog') and contains(@class,'modal') and contains(@class,'in')]" .
            "[.//div[contains(@class,'bootstrap-dialog-title')][normalize-space(.)='User Activity']]"
        );
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($dialog));
    }

    /**
     * @Then /^I see the administered user's test activity$/
     */
    public function iSeeTheAdministeredUsersTestActivity(): void {
        $dialog = WebDriverBy::xpath(
            "//div[contains(@class,'bootstrap-dialog') and contains(@class,'modal') and contains(@class,'in')]" .
            "[.//div[contains(@class,'bootstrap-dialog-title')][normalize-space(.)='User Activity']]"
        );
        $this->wait->until(function () use ($dialog) {
            $dialogs = $this->driver->findElements($dialog);
            return count($dialogs) === 1
                && str_contains($dialogs[0]->getText(), 'Downloaded Image')
                && str_contains($dialogs[0]->getText(), 'sample0.jpg')
                && str_contains($dialogs[0]->getText(), self::ALBUM_NAME);
        });

        $text = $this->driver->findElement($dialog)->getText();
        Assert::assertStringContainsString('Downloaded Image', $text);
        Assert::assertStringContainsString('sample0.jpg', $text);
        Assert::assertStringContainsString(self::ALBUM_NAME, $text);
    }

    /**
     * @When /^I view the site as the administered user$/
     */
    public function iViewTheSiteAsTheAdministeredUser(): void {
        $row = $this->userRow(self::USERNAME);
        $row->findElement(WebDriverBy::className('view-as-user-btn'))->click();

        $this->wait->until(function () {
            return str_ends_with($this->driver->getCurrentURL(), '/user/index.php');
        });
    }

    /**
     * @Then /^I am viewing the site as the administered user$/
     */
    public function iAmViewingTheSiteAsTheAdministeredUser(): void {
        Assert::assertStringEndsWith('/user/index.php', $this->driver->getCurrentURL());
        $username = WebDriverBy::linkText(self::USERNAME);
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($username));
        Assert::assertTrue($this->driver->findElement($username)->isDisplayed());
    }

    private function submitNewUser(
        string $username,
        string $name,
        string $email,
        bool $active,
        bool $expectSuccess
    ): void {
        [$firstName, $lastName] = $this->splitName($name);

        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('add-user-btn')));
        $this->driver->findElement(WebDriverBy::id('add-user-btn'))->click();
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('user-save-btn')));

        $this->setInputValue('user-username', $username);
        $this->setInputValue('user-first-name', $firstName);
        $this->setInputValue('user-last-name', $lastName);
        $this->setInputValue('user-email', $email);
        $this->selectRole('downloader');
        $this->setCheckbox('user-active', $active);

        $this->clickVisibleButtonById('user-save-btn');

        if (!$expectSuccess) {
            return;
        }

        $expected = [
            'username' => $username,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'role' => 'downloader',
            'active' => $active ? '1' : '0',
        ];

        $row = $this->waitForUserRow($username);
        $expected['id'] = (int)$row->getAttribute('user-id');
        $this->createdUser = $expected;

        $this->wait->until(function () use ($username) {
            $inputs = $this->driver->findElements(WebDriverBy::id('user-username'));
            return count($inputs) === 1
                && $inputs[0]->isDisplayed()
                && $inputs[0]->getAttribute('value') === $username
                && count($this->driver->findElements(WebDriverBy::id('user-update-btn'))) === 1;
        });
    }

    private function openUserEditor(string $username): void {
        $row = $this->userRow($username);
        $row->findElement(WebDriverBy::className('edit-user-btn'))->click();

        $this->wait->until(function () use ($username) {
            $inputs = $this->driver->findElements(WebDriverBy::id('user-username'));
            return count($inputs) === 1
                && $inputs[0]->isDisplayed()
                && $inputs[0]->getAttribute('value') === $username;
        });

        $this->wait->until(function () {
            $selects = $this->driver->findElements(WebDriverBy::id('user-role'));
            return count($selects) === 1
                && count($selects[0]->findElements(WebDriverBy::tagName('option'))) >= 3;
        });
    }

    private function assertUserRow(array $expected): void {
        $this->wait->until(function () use ($expected) {
            $rows = $this->userRows($expected['username']);
            if (count($rows) !== 1) {
                return false;
            }

            $row = $rows[0];
            return trim($row->findElement(WebDriverBy::className('user-name'))->getText())
                    === trim($expected['firstName'] . ' ' . $expected['lastName'])
                && trim($row->findElement(WebDriverBy::className('user-email'))->getText())
                    === $expected['email']
                && trim($row->findElement(WebDriverBy::className('user-role'))->getText())
                    === $expected['role']
                && trim($row->findElement(WebDriverBy::className('user-active'))->getText())
                    === $expected['active'];
        });

        $row = $this->userRow($expected['username']);
        Assert::assertSame(
            trim($expected['firstName'] . ' ' . $expected['lastName']),
            trim($row->findElement(WebDriverBy::className('user-name'))->getText())
        );
        Assert::assertSame($expected['email'], trim($row->findElement(WebDriverBy::className('user-email'))->getText()));
        Assert::assertSame($expected['role'], trim($row->findElement(WebDriverBy::className('user-role'))->getText()));
        Assert::assertSame($expected['active'], trim($row->findElement(WebDriverBy::className('user-active'))->getText()));
    }

    private function userRow(string $username): RemoteWebElement {
        return $this->waitForUserRow($username);
    }

    private function waitForUserRow(string $username): RemoteWebElement {
        $this->wait->until(function () use ($username) {
            return count($this->userRows($username)) === 1;
        });
        return $this->userRows($username)[0];
    }

    private function userRows(string $username): array {
        return $this->driver->findElements(
            WebDriverBy::xpath(
                "//table[@id='users']//tbody//tr" .
                "[td[contains(concat(' ', normalize-space(@class), ' '), ' user-username ')]" .
                "[normalize-space(.)=" . $this->xpathLiteral($username) . "]]"
            )
        );
    }

    private function selectRole(string $role): void {
        $this->wait->until(function () use ($role) {
            $selects = $this->driver->findElements(WebDriverBy::id('user-role'));
            if (count($selects) !== 1) {
                return false;
            }
            foreach ($selects[0]->findElements(WebDriverBy::tagName('option')) as $option) {
                if ($option->getAttribute('value') === $role) {
                    return true;
                }
            }
            return false;
        });

        $select = new WebDriverSelect($this->driver->findElement(WebDriverBy::id('user-role')));
        $select->selectByValue($role);
    }

    private function setInputValue(string $id, string $value): void {
        $selector = WebDriverBy::id($id);
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($selector));
        $input = $this->driver->findElement($selector);
        $input->clear();
        $input->sendKeys($value);
        $this->wait->until(function () use ($selector, $value) {
            return $this->driver->findElement($selector)->getAttribute('value') === $value;
        });
    }

    private function setCheckbox(string $id, bool $checked): void {
        $checkbox = $this->driver->findElement(WebDriverBy::id($id));
        if ($checkbox->isSelected() !== $checked) {
            $checkbox->click();
        }
        Assert::assertSame($checked, $checkbox->isSelected());
    }

    private function clickVisibleButtonById(string $id): void {
        $selector = WebDriverBy::cssSelector(".bootstrap-dialog.modal.in #$id");
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($selector));
        $this->driver->findElement($selector)->click();
    }

    private function waitForSelectedAlbum(bool $expected): void {
        $selector = WebDriverBy::cssSelector(
            "#user-albums .selected-album[album-id='" . self::ALBUM_ID . "']"
        );
        $this->wait->until(function () use ($selector, $expected) {
            $count = count($this->driver->findElements($selector));
            return $expected ? $count === 1 : $count === 0;
        });
    }

    private function defaultUserExpectation(): array {
        return [
            'username' => self::USERNAME,
            'firstName' => 'Behat',
            'lastName' => 'Admin User',
            'email' => self::USER_EMAIL,
            'role' => 'downloader',
            'active' => '1',
        ];
    }

    private function removeFixtureUser(Sql $sql): void {
        $sql->executeStatement('DELETE FROM albums_for_users WHERE user = ?', [self::USER_ID]);
        $sql->executeStatement('DELETE FROM user_logs WHERE user = ?', [self::USER_ID]);
        $sql->executeStatement('DELETE FROM users WHERE id = ?', [self::USER_ID]);
        $stale = $sql->getRow('SELECT id FROM users WHERE usr = ?', [self::USERNAME]);
        if ($stale !== null) {
            $staleId = (int)$stale['id'];
            $sql->executeStatement('DELETE FROM albums_for_users WHERE user = ?', [$staleId]);
            $sql->executeStatement('DELETE FROM user_logs WHERE user = ?', [$staleId]);
            $sql->executeStatement('DELETE FROM users WHERE id = ?', [$staleId]);
        }
    }

    private function splitName(string $name): array {
        $parts = explode(' ', trim($name), 2);
        return [$parts[0], $parts[1] ?? ''];
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
