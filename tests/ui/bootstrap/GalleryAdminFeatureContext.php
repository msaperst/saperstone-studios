<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Exception;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use PHPUnit\Framework\Assert;
use ui\models\Gallery;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Gallery.php';

class GalleryAdminFeatureContext implements Context {
    private array $reorderedImageOrder = [];

    private RemoteWebDriver $driver;
    private WebDriverWait $wait;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope): void {
        $environment = $scope->getEnvironment();
        $this->driver = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getDriver();
        $this->wait = new WebDriverWait($this->driver, 10);
    }

    /**
     * @When /^I open the gallery for editing$/
     */
    public function iOpenGalleryForEditing(): void {
        $button = WebDriverBy::id('edit-gallery-btn');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($button));
        $this->driver->findElement($button)->click();
        $this->wait->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('new-gallery-title'))
        );
    }

    /**
     * @When /^I rename the gallery to "([^"]*)"$/
     */
    public function iRenameGallery($title): void {
        $this->iOpenGalleryForEditing();
        $input = $this->driver->findElement(WebDriverBy::id('new-gallery-title'));
        $input->clear()->sendKeys($title);
        $this->clickDialogButton('Save Details');
        $this->wait->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::id('new-gallery-title'))
        );
    }

    /**
     * @Then /^I see the gallery heading "([^"]*)"$/
     */
    public function iSeeGalleryHeading($heading): void {
        $selector = WebDriverBy::cssSelector('h1.page-header');
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($selector));
        Assert::assertSame($heading, trim($this->driver->findElement($selector)->getText()));
    }

    /**
     * @When /^I update the current gallery image title to "([^"]*)" and caption to "([^"]*)"$/
     */
    public function iUpdateCurrentGalleryImageMetadata($title, $caption): void {
        $button = WebDriverBy::id('edit-image-btn');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($button));
        $this->driver->findElement($button)->click();

        $titleInput = WebDriverBy::id('gallery-title');
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($titleInput));
        $this->setInputValue($titleInput, $title);

        $captionInput = WebDriverBy::id('gallery-caption');
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($captionInput));
        $this->setInputValue($captionInput, $caption);

        // Metadata changes should not rename the fixture file in this scenario.
        $filenameMatch = $this->driver->findElement(WebDriverBy::id('gallery-filename-match'));
        if ($filenameMatch->isSelected()) {
            $filenameMatch->click();
        }

        $this->clickDialogButton('Update');

        // The dialog only closes from the AJAX success callback, after the
        // server has responded and the active image metadata has been updated.
        // Wait on that user-visible completion signal; persistence is asserted
        // separately by the following Then step.
        $this->wait->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($titleInput)
        );
    }

    /**
     * @Then /^I see the current gallery image title "([^"]*)" and caption "([^"]*)"$/
     */
    public function iSeeCurrentGalleryImageMetadata($title, $caption): void {
        $active = WebDriverBy::cssSelector('.modal-carousel .carousel-inner .item.active');
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($active));
        $slide = $this->driver->findElement($active);

        Assert::assertSame(
            $title,
            $slide->findElement(WebDriverBy::className('contain'))->getAttribute('alt')
        );
        Assert::assertSame(
            $caption,
            trim($slide->findElement(WebDriverBy::cssSelector('.carousel-caption h2'))->getText())
        );
    }

    /**
     * @When /^I delete the current gallery image$/
     */
    public function iDeleteCurrentGalleryImage(): void {
        $button = WebDriverBy::id('delete-image-btn');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($button));
        $this->driver->findElement($button)->click();
        $this->clickDialogButton('Delete');
        $this->wait->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::xpath("//div[contains(@class, 'bootstrap-dialog')]//*[contains(normalize-space(.), 'Are you sure you want to delete the image')]")
            )
        );
    }

    /**
     * @Then /^I see (\d+) gallery images$/
     */
    public function iSeeGalleryImages($expected): void {
        $selector = WebDriverBy::cssSelector('.image-grid .gallery');
        $this->wait->until(function () use ($selector, $expected) {
            return count($this->driver->findElements($selector)) === (int) $expected;
        });
        Assert::assertCount((int) $expected, $this->driver->findElements($selector));
    }

    /**
     * @Then /^I see the uploaded gallery image$/
     */
    public function iSeeUploadedGalleryImage(): void {
        $selector = WebDriverBy::cssSelector('.image-grid .gallery img[alt="flower.jpeg"]');
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($selector));

        $images = $this->driver->findElements($selector);
        Assert::assertCount(1, $images);
        Assert::assertSame('flower.jpeg', $images[0]->getAttribute('alt'));
    }

    /**
     * @When /^I begin rearranging gallery images$/
     */
    public function iBeginRearrangingGalleryImages(): void {
        $gallery = new Gallery($this->driver, $this->wait);
        $gallery->waitForImagesToLoad(1);

        $button = WebDriverBy::id('sort-gallery-btn');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($button));
        $sortButton = $this->driver->findElement($button);
        $this->driver->executeScript(
            "arguments[0].scrollIntoView({block: 'center'});",
            [$sortButton]
        );
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($button));
        $sortButton->click();
        $this->wait->until(function () {
            return $this->driver->executeScript("return $('.image-grid').hasClass('ui-sortable');") === true;
        });
    }

    /**
     * @When /^I move gallery image (\d+) after gallery image (\d+)$/
     */
    public function iMoveGalleryImageAfter($sourceNumber, $targetNumber): void {
        $source = $this->galleryCard($sourceNumber);
        $target = $this->galleryCard($targetNumber);
        (new WebDriverActions($this->driver))->dragAndDrop($source, $target)->perform();

        $this->reorderedImageOrder = array_map('intval', $this->driver->executeScript(<<<'JS'
            var images = [];
            $('div.gallery').each(function () {
                images.push({
                    id: $(this).attr('image-id'),
                    col: $(this).parent().attr('id'),
                    height: $(this).position().top
                });
            });
            images.sort(function (a, b) {
                if (a.height === b.height) {
                    var x = a.col.toLowerCase(), y = b.col.toLowerCase();
                    return x < y ? -1 : x > y ? 1 : 0;
                }
                return a.height - b.height;
            });
            return images.map(function (image) {
                return image.id;
            });
JS
        ));

    }

    /**
     * @When /^I save the gallery image order$/
     */
    public function iSaveGalleryImageOrder(): void {
        $button = WebDriverBy::id('save-gallery-btn');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($button));
        // The fixed navigation can overlap this small toolbar button after a
        // drag operation. Trigger the same browser click without relying on
        // viewport hit-testing.
        $this->driver->executeScript("arguments[0].click();", [$this->driver->findElement($button)]);
        $this->wait->until(function () {
            return $this->driver->executeScript("return !$('.image-grid').hasClass('ui-sortable');") === true;
        });
    }

    /**
     * @Then /^I see the reordered gallery image order$/
     */
    public function iSeeReorderedGalleryImageOrder(): void {
        Assert::assertNotEmpty($this->reorderedImageOrder, 'No reordered gallery image order was captured');

        $this->wait->until(function () {
            return $this->visibleGalleryImageOrder() === $this->reorderedImageOrder;
        });

        Assert::assertSame($this->reorderedImageOrder, $this->visibleGalleryImageOrder());
    }

    /**
     * @When /^I upload the gallery test image$/
     */
    public function iUploadGalleryTestImage(): void {
        // jquery.uploadfile places its hidden file input alongside the upload
        // button in the dialog footer, not inside #upload-container.
        $input = WebDriverBy::cssSelector('.bootstrap-dialog.modal.in input[type="file"][name^="myfile"]');
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated($input));
        $file = realpath(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'flower.jpeg');
        if ($file === false) {
            throw new Exception('Unable to resolve gallery upload test image');
        }
        // WebDriver file selection fires the native change event. The upload
        // plugin handles that event by removing this input/form and creating a
        // replacement, so do not retain or interact with this element after
        // sendKeys().
        $this->driver->findElement($input)->sendKeys($file);

        $close = WebDriverBy::xpath(
            "//div[contains(@class, 'bootstrap-dialog') and contains(@class, 'modal') and contains(@class, 'in')]//button[normalize-space(.)='Close']"
        );

        // onSubmit disables the dialog controls; afterUploadAll enables them
        // only after the upload request has completed. Observe both states so
        // we cannot mistake the dialog's initial enabled state for completion.
        $this->wait->until(function () use ($close) {
            $buttons = $this->driver->findElements($close);
            return count($buttons) === 1 && !$buttons[0]->isEnabled();
        });
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($close));

        // Surface upload failures as the user sees them instead of allowing a
        // later image-count assertion to fail with an opaque timeout.
        $errors = $this->driver->findElements(
            WebDriverBy::cssSelector('.bootstrap-dialog.modal.in .ajax-file-upload-statusbar.alert-danger')
        );
        if ($errors !== []) {
            Assert::fail('Gallery upload failed in the UI: ' . trim($errors[0]->getText()));
        }
    }

    private function visibleGalleryImageOrder(): array {
        $images = [];
        foreach ($this->driver->findElements(WebDriverBy::cssSelector('.image-grid .gallery')) as $card) {
            $images[] = [
                'id' => (int) $card->getAttribute('image-id'),
                'sequence' => (int) $card->getAttribute('sequence'),
            ];
        }
        usort($images, static function (array $a, array $b): int {
            return $a['sequence'] <=> $b['sequence'];
        });
        return array_column($images, 'id');
    }

    private function setInputValue(WebDriverBy $selector, string $value): void {
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($selector));

        // Set dynamic Bootstrap-dialog fields through the browser and dispatch
        // the same input/change events the page receives from normal editing.
        // This avoids intermittent WebDriver key delivery without bypassing
        // any application behavior.
        $input = $this->driver->findElement($selector);
        $this->driver->executeScript(
            "arguments[0].focus(); arguments[0].value = arguments[1]; " .
            "arguments[0].dispatchEvent(new Event('input', {bubbles:true})); " .
            "arguments[0].dispatchEvent(new Event('change', {bubbles:true}));",
            [$input, $value]
        );

        Assert::assertSame($value, $input->getAttribute('value'));
    }

    private function galleryCard(int $imageNumber) {
        $sequence = $imageNumber - 1;
        $selector = WebDriverBy::cssSelector(".image-grid .gallery[sequence='$sequence']");
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated($selector));
        return $this->driver->findElement($selector);
    }

    private function clickDialogButton(string $label): void {
        $selector = WebDriverBy::xpath(
            "//div[contains(@class, 'bootstrap-dialog') and contains(@class, 'modal') and contains(@class, 'in')]//button[normalize-space(.)='$label']"
        );
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($selector));
        $buttons = $this->driver->findElements($selector);
        $buttons[count($buttons) - 1]->click();
    }

}
