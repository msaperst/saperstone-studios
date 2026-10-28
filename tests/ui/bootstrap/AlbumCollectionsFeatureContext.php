<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\Interactions\WebDriverActions;
use PHPUnit\Framework\Assert;
use Sql;

require_once dirname(__DIR__, 3) . '/src/autoloader.php';

/** Focused real-browser contract for parent/child album navigation and admin. */
class AlbumCollectionsFeatureContext implements Context {
    private const COLLECTION_ID = 98875;

    private $driver;
    private WebDriverWait $wait;
    private ?string $collectionName = null;

    /** @BeforeScenario */
    public function setup(BeforeScenarioScope $scope): void {
        $driver = $scope->getEnvironment()->getContext(BaseFeatureContext::class)->getDriver();
        $this->driver = $driver;
        $this->wait = new WebDriverWait($driver, 12);
        $this->collectionName = null;
    }

    /** @AfterScenario */
    public function cleanup(): void {
        if ($this->collectionName === null) {
            return;
        }
        $sql = new Sql();
        try {
            $sql->executeStatement('DELETE FROM albums_for_users WHERE album = ?', [self::COLLECTION_ID]);
            $sql->executeStatement('DELETE FROM albums WHERE id = ?', [self::COLLECTION_ID]);
        } finally {
            $sql->disconnect();
        }
    }

    /** @Given /^a collection named "([^"]+)" exists$/ */
    public function collectionExists(string $name): void {
        $this->createCollection($name, null);
    }

    /** @Given /^a collection named "([^"]+)" exists with code "([^"]+)"$/ */
    public function codedCollectionExists(string $name, string $code): void {
        $this->createCollection($name, $code);
    }

    private function createCollection(string $name, ?string $code): void {
        $sql = new Sql();
        try {
            $sql->executeStatement('DELETE FROM albums_for_users WHERE album = ?', [self::COLLECTION_ID]);
            $sql->executeStatement('DELETE FROM albums WHERE id = ?', [self::COLLECTION_ID]);
            $sql->executeStatement(
                'INSERT INTO albums (id, name, description, location, owner, code, isCollection) ' .
                'VALUES (?, ?, ?, ?, ?, ?, 1)',
                [self::COLLECTION_ID, $name, 'Sample collection', '', 1, $code]
            );
            $this->collectionName = $name;
        } finally {
            $sql->disconnect();
        }
    }

    /** @Given /^album (\d+) belongs to that collection$/ */
    public function albumBelongsToCollection(int $id): void {
        Assert::assertNotNull($this->collectionName);
        $sql = new Sql();
        try {
            $next = $sql->getRow(
                'SELECT COALESCE(MAX(collectionOrder), -1) + 1 AS n FROM albums WHERE parent = ?',
                [self::COLLECTION_ID]
            );
            $sql->executeStatement(
                'UPDATE albums SET parent = ?, collectionOrder = ? WHERE id = ?',
                [self::COLLECTION_ID, (int)$next['n'], $id]
            );
        } finally {
            $sql->disconnect();
        }
    }

    /** @Then /^I see the "([^"]+)" collection landing page$/ */
    public function collectionPage(string $name): void {
        $this->wait->until(WebDriverExpectedCondition::urlContains(
            'collection.php?collection=' . self::COLLECTION_ID
        ));
        Assert::assertStringContainsString(
            $name, $this->driver->findElement(WebDriverBy::cssSelector('h1.page-header'))->getText()
        );
        Assert::assertNotEmpty($this->driver->findElements(WebDriverBy::cssSelector('ol.breadcrumb')));
    }

    /** @Then /^I see "([^"]+)" as a child gallery$/ */
    public function seeChild(string $name): void {
        $label = $this->driver->findElement(
            WebDriverBy::cssSelector('[data-child-album-id="99999"] h3')
        );
        Assert::assertSame($name, $label->getText());
    }

    /** @When /^I open the "([^"]+)" child gallery$/ */
    public function openChild(string $name): void {
        $this->driver->findElement(
            WebDriverBy::cssSelector('[data-child-album-id="99999"] a.album-collection-link')
        )->click();
        $this->wait->until(WebDriverExpectedCondition::urlContains('album.php?album=99999'));
    }

    /** @Then /^the "([^"]+)" breadcrumb links back to the collection$/ */
    public function parentBreadcrumb(string $name): void {
        $link = $this->driver->findElement(
            WebDriverBy::cssSelector('ol.breadcrumb a[href="collection.php?collection=' . self::COLLECTION_ID . '"]')
        );
        Assert::assertSame($name, $link->getText());
    }

    /** @When /^I follow the "([^"]+)" breadcrumb$/ */
    public function followBreadcrumb(string $name): void {
        $this->parentBreadcrumb($name);
        $this->driver->findElement(
            WebDriverBy::cssSelector('ol.breadcrumb a[href="collection.php?collection=' . self::COLLECTION_ID . '"]')
        )->click();
    }

