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
use Sql;
use ui\models\Gallery;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Gallery.php';

class GalleryAdminFeatureContext implements Context {
    private array $originalImageOrder = [];
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
     * @When /^I open gallery (\d+) for editing$/
     */
    public function iOpenGalleryForEditing($galleryId): void {
        $button = WebDriverBy::id('edit-gallery-btn');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($button));
        $this->driver->findElement($button)->click();
        $this->wait->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('new-gallery-title'))
        );
        Assert::assertSame(
            (string) $galleryId,
            (string) $this->driver->findElement(WebDriverBy::id('gallery-config'))->getAttribute('data-gallery-id')
        );
    }

    /**
     * @When /^I rename gallery (\d+) to "([^"]*)"$/
     */
    public function iRenameGallery($galleryId, $title): void {
        $this->iOpenGalleryForEditing($galleryId);
        $input = $this->driver->findElement(WebDriverBy::id('new-gallery-title'));
        $input->clear()->sendKeys($title);
        $this->clickDialogButton('Save Details');
        $this->waitForGalleryTitle($galleryId, $title);
    }

    /**
     * @Then /^gallery (\d+) is named "([^"]*)"$/
     */
    public function galleryIsNamed($galleryId, $title): void {
        $sql = new Sql();
        $gallery = $sql->getRow('SELECT title FROM galleries WHERE id = ?', [$galleryId]);
        $sql->disconnect();
        Assert::assertSame($title, $gallery['title'] ?? null);
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
        $this->driver->findElement($titleInput)->clear()->sendKeys($title);
        $this->driver->findElement(WebDriverBy::id('gallery-caption'))->clear()->sendKeys($caption);

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
     * @Then /^gallery (\d+) image (\d+) has title "([^"]*)" and caption "([^"]*)"$/
     */
    public function galleryImageHasMetadata($galleryId, $imageNumber, $title, $caption): void {
        $sql = new Sql();
        $image = $sql->getRow(
            'SELECT title, caption FROM gallery_images WHERE gallery = ? AND sequence = ?',
            [$galleryId, $imageNumber - 1]
        );
        $sql->disconnect();

        Assert::assertSame($title, $image['title'] ?? null);
        Assert::assertSame($caption, $image['caption'] ?? null);
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
     * @Then /^gallery (\d+) has (\d+) images$/
     */
    public function galleryHasImages($galleryId, $expected): void {
        $this->wait->until(function () use ($galleryId, $expected) {
            $sql = new Sql();
            $count = $sql->getRowCount('SELECT * FROM gallery_images WHERE gallery = ?', [$galleryId]);
            $sql->disconnect();
            return $count === (int) $expected;
        });
    }

    /**
     * @When /^I begin rearranging gallery images$/
     */
    public function iBeginRearrangingGalleryImages(): void {
        $gallery = new Gallery($this->driver, $this->wait);
        $gallery->waitForImagesToLoad(1);

        $sql = new Sql();
        $images = $sql->getRows(
            'SELECT id FROM gallery_images WHERE gallery = ? ORDER BY sequence',
            [999]
        );
        $sql->disconnect();
        $this->originalImageOrder = array_map('intval', array_column($images, 'id'));

        $button = WebDriverBy::id('sort-gallery-btn');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($button));
        $this->driver->findElement($button)->click();
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

        Assert::assertNotSame(
            $this->originalImageOrder,
            $this->reorderedImageOrder,
            'Dragging the gallery image did not change its visual order'
        );
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
     * @Then /^the reordered gallery image order is persisted for gallery (\d+)$/
     */
    public function reorderedGalleryImageOrderIsPersisted($galleryId): void {
        Assert::assertNotEmpty($this->reorderedImageOrder, 'No reordered gallery image order was captured');

        $this->wait->until(function () use ($galleryId) {
            $sql = new Sql();
            $images = $sql->getRows(
                'SELECT id FROM gallery_images WHERE gallery = ? ORDER BY sequence',
                [$galleryId]
            );
            $sql->disconnect();

            return array_map('intval', array_column($images, 'id')) === $this->reorderedImageOrder;
        });

        $sql = new Sql();
        $images = $sql->getRows(
            'SELECT id FROM gallery_images WHERE gallery = ? ORDER BY sequence',
            [$galleryId]
        );
        $sql->disconnect();

        Assert::assertSame(
            $this->reorderedImageOrder,
            array_map('intval', array_column($images, 'id'))
        );
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
        $this->driver->findElement($input)->sendKeys($file);
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

    private function waitForGalleryTitle($galleryId, $title): void {
        $this->wait->until(function () use ($galleryId, $title) {
            $sql = new Sql();
            $gallery = $sql->getRow('SELECT title FROM galleries WHERE id = ?', [$galleryId]);
            $sql->disconnect();
            return ($gallery['title'] ?? null) === $title;
        });
    }
}
