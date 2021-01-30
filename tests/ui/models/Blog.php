<?php

namespace ui\models;

use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Sql;

class Blog {
    /**
     * @var RemoteWebDriver
     */
    private $driver;
    /**
     * @var WebDriverWait
     */
    private $wait;

    public function __construct($driver, $wait) {
        $this->driver = $driver;
        $this->wait = $wait;
    }

    public function getBlogId(): int {
        return $this->driver->findElement(WebDriverBy::id('post-comment-submit'))->getAttribute('post-id');
    }

    public function fillOutCommentForm($name, $email, $message) {
        if ($name != NULL) {
            $this->driver->findElement(WebDriverBy::id('post-comment-name'))->clear()->sendkeys($name);
        }
        if ($email != NULL) {
            $this->driver->findElement(WebDriverBy::id('post-comment-email'))->clear()->sendkeys($email);
        }
        $this->driver->findElement(WebDriverBy::id('post-comment-message'))->sendKeys($message);
    }

    public function leaveComment($name, $email, $message) {
        $this->fillOutCommentForm($name, $email, $message);
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('post-comment-submit')));
        $this->driver->findElement(WebDriverBy::id('post-comment-submit'))->click();
    }

    public function waitForPostToLoad($postNum) {
        // using times two due to the extra row for sharing
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#post-content > div:nth-child(' . ($postNum * 2 + 1) . ')')));
    }

    public function waitForPreviewToLoad($lineNum) {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector(".col-gallery > div:nth-child(" . ($lineNum + 1) . ")")));
    }

    public function waitForCommentsToLoad() {
        $sql = new Sql();
        $comments = $sql->getRows("SELECT * FROM blog_comments WHERE blog = {$this->getBlogId()} ORDER BY date DESC");
        $sql->disconnect();
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#post-comments > div:nth-child(' . (sizeof($comments) + 1) . ')')));
    }

    public function deleteComment($ord) {
        $this->waitForCommentsToLoad();
        $blocks = $this->getCommentBlocks();
        $commentBlockSize = $blocks[intval($ord) - 1]->getSize();
        $action = new WebDriverActions($this->driver);
        $action->moveToElement($blocks[intval($ord) - 1], intval($commentBlockSize->getWidth() * 0.5 - 5), intval($commentBlockSize->getHeight() * -0.5 + 5))->click()->perform();
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::className('btn-danger')));
        $this->driver->findElement(WebDriverBy::className('btn-danger'))->click();
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('btn-danger'))));
    }

    public function getCommentHolder(): RemoteWebElement {
        return $this->driver->findElement(WebDriverBy::id('post-comments'));
    }

    public function getCommentBlocks(): array {
        return $this->getCommentHolder()->findElements(WebDriverBy::tagName('blockquote'));
    }
}