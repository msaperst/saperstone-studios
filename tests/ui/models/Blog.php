<?php

namespace ui\models;

use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;

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

        // Comment creation is asynchronous. Do not let the action complete
        // until the new comment is visible in the UI; otherwise a following
        // action can operate on the previously first comment.
        $this->wait->until(function () use ($message) {
            foreach ($this->getCommentBlocks() as $block) {
                if ($block->findElement(WebDriverBy::tagName('p'))->getText() === $message) {
                    return true;
                }
            }
            return false;
        });
    }

    public function waitForCommentsToLoad() {
        $header = WebDriverBy::cssSelector('#post-comments h2');
        $this->wait->until(function () use ($header) {
            $headers = $this->driver->findElements($header);
            if (count($headers) !== 1) {
                return false;
            }

            return preg_match('/^\\d+ Comments?$/', trim($headers[0]->getText())) === 1;
        });
    }

    public function deleteComment($ord) {
        $this->waitForCommentsToLoad();
        $index = intval($ord) - 1;

        $this->wait->until(function () use ($index) {
            $blocks = $this->getCommentBlocks();
            return isset($blocks[$index])
                && str_contains((string)$blocks[$index]->getAttribute('class'), 'deletable');
        });

        $blocks = $this->getCommentBlocks();
        $comment = $blocks[$index];
        $this->driver->executeScript(
            "arguments[0].scrollIntoView({block: 'center'});",
            [$comment]
        );

        // The legacy delete affordance is a CSS :after pseudo-element on the
        // blockquote. Click its visible top-right area as a user would.
        $commentBlockSize = $comment->getSize();
        $action = new WebDriverActions($this->driver);
        $action->moveToElement(
            $comment,
            intval($commentBlockSize->getWidth() * 0.5 - 5),
            intval($commentBlockSize->getHeight() * -0.5 + 5)
        )->click()->perform();

        $confirm = WebDriverBy::xpath(
            "//div[contains(@class, 'bootstrap-dialog')][.//div[contains(@class, 'bootstrap-dialog-title')][normalize-space(.)='Are You Sure?']]//button[contains(@class, 'btn-danger')][contains(normalize-space(.), 'Delete')]"
        );
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($confirm));
        $this->driver->findElement($confirm)->click();
        $this->wait->until(WebDriverExpectedCondition::not(
            WebDriverExpectedCondition::presenceOfElementLocated($confirm)
        ));
    }

    public function getCommentHolder(): RemoteWebElement {
        return $this->driver->findElement(WebDriverBy::id('post-comments'));
    }

    public function getCommentBlocks(): array {
        return $this->getCommentHolder()->findElements(WebDriverBy::tagName('blockquote'));
    }
}