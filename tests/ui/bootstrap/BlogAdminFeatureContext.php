<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;
use Facebook\WebDriver\WebDriverWait;
use GuzzleHttp\Client;
use PHPUnit\Framework\Assert;
use Sql;

class BlogAdminFeatureContext implements Context {

    private const FIXTURE_DATE = '2099-12-29';
    private const FIXTURE_TITLE = 'Behat Blog Admin Draft';
    private const FIXTURE_UPLOAD_FILENAME = 'behat-blog-admin-fixture.jpg';
    private const TITLE_PREFIX = 'Behat Blog Admin ';
    private const UPLOAD_FILENAME = 'flower-short.jpeg';
    private const NEW_CATEGORY = 'Behat Blog Admin Category';

    private RemoteWebDriver $driver;
    private WebDriverWait $wait;
    private string $baseUrl;
    private Sql $sql;
    private ?int $fixtureId = null;
    private ?int $createdPostId = null;
    private string $expectedTitle = '';
    private string $expectedText = '';
    private string $expectedDate = '';
    private string $expectedCategory = '';

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void {
        $environment = $scope->getEnvironment();
        $base = $environment->getContext('ui\\bootstrap\\BaseFeatureContext');

        $this->driver = $base->getDriver();
        $this->baseUrl = $base->getBaseUrl();
        $this->wait = new WebDriverWait($this->driver, 15);
        $this->sql = new Sql();
        $this->fixtureId = null;
        $this->createdPostId = null;
        $this->expectedTitle = '';
        $this->expectedText = '';
        $this->expectedDate = '';
        $this->expectedCategory = '';

        $this->removeStaleTestData();
    }

    /**
     * @AfterScenario
     */
    public function cleanupFixtures(): void {
        try {
            $this->removeStaleTestData();
            $this->resetAutoIncrement('blog_details');
            $this->resetAutoIncrement('tags');
        } finally {
            $this->sql->disconnect();
        }
    }

    /**
     * @Given /^a blog administration draft exists$/
     */
    public function aBlogAdministrationDraftExists(): void {
        $this->fixtureId = $this->createFixtureDraft();
    }

    /**
     * @When /^I enter "([^"]*)" as the blog administration title$/
     */
    public function iEnterTheBlogAdministrationTitle(string $title): void {
        $this->expectedTitle = $title;
        $this->setTextInput('post-title-input', $title);
    }

    /**
     * @When /^I set the blog administration date to "([^"]*)"$/
     */
    public function iSetTheBlogAdministrationDate(string $date): void {
        $this->expectedDate = $date;
        $this->setElementValue('post-date-input', $date);
    }

    /**
     * @When /^I add blog administration text "([^"]*)"$/
     */
    public function iAddBlogAdministrationText(string $text): void {
        $this->expectedText = $text;

        $before = count($this->driver->findElements(WebDriverBy::cssSelector('.blog-editable-text')));
        $button = WebDriverBy::id('add-text-button');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($button));
        $this->driver->findElement($button)->click();

        $this->wait->until(function () use ($before) {
            return count($this->driver->findElements(WebDriverBy::cssSelector('.blog-editable-text'))) === $before + 1;
        });

