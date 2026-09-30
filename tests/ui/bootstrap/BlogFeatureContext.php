<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Comment;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use PHPUnit\Framework\Assert;
use Sql;
use ui\models\Blog;
use User;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Blog.php';

class BlogFeatureContext implements Context {
    /**
     * @var Sql
     */
    private $sql;
    /**
     * @var RemoteWebDriver
     */
    private $driver;
    /**
     * @var WebDriverWait
     */
    private $wait;
    /**
     * @var User
     */
    private $user;
    private $baseUrl;
    private $tag = '';
    private $searchTerm = '';
    private $blogIds = [];
    private $blogFixtures = [];

    /** @BeforeScenario
     * @param BeforeScenarioScope $scope
     * @throws Exception
     */
    public function gatherContexts(BeforeScenarioScope $scope) {
        $environment = $scope->getEnvironment();
        $this->driver = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getDriver();
        $this->user = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getUser();
        $this->wait = new WebDriverWait($this->driver, 10);
        $this->baseUrl = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getBaseUrl();
        $this->sql = new Sql();
    }

    /**
     * @AfterScenario
     * @throws Exception
     */
    public function cleanup() {
        foreach ($this->blogIds as $blogId) {
            $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = $blogId;");
            $this->sql->executeStatement("DELETE FROM `blog_images` WHERE `blog_images`.`blog` = $blogId;");
            $this->sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = $blogId;");
            $this->sql->executeStatement("DELETE FROM `blog_texts` WHERE `blog_texts`.`blog` = $blogId;");
            $this->sql->executeStatement("DELETE FROM `blog_comments` WHERE `blog_comments`.`blog` = $blogId;");
            system("rm -rf " . escapeshellarg(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "content/blog/$blogId"));
        }
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_comments`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `blog_comments` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    /**
     * @Given /^blog (\d+) exists$/
     * @param $blogId
     * @throws Exception
     */
    public function blogExists($blogId) {
        $this->blogIds[] = $blogId;
        $this->blogFixtures[(int) $blogId] = [
            'id' => (int) $blogId,
            'title' => "Sample Blog $blogId",
            'date' => "$blogId-01-01",
            'tags' => [29],
            'tagNames' => ['Tea Ceremony'],
            'images' => 7,
            'texts' => 1,
            'searchText' => 'Some blog text',
        ];
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('$blogId', 'Sample Blog $blogId', '$blogId-01-01', 'posts/$blogId/01/01/sample.jpg', 0, 1)");
        $this->sql->executeStatement("INSERT INTO `blog_comments` (`blog`, `user`, `name`, `date`, `ip`, `email`, `comment`) VALUES ($blogId, NULL, 'Anna', '2012-10-31 09:56:47', '68.98.132.164', 'annad@annadbruce.com', 'hehehehehe this rules!')");
        $this->sql->executeStatement("INSERT INTO `blog_comments` (`blog`, `user`, `name`, `date`, `ip`, `email`, `comment`) VALUES ($blogId, 4, 'Uploader', '2012-10-31 13:56:47', '192.168.1.2', 'msaperst@gmail.com', 'awesome post')");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('$blogId', '1', 'posts/$blogId/01/01/sample.jpg', 570, 380, 0, 0)");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('$blogId', '1', 'posts/$blogId/01/01/sample.jpg', 570, 380, 570, 0)");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('$blogId', '1', 'posts/$blogId/01/01/sample.jpg', 570, 380, 0, 380)");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('$blogId', '1', 'posts/$blogId/01/01/sample.jpg', 570, 380, 570, 380)");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('$blogId', '1', 'posts/$blogId/01/01/sample.jpg', 1140, 760, 0, 760)");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('$blogId', '1', 'posts/$blogId/01/01/sample.jpg', 1140, 760, 0, 1520)");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('$blogId', '3', 'posts/$blogId/01/01/sample.jpg', 1140, 760, 0, 0)");
        $this->sql->executeStatement("INSERT INTO `blog_texts` (`blog`, `contentGroup`, `text`) VALUES ('$blogId', '2', 'Some blog text')");
        $this->sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('$blogId', 29)");
        $oldMask = umask(0);
        if (!is_dir(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "content/blog/$blogId/01/01")) {
            mkdir(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "content/blog/$blogId/01/01", 0777, true);
        }
        copy(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg', dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "content/blog/$blogId/01/01/sample.jpg");
        chmod(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "content/blog/$blogId/01/01/sample.jpg", 0777);
        umask($oldMask);
    }

    /**
     * @Given /^I am on the blog page$/
     */
    public function iAmOnTheBlogPage() {
        $this->driver->get($this->baseUrl . 'blog');
    }

    /**
     * @Given /^I am on the blog posts page$/
     */
    public function iAmOnTheBlogPostsPage() {
        $this->driver->get($this->baseUrl . 'blog/posts.php');
    }

    /**
     * @Given /^I am on the blog categories page$/
     */
    public function iAmOnTheBlogCategoriesPage() {
        $this->driver->get($this->baseUrl . 'blog/categories.php');
    }

    /**
     * @Given /^I am on the blog category (\d+) page$/
     * @param $int
     */
    public function iAmOnTheBlogCategoryPage($int) {
        $this->driver->get($this->baseUrl . "blog/category.php?t=$int");
        $this->tag = $int;
    }

    /**
     * @Given /^I am on the blog search page for "([^"]*)"$/
     */
    public function iAmOnTheBlogSearchPageFor($searchTerm) {
        $this->searchTerm = $searchTerm;
        $this->driver->get($this->baseUrl . 'blog/search.php?s=' . rawurlencode($searchTerm));
    }

    /**
     * @Given /^I have left the comment "([^"]*)" on blog (\d+)$/
     * @param $comment
     * @param $blogId
     * @throws Exception
     */
    public function iHaveLeftTheCommentOnBlog($comment, $blogId) {
        $params = [
            'post' => $blogId,
            'message' => $comment
        ];
        $_SESSION['hash'] = $this->user->getHash();
        $comment = Comment::withParams($params);
        $comment->create();
        unset($_SESSION['hash']);
    }

    /**
     * @When /^I try to leave the comment "([^"]*)"$/
     * @param $comment
     */
    public function iTryToLeaveTheComment($comment) {
        $blog = new Blog($this->driver, $this->wait);
        $blog->fillOutCommentForm(NULL, NULL, $comment);
    }

    /**
     * @When /^I leave the comment "([^"]*)"$/
     * @param $comment
     */
    public function iLeaveTheComment($comment) {
        $blog = new Blog($this->driver, $this->wait);
        $blog->leaveComment(NULL, NULL, $comment);
    }

    /**
     * @When /^I delete the "([^"]*)" comment$/
     * @param $ord
     */
    public function iDeleteTheComment($ord) {
        $blog = new Blog($this->driver, $this->wait);
        $blog->deleteComment($ord);
    }

    private function expectedBlogFixtures(): array {
        $fixtures = array_values($this->blogFixtures);

        $fixtures = array_values(array_filter($fixtures, function (array $fixture): bool {
            if ($this->tag !== '' && !in_array((int) $this->tag, $fixture['tags'], true)) {
                return false;
            }

            if ($this->searchTerm !== '') {
                return stripos($fixture['title'], $this->searchTerm) !== false
                    || stripos($fixture['searchText'], $this->searchTerm) !== false;
            }

            return true;
        }));

        usort($fixtures, static function (array $left, array $right): int {
            $date = strcmp($right['date'], $left['date']);
            return $date !== 0 ? $date : ($right['id'] <=> $left['id']);
        });

        return $fixtures;
    }

    private function verifyBlogPost($start) {
        $fixtures = $this->expectedBlogFixtures();
        Assert::assertArrayHasKey($start, $fixtures, "No blog fixture exists at rendered position $start");

        $this->wait->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('#post-content > div:nth-child(' . ($start + 1) . ')')
            )
        );

        $fixture = $fixtures[$start];
        $allPosts = $this->driver->findElements(WebDriverBy::cssSelector('#post-content > div'));
        $postContent = $allPosts[$start];

        Assert::assertSame($fixture['title'], $postContent->findElement(WebDriverBy::tagName('h2'))->getText());
        Assert::assertSame(
            date('F jS, Y', strtotime($fixture['date'])),
            $postContent->findElement(WebDriverBy::cssSelector('.text-right strong'))->getText()
        );
        Assert::assertSame(
            implode(', ', $fixture['tagNames']),
            $postContent->findElement(WebDriverBy::className('text-left'))->getText()
        );
        Assert::assertCount(1, $postContent->findElements(WebDriverBy::className('blog-share-button')));
        Assert::assertCount($fixture['images'], $postContent->findElements(WebDriverBy::className('post-image')));
        Assert::assertCount($fixture['texts'], $postContent->findElements(WebDriverBy::className('post-text')));
    }

    private function verifyBlogPreview($start) {
        $fixtures = $this->expectedBlogFixtures();
        $offset = $start * 3;
        $expected = array_slice($fixtures, $offset, 3);
        $expectedCount = $offset + count($expected);

        $this->wait->until(function () use ($expectedCount) {
            return count($this->driver->findElements(WebDriverBy::cssSelector('.col-gallery .post'))) >= $expectedCount;
        });

        $renderedTitles = array_map(
            fn($preview) => $preview->findElement(WebDriverBy::className('preview-title'))->getText(),
            $this->driver->findElements(WebDriverBy::cssSelector('.col-gallery .post'))
        );

        foreach ($expected as $fixture) {
            Assert::assertContains(strtoupper($fixture['title']), $renderedTitles);
        }
    }

    /**
     * @Then /^I see the "([^"]*)" blog post load$/
     * @param $ord
     */
    public function iSeeTheNextBlogPostLoad($ord) {
        $start = intval($ord) - 1;
        if ($start > 0) {
            $this->waitForLazyLoadedBlogContent(
                fn() => count($this->driver->findElements(WebDriverBy::cssSelector('#post-content > div'))) > $start
            );
        }
        $this->verifyBlogPost($start);
    }

    /**
     * @Then /^I see the "([^"]*)" blog previews load$/
     * @param $ord
     */
    public function iSeeTheNextBlogPreviewsLoad($ord) {
        $start = intval($ord) - 1;
        if ($start > 0) {
            $expectedCount = ($start + 1) * 3;
            $this->waitForLazyLoadedBlogContent(
                fn() => count($this->driver->findElements(WebDriverBy::cssSelector('.col-gallery .post'))) >= $expectedCount
            );
        }
        $this->verifyBlogPreview($start);
    }

    private function waitForLazyLoadedBlogContent(callable $contentLoaded): void {
        $this->wait->until(function () use ($contentLoaded) {
            if ($contentLoaded()) {
                return true;
            }

            $this->driver->executeScript("window.scrollTo(0, document.body.scrollHeight); window.dispatchEvent(new Event('scroll'));");
            return false;
        });
    }

    /**
     * @Then /^I see all of the categories displayed$/
     * @throws Exception
     */
    public function iSeeAllOfTheCategoriesDisplayed() {
        $category = WebDriverBy::linkText('Tea Ceremony');
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($category));
        Assert::assertTrue($this->driver->findElement($category)->isDisplayed());
    }

    /**
     * @Then /^I see the full blog post$/
     */
    public function iSeeTheFullBlogPost() {
        Assert::assertSame('Sample Blog 2039', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        Assert::assertSame(
            'January 1st, 2039',
            $this->driver->findElement(WebDriverBy::cssSelector('#post-content .text-right strong'))->getText()
        );
        Assert::assertSame(
            'Tea Ceremony',
            $this->driver->findElement(WebDriverBy::cssSelector('#post-content .text-left'))->getText()
        );
        Assert::assertCount(1, $this->driver->findElements(WebDriverBy::className('blog-share-button')));
        Assert::assertCount(7, $this->driver->findElements(WebDriverBy::className('post-image')));
        Assert::assertCount(1, $this->driver->findElements(WebDriverBy::className('post-text')));
    }

    /**
     * @Then /^I see the blog post's comments$/
     */
    public function iSeeComments() {
        $blog = new Blog($this->driver, $this->wait);
        $blog->waitForCommentsToLoad();
        $commentHolder = $blog->getCommentHolder();
        $blocks = $blog->getCommentBlocks();

        Assert::assertNotEmpty($blocks);
        Assert::assertStringStartsWith(
            count($blocks) . ' Comment',
            $commentHolder->findElement(WebDriverBy::className('text-left'))->getText()
        );
        foreach ($blocks as $block) {
            Assert::assertNotSame('', trim($block->findElement(WebDriverBy::tagName('p'))->getText()));
            Assert::assertNotSame('', trim($block->findElement(WebDriverBy::tagName('footer'))->getText()));
        }
    }

    /**
     * @Then /^I see the comment "([^"]*)"$/
     */
    public function iSeeTheComment($comment): void {
        $blog = new Blog($this->driver, $this->wait);
        $blog->waitForCommentsToLoad();

        $this->wait->until(function () use ($blog, $comment) {
            $comments = array_map(
                fn($block) => $block->findElement(WebDriverBy::tagName('p'))->getText(),
                $blog->getCommentBlocks()
            );
            return in_array($comment, $comments, true);
        });

        $comments = array_map(
            fn($block) => $block->findElement(WebDriverBy::tagName('p'))->getText(),
            $blog->getCommentBlocks()
        );
        Assert::assertContains($comment, $comments);
    }

    /**
     * @Then /^I do not see the comment "([^"]*)"$/
     */
    public function iDoNotSeeTheComment($comment): void {
        $blog = new Blog($this->driver, $this->wait);
        $blog->waitForCommentsToLoad();

        $this->wait->until(function () use ($blog, $comment) {
            $comments = array_map(
                fn($block) => $block->findElement(WebDriverBy::tagName('p'))->getText(),
                $blog->getCommentBlocks()
            );
            return !in_array($comment, $comments, true);
        });

        $comments = array_map(
            fn($block) => $block->findElement(WebDriverBy::tagName('p'))->getText(),
            $blog->getCommentBlocks()
        );
        Assert::assertNotContains($comment, $comments);
    }

    /**
     * @Then /^I can not delete the "([^"]*)" comment$/
     * @param $ord
     */
    public function iCanNotDeleteComment($ord) {
        $commentHolder = $this->driver->findElement(WebDriverBy::id('post-comments'));
        $blocks = $commentHolder->findElements(WebDriverBy::tagName('blockquote'));
        Assert::assertStringNotContainsString("deletable", (string) $blocks[intval($ord) - 1]->getAttribute('class'));
    }

    /**
     * @Then /^the submit comment button is enabled$/
     * @throws Exception
     */
    public function theSubmitCommentButtonIsEnabled() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('post-comment-submit')));
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('post-comment-submit'))->isEnabled());
    }

    /**
     * @Then /^the submit comment button is disabled$/
     * @throws Exception
     */
    public function theSubmitCommentButtonIsDisabled() {
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('post-comment-submit'))));
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('post-comment-submit'))->isEnabled());
    }
}