    /** @Then /^I can open album (\d+)$/ */
    public function albumIsOpen(int $id): void {
        $this->wait->until(WebDriverExpectedCondition::urlContains('album.php?album=' . $id));
        Assert::assertNotEmpty($this->driver->findElements(WebDriverBy::id('album-grid')));
    }

    /** @Then /^I do not see a parent collection breadcrumb$/ */
    public function noParentBreadcrumb(): void {
        Assert::assertCount(0, $this->driver->findElements(
            WebDriverBy::cssSelector('ol.breadcrumb a[href*="collection.php"]')
        ));
    }

    /** @When /^I expand the "([^"]+)" collection$/ */
    public function expandCollection(string $name): void {
        Assert::assertSame($this->collectionName, $name);
        $button = WebDriverBy::cssSelector('tr[album-id="' . self::COLLECTION_ID . '"] .collection-expand-btn');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($button));
        $this->driver->findElement($button)->click();
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('.collection-child-item')
        ));
    }

    /** @When /^I drag gallery (\d+) above gallery (\d+)$/ */
    public function dragAbove(int $moving, int $target): void {
        $drag = $this->driver->findElement(
            WebDriverBy::cssSelector('.collection-child-item[data-child-id="' . $moving . '"] .collection-drag-handle')
        );
        $drop = $this->driver->findElement(
            WebDriverBy::cssSelector('.collection-child-item[data-child-id="' . $target . '"]')
        );
        (new WebDriverActions($this->driver))
            ->clickAndHold($drag)->moveToElement($drop)->moveByOffset(0, -25)->release()->perform();
        $this->wait->until(function () use ($moving) {
            $sql = new Sql();
            try {
                $row = $sql->getRow('SELECT collectionOrder FROM albums WHERE id = ?', [$moving]);
                return $row !== null && (int)$row['collectionOrder'] === 0;
            } finally {
                $sql->disconnect();
            }
        });
    }

    /** @Then /^gallery (\d+) is listed before gallery (\d+)$/ */
    public function listedBefore(int $first, int $second): void {
        $this->expandCollection((string)$this->collectionName);
        $order = $this->driver->executeScript(<<<'JS'
            return Array.from(document.querySelectorAll('.collection-child-item'))
                .map(item => Number(item.getAttribute('data-child-id')));
            JS);
        Assert::assertLessThan(
            array_search($second, $order, true),
            array_search($first, $order, true)
        );
    }

    /** @When /^I delete the "([^"]+)" collection$/ */
    public function startDelete(string $name): void {
        Assert::assertSame($this->collectionName, $name);
        $edit = WebDriverBy::cssSelector('tr[album-id="' . self::COLLECTION_ID . '"] .edit-album-btn');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($edit));
        $this->driver->findElement($edit)->click();
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated(
            WebDriverBy::cssSelector('.bootstrap-dialog.modal.in')
        ));
        $this->lastDialogButton('Delete Collection')->click();
    }

    /** @Then /^the confirmation says child albums will be preserved$/ */
    public function deletionExplained(): void {
        $this->wait->until(function () {
            return count($this->driver->findElements(
                WebDriverBy::cssSelector('.bootstrap-dialog.modal.in')
            )) >= 2;
        });
        Assert::assertStringContainsString(
            'remain as standalone albums',
            $this->lastDialog()->getText()
        );
        Assert::assertStringContainsString('inherited access', $this->lastDialog()->getText());
    }

    /** @When /^I confirm deleting the "([^"]+)" collection$/ */
    public function confirmDelete(string $name): void {
        Assert::assertSame($this->collectionName, $name);
        $this->lastDialogButton('Delete Collection')->click();
        $this->wait->until(function () {
            $sql = new Sql();
            try {
                return $sql->getRow('SELECT id FROM albums WHERE id = ?', [self::COLLECTION_ID]) === null;
            } finally {
                $sql->disconnect();
            }
        });
    }

    /** @Then /^album (\d+) is still listed as a standalone album$/ */
    public function albumPreserved(int $id): void {
        $sql = new Sql();
        try {
            $album = $sql->getRow('SELECT parent FROM albums WHERE id = ?', [$id]);
            Assert::assertNotNull($album);
            Assert::assertNull($album['parent']);
        } finally {
            $sql->disconnect();
        }
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('tr[album-id="' . $id . '"]')
        ));
    }

    /** @Then /^child album (\d+) shows "([^"]+)" thumbnail status$/ */
    public function childThumbnailStatus(int $id, string $expected): void {
        $locator = WebDriverBy::cssSelector(
            '.collection-child-item[data-child-id="' . $id . '"] .collection-thumbnail-status'
        );
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($locator));
        Assert::assertSame($expected, trim($this->driver->findElement($locator)->getText()));
    }

    /** @Then /^the album editor action buttons fit on one row$/ */
    public function albumEditorButtonsStayOnOneRow(): void {
        $dialog = $this->lastDialog();
        $buttons = $dialog->findElements(WebDriverBy::cssSelector('.bootstrap-dialog-footer-buttons button'));
        Assert::assertGreaterThanOrEqual(5, count($buttons));
        $previousY = null;
        foreach ($buttons as $button) {
            Assert::assertTrue($button->isDisplayed());
            $location = $button->getLocation();
            if ($previousY !== null) {
                Assert::assertEqualsWithDelta(
                    $previousY,
                    $location->getY(),
                    3,
                    'Album editor action buttons wrap to a second row'
                );
            }
            $previousY = $location->getY();
        }
    }

    /** @Given /^album (\d+) has a protected thumbnail for image (\d+)$/ */
    public function protectedAlbumThumbnail(int $albumId, int $imageNumber): void {
        $sql = new Sql();
        try {
            $album = $sql->getRow('SELECT location FROM albums WHERE id = ?', [$albumId]);
            Assert::assertNotNull($album);
            $image = $sql->getRow(
                'SELECT location FROM album_images WHERE album = ? ORDER BY sequence, id LIMIT ?, 1',
                [$albumId, $imageNumber - 1]
            );
            Assert::assertNotNull($image);
            $folder = dirname(__DIR__, 3) . '/content/albums/' . $album['location'];
            $source = $folder . '/' . basename($image['location']);
            $directory = $folder . '/thumbs/400';
            Assert::assertFileExists($source);
            if (!is_dir($directory)) {
                Assert::assertTrue(mkdir($directory, 0775, true));
            }
            // A deterministic valid 1px derivative exercises the HTTP
            // image-loading path without running ImageMagick during Behat.
            Assert::assertNotFalse(file_put_contents(
                $directory . '/' . basename($image['location']),
                base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==')
            ));
            $sql->executeStatement('UPDATE albums SET thumbsCreated = 1 WHERE id = ?', [$albumId]);
        } finally {
            $sql->disconnect();
        }
    }

    /** @When /^I open the album cover picker$/ */
    public function openAlbumCoverPicker(): void {
        $this->lastDialogButton('Choose Cover')->click();
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated(
            WebDriverBy::cssSelector('.album-cover-option')
        ));
    }

    /** @Then /^I can see a loaded protected cover preview$/ */
    public function coverPreviewLoads(): void {
        $loaded = $this->driver->executeAsyncScript(<<<'JS'
            const done = arguments[arguments.length - 1];
            const cover = document.querySelector('.album-cover-option');
            if (!cover) {
                done(false);
                return;
            }
            const background = getComputedStyle(cover).backgroundImage;
            const match = background.match(/^url\(["']?(.*?)["']?\)$/);
            if (!match || !match[1].includes('/api/get-album-cover.php?')) {
                done(false);
                return;
            }
            const image = new Image();
            image.onload = () => done(image.naturalWidth > 0 && image.naturalHeight > 0);
            image.onerror = () => done(false);
            image.src = match[1];
            JS);
        Assert::assertTrue($loaded, 'Protected cover preview failed to load valid image bytes');
    }

    /** @When /^I select the first cover preview$/ */
    public function selectFirstCoverPreview(): void {
        $this->driver->findElement(WebDriverBy::cssSelector('.album-cover-option'))->click();
    }

    /** @Then /^album (\d+) uses the selected cover image$/ */
    public function selectedCoverPersists(int $albumId): void {
        $this->wait->until(function () use ($albumId) {
            $sql = new Sql();
            try {
                $album = $sql->getRow('SELECT coverImage FROM albums WHERE id = ?', [$albumId]);
                $first = $sql->getRow(
                    'SELECT id FROM album_images WHERE album = ? ORDER BY sequence, id LIMIT 1',
                    [$albumId]
                );
                return $album !== null && $first !== null &&
                    (int)$album['coverImage'] === (int)$first['id'];
            } finally {
                $sql->disconnect();
            }
        });
    }

    private function lastDialog() {
        $dialogs = $this->driver->findElements(WebDriverBy::cssSelector('.bootstrap-dialog.modal.in'));
        Assert::assertNotEmpty($dialogs);
        return end($dialogs);
    }

    private function lastDialogButton(string $label) {
        $dialog = $this->lastDialog();
        $buttons = $dialog->findElements(WebDriverBy::cssSelector('.bootstrap-dialog-footer-buttons button'));
        foreach ($buttons as $button) {
            if (trim($button->getText()) === $label) {
                return $button;
            }
        }
        Assert::fail("No '$label' button in the current dialog");
    }
}
