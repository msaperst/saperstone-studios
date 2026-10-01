<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use PHPUnit\Framework\Assert;
use RuntimeException;

class SiteImageAdminFeatureContext implements Context {

    private RemoteWebDriver $driver;
    private WebDriverWait $wait;
    private ?string $imagePath = null;
    private ?string $backupPath = null;
    private ?string $originalSignature = null;
    private ?string $savedSignature = null;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void {
        $environment = $scope->getEnvironment();
        $this->driver = $environment->getContext('ui\\bootstrap\\BaseFeatureContext')->getDriver();
        $this->wait = new WebDriverWait($this->driver, 15);
    }

    /**
     * @AfterScenario
     */
    public function restoreSiteImage(): void {
        if ($this->imagePath === null || $this->backupPath === null) {
            return;
        }

        $temporaryImage = dirname($this->imagePath) . DIRECTORY_SEPARATOR . 'tmp_' . basename($this->imagePath);
        if (is_file($temporaryImage) && !unlink($temporaryImage)) {
            throw new RuntimeException("Unable to remove temporary site image $temporaryImage");
        }

        if (is_file($this->imagePath) && !unlink($this->imagePath)) {
            throw new RuntimeException("Unable to remove edited site image {$this->imagePath}");
        }

        if (!copy($this->backupPath, $this->imagePath)) {
            throw new RuntimeException("Unable to restore site image {$this->imagePath}");
        }
        if (!unlink($this->backupPath)) {
            throw new RuntimeException("Unable to remove site image backup {$this->backupPath}");
        }

        $this->backupPath = null;
    }

    /**
     * @Given /^I have backed up the Portraits site image$/
     */
    public function iHaveBackedUpThePortraitsSiteImage(): void {
        $this->imagePath = dirname(__DIR__, 3)
            . DIRECTORY_SEPARATOR . 'content'
            . DIRECTORY_SEPARATOR . 'main'
            . DIRECTORY_SEPARATOR . 'portraits.jpg';

        Assert::assertFileExists($this->imagePath, 'The tracked Portraits site image fixture is missing');

        $temporaryImage = dirname($this->imagePath) . DIRECTORY_SEPARATOR . 'tmp_' . basename($this->imagePath);
        if (is_file($temporaryImage) && !unlink($temporaryImage)) {
            throw new RuntimeException("Unable to remove stale temporary site image $temporaryImage");
        }

        $backup = tempnam(sys_get_temp_dir(), 'site-image-admin-');
        if ($backup === false || !copy($this->imagePath, $backup)) {
            throw new RuntimeException('Unable to back up the Portraits site image');
        }

        $this->backupPath = $backup;
    }

    /**
     * @Then /^I see an edit control for the "([^"]*)" site image$/
     */
    public function iSeeAnEditControlForTheSiteImage(string $section): void {
        $holder = $this->siteImageHolder($section);
        $selector = WebDriverBy::cssSelector('.image-edit-control .ajax-file-upload');
        $this->wait->until(function () use ($holder, $selector) {
            $controls = $holder->findElements($selector);
            return count($controls) === 1 && $controls[0]->isDisplayed();
        });

        $controls = $holder->findElements($selector);
        Assert::assertCount(1, $controls);
        Assert::assertStringContainsString('Edit This Image', $controls[0]->getText());
    }

    /**
     * @When /^I upload "([^"]*)" for the "([^"]*)" site image$/
     */
    public function iUploadForTheSiteImage(string $fileName, string $section): void {
        $this->rememberOriginalImage($section);
        $this->selectUploadFixture($fileName, $section);

        $holder = $this->siteImageHolder($section);
        $this->wait->until(function () use ($holder) {
            $save = $holder->findElements(WebDriverBy::cssSelector('.saveme button'));
            $images = $holder->findElements(WebDriverBy::tagName('img'));
            return count($save) === 1
                && $save[0]->isDisplayed()
                && count($images) === 1
                && str_contains((string)$images[0]->getAttribute('src'), '/tmp_portraits.jpg');
        });
    }