        $editors = $this->driver->findElements(WebDriverBy::cssSelector('.blog-editable-text .note-editable'));
        Assert::assertNotEmpty($editors);
        $this->setRichText($editors[count($editors) - 1], $text);
    }

    /**
     * @When /^I preview the blog administration post$/
     */
    public function iPreviewTheBlogAdministrationPost(): void {
        $button = WebDriverBy::id('preview-post');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($button));
        $this->driver->findElement($button)->click();

        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('post-title-preview')));
    }

    /**
     * @Then /^I see the blog administration preview title "([^"]*)"$/
     */
    public function iSeeTheBlogAdministrationPreviewTitle(string $title): void {
        Assert::assertSame(
            $title,
            trim($this->driver->findElement(WebDriverBy::id('post-title-preview'))->getText())
        );
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('post-title-input'))->isDisplayed());
    }

    /**
     * @Then /^I see blog administration preview text "([^"]*)"$/
     */
    public function iSeeBlogAdministrationPreviewText(string $text): void {
        $this->wait->until(function () use ($text) {
            foreach ($this->driver->findElements(WebDriverBy::cssSelector('.blog-editable-text .blog-text-content')) as $content) {
                if ($content->isDisplayed() && str_contains($content->getText(), $text)) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * @When /^I return to blog administration editing$/
     */
    public function iReturnToBlogAdministrationEditing(): void {
        $button = WebDriverBy::id('edit-post');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($button));
        $element = $this->driver->findElement($button);
        $this->driver->executeScript("arguments[0].scrollIntoView({block: 'center'});", [$element]);
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($button));
        $element->click();
    }

    /**
     * @Then /^I see the blog administration editing controls$/
     */
    public function iSeeTheBlogAdministrationEditingControls(): void {
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('post-title-input')));
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('preview-post'))->isDisplayed());
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('edit-post'))->isDisplayed());
        Assert::assertNotEmpty($this->driver->findElements(WebDriverBy::cssSelector('.blog-editable-text .note-editor')));
    }

    /**
     * @When /^I try to save the blog administration draft$/
     */
    public function iTryToSaveTheBlogAdministrationDraft(): void {
        $this->clickEditorButton('save-post');
    }

    /**
     * @Then /^I see the blog administration dialog message "([^"]*)"$/
     */
    public function iSeeTheBlogAdministrationDialogMessage(string $message): void {
        $dialogBody = WebDriverBy::cssSelector('.bootstrap-dialog.modal.in .bootstrap-dialog-body');
        $this->wait->until(function () use ($dialogBody, $message) {
            foreach ($this->driver->findElements($dialogBody) as $body) {
                if ($body->isDisplayed() && str_contains($body->getText(), $message)) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * @When /^I upload the blog administration test image$/
     */
    public function iUploadTheBlogAdministrationTestImage(): void {
        $source = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . self::UPLOAD_FILENAME;
        if (!is_file($source)) {
            throw new \RuntimeException('Blog administration upload fixture does not exist');
        }

        // Selenium runs in a separate process/container, so use the repository
        // fixture path that is shared with the browser instead of a host /tmp file.
        $fileInput = WebDriverBy::cssSelector('#add-images-button input[type="file"]');
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated($fileInput));
        $this->driver->findElement($fileInput)->sendKeys(realpath($source));

        $this->wait->until(function () {
            foreach ($this->driver->findElements(WebDriverBy::cssSelector('#post-preview-image option')) as $option) {
                if (trim($option->getText()) === self::UPLOAD_FILENAME) {
                    return true;
                }
            }
            return false;
        });
        $this->wait->until(function () {
            foreach ($this->driver->findElements(WebDriverBy::cssSelector('#post-image-holder img')) as $image) {
                if (str_ends_with((string)$image->getAttribute('src'), '/' . self::UPLOAD_FILENAME)) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * @When /^I select the uploaded image as the blog administration preview$/
     */
    public function iSelectTheUploadedImageAsTheBlogAdministrationPreview(): void {
        $selectElement = $this->driver->findElement(WebDriverBy::id('post-preview-image'));
        (new WebDriverSelect($selectElement))->selectByVisibleText(self::UPLOAD_FILENAME);

        $this->wait->until(function () {
            $images = $this->driver->findElements(WebDriverBy::cssSelector('#post-preview-holder img.blog-preview-image'));
            return count($images) === 1
                && str_ends_with((string)$images[0]->getAttribute('src'), '/' . self::UPLOAD_FILENAME);
        });
    }

    /**
     * @When /^I add the "([^"]*)" blog administration category$/
     */
    public function iAddTheBlogAdministrationCategory(string $category): void {
        $this->expectedCategory = $category;
        $selectElement = $this->driver->findElement(WebDriverBy::id('post-tags-select'));
        (new WebDriverSelect($selectElement))->selectByVisibleText($category);
        $this->waitForSelectedCategory($category);
    }

    /**
     * @When /^I save the blog administration draft$/
     */
    public function iSaveTheBlogAdministrationDraft(): void {
        $this->clickEditorButton('save-post');
        $this->waitForPostRedirect();
        $this->createdPostId = $this->postIdFromCurrentUrl();
    }

    /**
     * @Then /^the created blog administration post is stored as a draft$/
     */
    public function theCreatedBlogAdministrationPostIsStoredAsADraft(): void {
        $row = $this->postRowById($this->createdPostId());
        Assert::assertSame(
            $this->expectedTitle,
            trim($row->findElement(WebDriverBy::className('post-title'))->getText())
        );
        Assert::assertSame(
            'false',
            trim($row->findElement(WebDriverBy::className('post-active'))->getText())
        );
    }

    /**
     * @Then /^I see the created blog administration post$/
     */
    public function iSeeTheCreatedBlogAdministrationPost(): void {
        Assert::assertStringContainsString(
            '/blog/post.php?p=' . $this->createdPostId(),
            $this->driver->getCurrentURL()
        );
        $this->waitForPostContent($this->expectedText);
        Assert::assertSame(
            $this->expectedTitle,
            trim($this->driver->findElement(WebDriverBy::tagName('h1'))->getText())
        );
        Assert::assertStringContainsString(
            $this->expectedText,
            $this->driver->findElement(WebDriverBy::id('post-content'))->getText()
        );
        if ($this->expectedCategory !== '') {
            Assert::assertStringContainsString(
                $this->expectedCategory,
                $this->driver->findElement(WebDriverBy::id('post-content'))->getText()
            );
        }
        if ($this->expectedDate !== '') {
            Assert::assertStringContainsString(
                date('F jS, Y', strtotime($this->expectedDate)),
                $this->driver->findElement(WebDriverBy::id('post-content'))->getText()
            );
        }
    }

    /**
     * @When /^I open blog administration management$/
     */
    public function iOpenBlogAdministrationManagement(): void {
        $this->driver->get($this->baseUrl . 'blog/manage.php');
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('posts')));
    }

    /**
     * @When /^I create the blog administration category "([^"]*)"$/
     */
    public function iCreateTheBlogAdministrationCategory(string $category): void {
        $selectElement = $this->driver->findElement(WebDriverBy::id('post-tags-select'));
        (new WebDriverSelect($selectElement))->selectByVisibleText('New Category');

        $input = WebDriverBy::id('new-category-name');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($input));
        $this->driver->findElement($input)->clear()->sendKeys($category);

        $create = WebDriverBy::xpath(
            "//div[contains(@class,'bootstrap-dialog') and contains(@class,'modal') and contains(@class,'in')]" .
            "[.//*[@id='new-category-name']]//button[contains(@class,'btn-success')]" .
            "[contains(normalize-space(.),'Create Category')]"
        );
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($create));
        $this->driver->findElement($create)->click();

        $this->waitForSelectedCategory($category);
    }

    /**
     * @Then /^I see the "([^"]*)" blog administration category selected$/
     */
    public function iSeeTheBlogAdministrationCategorySelected(string $category): void {
        $this->waitForSelectedCategory($category);
    }

    /**
     * @When /^I reopen the new blog administration editor$/
     */
    public function iReopenTheNewBlogAdministrationEditor(): void {
        $this->driver->get($this->baseUrl . 'blog/new.php');
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('post-tags-select')));
    }

    /**
     * @Then /^the "([^"]*)" blog category exists$/
     */
    public function theBlogCategoryExists(string $category): void {
        foreach ($this->driver->findElements(WebDriverBy::cssSelector('#post-tags-select option')) as $option) {
            if (trim($option->getText()) === $category) {
                return;
            }
        }
        Assert::fail("Expected blog category '$category' to be available after reopening the editor");
    }

    /**
     * @When /^I open the full editor for the blog administration draft$/
     */
    public function iOpenTheFullEditorForTheBlogAdministrationDraft(): void {
        $row = $this->postRowById($this->fixtureId());
        $button = $row->findElement(WebDriverBy::className('edit-post-btn'));
        $this->driver->executeScript("arguments[0].scrollIntoView({block: 'center'});", [$button]);
        $button->click();

        $this->wait->until(function () {
            return str_contains($this->driver->getCurrentURL(), '/blog/new.php?p=' . $this->fixtureId());
        });
        $this->wait->until(function () {
            $title = $this->driver->findElements(WebDriverBy::id('post-title-input'));
            return count($title) === 1
                && $title[0]->isDisplayed()
                && $title[0]->getAttribute('value') === self::FIXTURE_TITLE;
        });
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('.blog-editable-text .note-editable')
        ));
    }

    /**
     * @When /^I change the blog administration draft title to "([^"]*)"$/
     */
    public function iChangeTheBlogAdministrationDraftTitle(string $title): void {
        $this->expectedTitle = $title;
        $this->setTextInput('post-title-input', $title);
    }

    /**
     * @When /^I change the blog administration draft text to "([^"]*)"$/
     */
    public function iChangeTheBlogAdministrationDraftText(string $text): void {
        $this->expectedText = $text;
        $editor = WebDriverBy::cssSelector('.blog-editable-text .note-editable');
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated($editor));
        $this->setRichText($this->driver->findElement($editor), $text);
    }

    /**
     * @When /^I update the blog administration post$/
     */
    public function iUpdateTheBlogAdministrationPost(): void {
        $this->clickEditorButton('update-post');
        $this->waitForPostRedirect();
    }

    /**
     * @Then /^I see the blog administration draft post$/
     */
    public function iSeeTheBlogAdministrationDraftPost(): void {
        Assert::assertStringContainsString(
            '/blog/post.php?p=' . $this->fixtureId(),
            $this->driver->getCurrentURL()
        );
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::tagName('h1')));
        Assert::assertSame(
            $this->expectedTitle,
            trim($this->driver->findElement(WebDriverBy::tagName('h1'))->getText())
        );
        $this->waitForPostContent($this->expectedText);
        Assert::assertStringContainsString(
            $this->expectedText,
            $this->driver->findElement(WebDriverBy::id('post-content'))->getText()
        );
    }

    /**
     * @When /^I publish the blog administration draft$/
     */
    public function iPublishTheBlogAdministrationDraft(): void {
        $button = WebDriverBy::id('publish-saved-post');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($button));
        $this->driver->findElement($button)->click();

        $this->waitForPostRedirect();
    }

    /**
     * @Then /^I see the published blog administration post$/
     */
    public function iSeeThePublishedBlogAdministrationPost(): void {
        Assert::assertStringContainsString(
            '/blog/post.php?p=' . $this->fixtureId(),
            $this->driver->getCurrentURL()
        );
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::tagName('h1')));
        Assert::assertSame(
            self::FIXTURE_TITLE,
            trim($this->driver->findElement(WebDriverBy::tagName('h1'))->getText())
        );
    }

    /**
     * @When /^I reopen the published blog administration post$/
     */
    public function iReopenThePublishedBlogAdministrationPost(): void {
        $this->driver->get($this->baseUrl . 'blog/post.php?p=' . $this->fixtureId());
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::tagName('h1')));
    }

    /**
     * @Then /^I see the published blog administration post as a visitor$/
     */
    public function iSeeThePublishedBlogAdministrationPostAsAVisitor(): void {
        Assert::assertSame(
            self::FIXTURE_TITLE,
            trim($this->driver->findElement(WebDriverBy::tagName('h1'))->getText())
        );
        Assert::assertCount(0, $this->driver->findElements(WebDriverBy::id('edit-post-btn')));
    }

    /**
     * @When /^I quick edit the blog administration draft$/
     */
    public function iQuickEditTheBlogAdministrationDraft(): void {
        $row = $this->postRowById($this->fixtureId());
        $button = $row->findElement(WebDriverBy::className('quick-edit-post-btn'));
        $this->driver->executeScript("arguments[0].scrollIntoView({block: 'center'});", [$button]);
        $button->click();

        $modal = WebDriverBy::cssSelector('#post.modal.in');
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($modal));
        $this->wait->until(function () {
            $title = $this->driver->findElement(WebDriverBy::id('post-title-input'));
            return $title->getAttribute('value') === self::FIXTURE_TITLE;
        });
        $this->waitForSelectedCategory('Tea Ceremony');
        $this->wait->until(function () {
            return $this->driver->findElement(WebDriverBy::id('post-preview-image'))->getAttribute('value') !== '';
        });
    }

    /**
     * @When /^I change the quick edit title to "([^"]*)"$/
     */
    public function iChangeTheQuickEditTitle(string $title): void {
        $this->expectedTitle = $title;
        $this->setTextInput('post-title-input', $title);
    }

    /**
     * @When /^I update the quick edited blog administration post$/
     */
    public function iUpdateTheQuickEditedBlogAdministrationPost(): void {
        $button = WebDriverBy::id('post-update-button');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($button));
        $this->driver->findElement($button)->click();

        $this->wait->until(function () {
            $modals = $this->driver->findElements(WebDriverBy::cssSelector('#post.modal.in'));
            return empty($modals) || !$modals[0]->isDisplayed();
        });
        $this->waitForPostRowTitle($this->fixtureId(), $this->expectedTitle);
    }

    /**
     * @Then /^I see the quick edited blog administration post in the manage table$/
     */
    public function iSeeTheQuickEditedBlogAdministrationPostInTheManageTable(): void {
        $this->waitForPostRowTitle($this->fixtureId(), $this->expectedTitle);
        $row = $this->postRowById($this->fixtureId());
        Assert::assertSame(
            $this->expectedTitle,
            trim($row->findElement(WebDriverBy::className('post-title'))->getText())
        );
    }

    /**
     * @When /^I reload blog administration management$/
     */
    public function iReloadBlogAdministrationManagement(): void {
        $this->driver->navigate()->refresh();
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('posts')));
    }

    /**
     * @Then /^the quick edited blog administration post is persisted$/
     */
    public function theQuickEditedBlogAdministrationPostIsPersisted(): void {
        $this->waitForPostRowTitle($this->fixtureId(), $this->expectedTitle);
        $row = $this->postRowById($this->fixtureId());
        Assert::assertSame(
            $this->expectedTitle,
            trim($row->findElement(WebDriverBy::className('post-title'))->getText())
        );
        Assert::assertSame(
            'false',
            trim($row->findElement(WebDriverBy::className('post-active'))->getText())
        );
    }

    /**
     * @When /^I delete the blog administration draft$/
     */
    public function iDeleteTheBlogAdministrationDraft(): void {
        $button = WebDriverBy::id('post-delete-button');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($button));
        $this->driver->findElement($button)->click();

        $confirmation = WebDriverBy::xpath(
            "//div[contains(@class,'bootstrap-dialog') and contains(@class,'modal') and contains(@class,'in')]" .
            "[.//*[contains(normalize-space(.),'Are you sure you want to delete this post?')]]"
        );
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($confirmation));
    }

    /**
     * @When /^I confirm deletion of the blog administration draft$/
     */
    public function iConfirmDeletionOfTheBlogAdministrationDraft(): void {
        $confirm = WebDriverBy::xpath(
            "//div[contains(@class,'bootstrap-dialog') and contains(@class,'modal') and contains(@class,'in')]" .
            "[.//*[contains(normalize-space(.),'Are you sure you want to delete this post?')]]" .
            "//button[normalize-space(.)='OK' or contains(normalize-space(.),'OK')]"
        );
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($confirm));
        $this->driver->findElement($confirm)->click();
    }

    /**
     * @Then /^I no longer see the blog administration draft in the manage table$/
     */
    public function iNoLongerSeeTheBlogAdministrationDraftInTheManageTable(): void {
        $selector = WebDriverBy::cssSelector("#posts tbody tr[post-id='" . $this->fixtureId() . "']");
        $this->wait->until(function () use ($selector) {
            return count($this->driver->findElements($selector)) === 0;
        });
    }

    /**
     * @When /^I reopen the deleted blog administration post$/
     */
    public function iReopenTheDeletedBlogAdministrationPost(): void {
        $this->driver->get($this->baseUrl . 'blog/post.php?p=' . $this->fixtureId());
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::tagName('h1')));
    }

    /**
     * @Then /^the blog administration draft no longer exists$/
     */
    public function theBlogAdministrationDraftNoLongerExists(): void {
        Assert::assertSame(
            '404 Not Found',
            trim($this->driver->findElement(WebDriverBy::tagName('h1'))->getText())
        );
    }

    private function clickEditorButton(string $id): void {
        $button = WebDriverBy::id($id);
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($button));
        $this->driver->findElement($button)->click();
    }

    private function waitForPostRedirect(): void {
        $this->wait->until(function () {
            return str_contains($this->driver->getCurrentURL(), '/blog/post.php?p=');
        });
    }

    private function postIdFromCurrentUrl(): int {
        $query = (string)parse_url($this->driver->getCurrentURL(), PHP_URL_QUERY);
        parse_str($query, $params);
        Assert::assertArrayHasKey('p', $params);
        Assert::assertGreaterThan(0, (int)$params['p']);
        return (int)$params['p'];
    }

    private function setTextInput(string $id, string $value): void {
        $selector = WebDriverBy::id($id);
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($selector));
        $element = $this->driver->findElement($selector);
        $element->clear();
        $element->sendKeys($value);

        $this->wait->until(function () use ($selector, $value) {
            return (string)$this->driver->findElement($selector)->getAttribute('value') === $value;
        });
    }

    private function setElementValue(string $id, string $value): void {
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

    private function setRichText(RemoteWebElement $editor, string $text): void {
        $this->driver->executeScript(
            "arguments[0].innerHTML = arguments[1];" .
            "arguments[0].dispatchEvent(new Event('input', {bubbles: true}));",
            [$editor, $text]
        );
        $this->wait->until(function () use ($editor, $text) {
            return str_contains($editor->getText(), $text);
        });
    }

    private function waitForSelectedCategory(string $category): void {
        $this->wait->until(function () use ($category) {
            foreach ($this->driver->findElements(WebDriverBy::cssSelector('#post-tags .selected-tag')) as $tag) {
                if (trim($tag->getText()) === $category) {
                    return true;
                }
            }
            return false;
        });
    }

    private function postRowById(int $id): RemoteWebElement {
        $selector = WebDriverBy::cssSelector("#posts tbody tr[post-id='$id']");
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated($selector));
        return $this->driver->findElement($selector);
    }

    private function waitForPostRowTitle(int $id, string $title): void {
        $this->wait->until(function () use ($id, $title) {
            try {
                $rows = $this->driver->findElements(
                    WebDriverBy::cssSelector("#posts tbody tr[post-id='$id']")
                );
                if (count($rows) !== 1) {
                    return false;
                }
                return trim($rows[0]->findElement(WebDriverBy::className('post-title'))->getText()) === $title;
            } catch (StaleElementReferenceException) {
                return false;
            }
        });
    }

    private function removeStaleTestData(): void {
        $rows = $this->sql->getRows(
            'SELECT id FROM blog_details WHERE title LIKE ?',
            [self::TITLE_PREFIX . '%']
        );
        foreach ($rows as $row) {
            $id = (int)$row['id'];
            if (!$this->deletePostViaApi($id)) {
                $this->deleteBlogRows($id);
            }
        }

        $categoryRows = $this->sql->getRows('SELECT id FROM tags WHERE tag = ?', [self::NEW_CATEGORY]);
        foreach ($categoryRows as $row) {
            $this->sql->executeStatement('DELETE FROM blog_tags WHERE tag = ?', [(int)$row['id']]);
            $this->sql->executeStatement('DELETE FROM tags WHERE id = ?', [(int)$row['id']]);
        }

        foreach ([self::UPLOAD_FILENAME, self::FIXTURE_UPLOAD_FILENAME] as $filename) {
            $uploaded = $this->repoRoot() . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $filename;
            if (is_file($uploaded)) {
                @unlink($uploaded);
            }
        }

    }

    private function createFixtureDraft(): int {
        $client = $this->httpClient();
        $source = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'flower-short.jpeg';

        $upload = $client->request('POST', 'api/upload-blog-images.php', [
            'headers' => $this->adminHeaders(),
            'http_errors' => false,
            'multipart' => [[
                'name' => 'myfile',
                'contents' => fopen($source, 'rb'),
                'filename' => self::FIXTURE_UPLOAD_FILENAME,
                'headers' => ['Content-Type' => 'image/jpeg'],
            ]],
        ]);
        if ($upload->getStatusCode() !== 200) {
            throw new \RuntimeException(
                'Unable to upload blog administration fixture: ' . (string)$upload->getBody()
            );
        }

        $uploaded = json_decode((string)$upload->getBody(), true);
        if ($uploaded !== [self::FIXTURE_UPLOAD_FILENAME]) {
            throw new \RuntimeException('Unexpected blog administration fixture upload response');
        }

        $temporaryImage = '../tmp/' . self::FIXTURE_UPLOAD_FILENAME;
        $create = $client->request('POST', 'api/create-blog-post.php', [
            'headers' => $this->adminHeaders(),
            'http_errors' => false,
            'form_params' => [
                'title' => self::FIXTURE_TITLE,
                'date' => self::FIXTURE_DATE,
                'tags' => [29],
                'preview' => [
                    'img' => $temporaryImage,
                    'offset' => '0',
                ],
                'content' => [
                    1 => [
                        'group' => 1,
                        'type' => 'images',
                        'imgs' => [[
                            'location' => $temporaryImage,
                            'top' => '0',
                            'left' => '0',
                            'width' => '600',
                            'height' => '450',
                        ]],
                    ],
                    2 => [
                        'group' => 2,
                        'type' => 'text',
                        'text' => 'Original blog administration body',
                    ],
                ],
            ],
        ]);
        if ($create->getStatusCode() !== 200 || !ctype_digit(trim((string)$create->getBody()))) {
            throw new \RuntimeException(
                'Unable to create blog administration fixture: ' . (string)$create->getBody()
            );
        }

        return (int)trim((string)$create->getBody());
    }

    private function deletePostViaApi(int $id): bool {
        $response = $this->httpClient()->request('POST', 'api/delete-blog.php', [
            'headers' => $this->adminHeaders(),
            'http_errors' => false,
            'form_params' => ['post' => $id],
        ]);

        return $response->getStatusCode() === 200 && trim((string)$response->getBody()) === '';
    }

    private function httpClient(): Client {
        return new Client(['base_uri' => $this->baseUrl]);
    }

    private function adminHeaders(): array {
        return ['Cookie' => 'hash=1d7505e7f434a7713e84ba399e937191'];
    }

    private function createdPostId(): int {
        if ($this->createdPostId === null) {
            throw new \LogicException('Blog administration post has not been created');
        }
        return $this->createdPostId;
    }

    private function waitForPostContent(string $text): void {
        $this->wait->until(function () use ($text) {
            $content = $this->driver->findElements(WebDriverBy::id('post-content'));
            return count($content) === 1 && str_contains($content[0]->getText(), $text);
        });
    }

    private function fixtureId(): int {
        if ($this->fixtureId === null) {
            throw new \LogicException('Blog administration fixture has not been created');
        }
        return $this->fixtureId;
    }

    private function deleteBlogRows(int $id): void {
        $this->sql->executeStatement('DELETE FROM blog_comments WHERE blog = ?', [$id]);
        $this->sql->executeStatement('DELETE FROM blog_images WHERE blog = ?', [$id]);
        $this->sql->executeStatement('DELETE FROM blog_tags WHERE blog = ?', [$id]);
        $this->sql->executeStatement('DELETE FROM blog_texts WHERE blog = ?', [$id]);
        $this->sql->executeStatement('DELETE FROM blog_details WHERE id = ?', [$id]);
    }

    private function resetAutoIncrement(string $table): void {
        $row = $this->sql->getRow("SELECT MAX(id) AS maxId FROM `$table`");
        $next = ((int)($row['maxId'] ?? 0)) + 1;
        $this->sql->executeStatement("ALTER TABLE `$table` AUTO_INCREMENT = $next");
    }

    private function repoRoot(): string {
        return dirname(__DIR__, 3);
    }
}
