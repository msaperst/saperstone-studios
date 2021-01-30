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
    private $blogIds = [];

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
            system("rm -rf " . escapeshellarg(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "content/blog/$blogId"));
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
        if (!is_dir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "content/blog/$blogId/01/01")) {
            mkdir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "content/blog/$blogId/01/01", 0777, true);
        }
        copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "content/blog/$blogId/01/01/sample.jpg");
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "content/blog/$blogId/01/01/sample.jpg", 0777);
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

    private function verifyBlogPost($start) {
        $blog = new Blog($this->driver, $this->wait);
        $blog->waitForPostToLoad($start);
        $pullPost = $start;
        do {
            $details = $this->sql->getRow("SELECT * FROM blog_details ORDER BY date DESC LIMIT $pullPost,1;");
            $tags = array_column($this->sql->getRows("SELECT `tags`.* FROM `tags` JOIN `blog_tags` ON tags.id = blog_tags.tag WHERE blog_tags.blog = {$details['id']}"), 'id');
            $pullPost++;
        } while ($this->tag != '' && !in_array($this->tag, $tags));
        $tags = join(', ', array_column($this->sql->getRows("SELECT `tags`.* FROM `tags` JOIN `blog_tags` ON tags.id = blog_tags.tag WHERE blog_tags.blog = {$details['id']}"), 'tag'));
        $allPosts = $this->driver->findElements(WebDriverBy::cssSelector('#post-content > div'));
        $postContent = $allPosts[$start * 2];    // using times two due to the extra row for sharing
        Assert::assertEquals($details['title'], $postContent->findElement(WebDriverBy::tagName('h2'))->getText());
        Assert::assertEquals(date('F jS, Y', strtotime($details['date'])), $postContent->findElement(WebDriverBy::cssSelector('.text-center strong'))->getText());
        Assert::assertEquals($tags, $postContent->findElement(WebDriverBy::className('text-left'))->getText());
        Assert::assertEquals('Like', $postContent->findElement(WebDriverBy::className('text-right'))->getText());
        Assert::assertEquals($this->sql->getRowCount("SELECT * FROM blog_images WHERE blog = {$details['id']}"), sizeof($postContent->findElements(WebDriverBy::className('post-image'))));
        Assert::assertEquals($this->sql->getRowCount("SELECT * FROM blog_texts WHERE blog = {$details['id']}"), sizeof($postContent->findElements(WebDriverBy::className('post-text'))));
    }

    private function verifyBlogPreview($start) {
        $blog = new Blog($this->driver, $this->wait);
        $blog->waitForPreviewToLoad($start);
        $s = $start * 3;
        $details = $this->sql->getRows("SELECT * FROM blog_details ORDER BY date DESC LIMIT $s,3;");
        for ($i = 0; $i < 3; $i++) {
            $allPreviews = $this->driver->findElements(WebDriverBy::cssSelector("#post-$i > div"));
            $preview = $allPreviews[$start];
            Assert::assertEquals(strtoupper($details[$i]['title']), $preview->findElement(WebDriverBy::tagName('span'))->getText());
        }
    }

    /**
     * @Then /^I see the "([^"]*)" blog post load$/
     * @param $ord
     */
    public function iSeeTheNextBlogPostLoad($ord) {
        $this->verifyBlogPost(intval($ord) - 1);
    }

    /**
     * @Then /^I see the "([^"]*)" blog previews load$/
     * @param $ord
     */
    public function iSeeTheNextBlogPreviewsLoad($ord) {
        $this->verifyBlogPreview(intval($ord) - 1);
    }

    /**
     * @Then /^I see all of the categories displayed$/
     * @throws Exception
     */
    public function iSeeAllOfTheCategoriesDisplayed() {
        $count = $this->sql->getRowCount("SELECT DISTINCT tag FROM `blog_tags`");
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector("#tag-cloud > span:nth-child($count)")));
        Assert::assertEquals($count, sizeof($this->driver->findElements(WebDriverBy::cssSelector('#tag-cloud > span'))));
    }

    /**
     * @Then /^I see the full blog post$/
     */
    public function iSeeTheFullBlogPost() {
        $blog = $this->driver->findElement(WebDriverBy::id('post-comment-submit'))->getAttribute('post-id');
        $details = $this->sql->getRow("SELECT * FROM blog_details WHERE id = $blog;");
        $tags = join(', ', array_column($this->sql->getRows("SELECT `tags`.* FROM `tags` JOIN `blog_tags` ON tags.id = blog_tags.tag WHERE blog_tags.blog = {$details['id']}"), 'tag'));
        Assert::assertEquals($details['title'], $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        Assert::assertEquals(date('F jS, Y', strtotime($details['date'])), $this->driver->findElement(WebDriverBy::cssSelector('.text-center strong'))->getText());
        Assert::assertEquals($tags, $this->driver->findElement(WebDriverBy::className('text-left'))->getText());
        Assert::assertEquals('Like', $this->driver->findElement(WebDriverBy::className('text-right'))->getText());
        Assert::assertEquals($this->sql->getRowCount("SELECT * FROM blog_images WHERE blog = {$details['id']}"), sizeof($this->driver->findElements(WebDriverBy::className('post-image'))));
        Assert::assertEquals($this->sql->getRowCount("SELECT * FROM blog_texts WHERE blog = {$details['id']}"), sizeof($this->driver->findElements(WebDriverBy::className('post-text'))));
    }

    /**
     * @Then /^I see the blog post's comments$/
     */
    public function iSeeComments() {
        $blog = new Blog($this->driver, $this->wait);
        $blog->waitForCommentsToLoad();
        $commentHolder = $blog->getCommentHolder();
        $blocks = $blog->getCommentBlocks();
        $comments = $this->sql->getRows("SELECT * FROM blog_comments WHERE blog = {$blog->getBlogId()} ORDER BY date DESC");
        Assert::assertStringStartsWith(sizeof($comments) . ' Comment', $commentHolder->findElement(WebDriverBy::className('text-left'))->getText());
        Assert::assertEquals(sizeof($comments), sizeof($blocks));
        for ($i = 0; $i < sizeof($blocks); $i++) {
            $block = $blocks[$i];
            Assert::assertEquals($comments[$i]['comment'], $block->findElement(WebDriverBy::tagName('p'))->getText());
            $text = $block->findElement(WebDriverBy::tagName('footer'))->getText();
            $parts = explode('
', $text);
            if (sizeof($parts) == 1) {
                array_unshift($parts, '');
            }
            Assert::assertEquals($comments[$i]['name'], $parts[0]);
            Assert::assertEquals($comments[$i]['date'], $parts[1]);
        }
    }

    /**
     * @Then /^I can not delete the "([^"]*)" comment$/
     * @param $ord
     */
    public function iCanNotDeleteComment($ord) {
        $commentHolder = $this->driver->findElement(WebDriverBy::id('post-comments'));
        $blocks = $commentHolder->findElements(WebDriverBy::tagName('blockquote'));
        Assert::assertNotContains("deletable", $blocks[intval($ord) - 1]->getAttribute('class'));
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