    /**
     * @When /^I try to upload "([^"]*)" for the "([^"]*)" site image$/
     */
    public function iTryToUploadForTheSiteImage(string $fileName, string $section): void {
        $this->rememberOriginalImage($section);
        $this->selectUploadFixture($fileName, $section);

        $dialog = WebDriverBy::xpath(
            "//div[contains(@class, 'bootstrap-dialog')][.//div[contains(@class, 'bootstrap-dialog-title')][normalize-space(.)='Whoops, Something Went Wrong']]"
        );
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($dialog));
    }

    /**
     * @Then /^I see the "([^"]*)" site image ready to save$/
     */
    public function iSeeTheSiteImageReadyToSave(string $section): void {
        $holder = $this->siteImageHolder($section);
        $image = $this->siteImage($section);

        Assert::assertSame('/img/main/tmp_portraits.jpg', parse_url((string)$image->getAttribute('src'), PHP_URL_PATH));

        $saveButtons = $holder->findElements(WebDriverBy::cssSelector('.saveme button'));
        Assert::assertCount(1, $saveButtons);
        Assert::assertTrue($saveButtons[0]->isDisplayed());
        Assert::assertStringContainsString('Save This Image', $saveButtons[0]->getText());
        Assert::assertCount(0, $holder->findElements(WebDriverBy::className('overlay')));
    }

    /**
     * @When /^I save the "([^"]*)" site image$/
     */
    public function iSaveTheSiteImage(string $section): void {
        $holder = $this->siteImageHolder($section);
        $save = WebDriverBy::cssSelector('.saveme button');
        $this->wait->until(function () use ($holder, $save) {
            $buttons = $holder->findElements($save);
            return count($buttons) === 1 && $buttons[0]->isDisplayed() && $buttons[0]->isEnabled();
        });
        $holder->findElement($save)->click();

        $this->waitForSavedImage($section);
        $this->savedSignature = $this->imageSignature($this->siteImage($section));
    }

    /**
     * @Then /^I see the saved "([^"]*)" site image$/
     */
    public function iSeeTheSavedSiteImage(string $section): void {
        Assert::assertNotNull($this->originalSignature, 'No original browser image signature was captured');
        Assert::assertNotNull($this->savedSignature, 'No saved browser image signature was captured');

        $this->waitForSavedImage($section);
        $current = $this->imageSignature($this->siteImage($section));

        Assert::assertSame($this->savedSignature, $current);
        Assert::assertNotSame($this->originalSignature, $current, 'The rendered site image did not visibly change');
    }

    /**
     * @Then /^I see the saved "([^"]*)" site image after reload$/
     */
    public function iSeeTheSavedSiteImageAfterReload(string $section): void {
        Assert::assertNotNull($this->savedSignature, 'No saved browser image signature was captured before reload');

        $this->waitForSavedImage($section);
        Assert::assertSame(
            $this->savedSignature,
            $this->imageSignature($this->siteImage($section)),
            'The rendered site image changed after reload'
        );
    }

    /**
     * @Then /^I see a site image upload error indicating the image is too small$/
     */
    public function iSeeASiteImageUploadErrorIndicatingTheImageIsTooSmall(): void {
        $dialog = WebDriverBy::xpath(
            "//div[contains(@class, 'bootstrap-dialog')][.//div[contains(@class, 'bootstrap-dialog-title')][normalize-space(.)='Whoops, Something Went Wrong']]"
        );
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($dialog));
        $body = $this->driver->findElement($dialog)->findElement(WebDriverBy::className('bootstrap-dialog-body'));

        Assert::assertStringContainsString('minimum width requirements', $body->getText());
    }

    /**
     * @Then /^I see the original "([^"]*)" site image unchanged$/
     */
    public function iSeeTheOriginalSiteImageUnchanged(string $section): void {
        Assert::assertNotNull($this->originalSignature, 'No original browser image signature was captured');

        $image = $this->siteImage($section);
        $this->waitForImageLoad($image);
        Assert::assertSame('/img/main/portraits.jpg', parse_url((string)$image->getAttribute('src'), PHP_URL_PATH));
        Assert::assertSame($this->originalSignature, $this->imageSignature($image));
        Assert::assertCount(0, $this->siteImageHolder($section)->findElements(WebDriverBy::className('saveme')));
    }

    private function selectUploadFixture(string $fileName, string $section): void {
        $fixture = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . $fileName;
        Assert::assertFileExists($fixture, "Upload fixture $fileName does not exist");

        $holder = $this->siteImageHolder($section);
        $input = WebDriverBy::cssSelector("input[type='file'][name='myfile']");
        $this->wait->until(function () use ($holder, $input) {
            return count($holder->findElements($input)) === 1;
        });
        $holder->findElement($input)->sendKeys((string)realpath($fixture));
    }

    private function rememberOriginalImage(string $section): void {
        if ($this->originalSignature === null) {
            $this->originalSignature = $this->imageSignature($this->siteImage($section));
        }
    }

    private function waitForSavedImage(string $section): void {
        $this->wait->until(function () use ($section) {
            $holder = $this->siteImageHolder($section);
            $images = $holder->findElements(WebDriverBy::tagName('img'));
            if (count($images) !== 1) {
                return false;
            }

            $path = parse_url((string)$images[0]->getAttribute('src'), PHP_URL_PATH);
            return $path === '/img/main/portraits.jpg'
                && count($holder->findElements(WebDriverBy::className('saveme'))) === 0
                && count($holder->findElements(WebDriverBy::cssSelector('.overlay .info'))) === 1;
        });

        $this->waitForImageLoad($this->siteImage($section));
    }

    private function siteImageHolder(string $section): RemoteWebElement {
        $selector = WebDriverBy::cssSelector(
            'div[section="' . str_replace('"', '\\"', $section) . '"]'
        );
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated($selector));
        return $this->driver->findElement($selector);
    }

    private function siteImage(string $section): RemoteWebElement {
        return $this->siteImageHolder($section)->findElement(WebDriverBy::tagName('img'));
    }

    private function waitForImageLoad(RemoteWebElement $image): void {
        $this->wait->until(function () use ($image) {
            return $this->driver->executeScript(
                'return arguments[0].complete && arguments[0].naturalWidth > 0;',
                [$image]
            ) === true;
        });
    }

    private function imageSignature(RemoteWebElement $image): string {
        $this->waitForImageLoad($image);

        $signature = $this->driver->executeScript(
            "var image = arguments[0];" .
            "var canvas = document.createElement('canvas');" .
            "canvas.width = 16; canvas.height = 16;" .
            "var context = canvas.getContext('2d');" .
            "context.drawImage(image, 0, 0, 16, 16);" .
            "return canvas.toDataURL('image/png');",
            [$image]
        );

        Assert::assertIsString($signature);
        Assert::assertNotSame('', $signature);
        return $signature;
    }
}
