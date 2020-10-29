<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Comment;
use Exception;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use PHPUnit\Framework\Assert;
use Sql;


class BlogFeatureContext implements Context {

    private $driver;
    private $wait;
    private $baseUrl;
    private $user;

    private $tag = '';

    /**
     * @var Sql
     */
    private $sql;

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
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('990', 'Sample Blog 1', '2031-01-01', '', 0, 1)");
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('991', 'Sample Blog 2', '2031-01-02', '', 0, 1)");
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('992', 'Sample Blog 3', '2031-01-03', '', 0, 1)");
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('993', 'Sample Blog 4', '2031-01-04', '', 0, 1)");
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('994', 'Sample Blog 5', '2031-01-05', '', 0, 1)");
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('995', 'Sample Blog 6', '2031-01-06', '', 0, 1)");
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('996', 'Sample Blog 7', '2031-01-07', '', 0, 1)");
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('997', 'Sample Blog 8', '2031-01-08', '', 0, 1)");
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('998', 'Sample Blog 9', '2031-01-09', '', 0, 1)");
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('999', 'Sample Blog 10', '2031-01-10', '', 0, 1)");
        $this->sql->executeStatement("INSERT INTO `blog_comments` (`id`, `blog`, `user`, `name`, `date`, `ip`, `email`, `comment`) VALUES (998, 999, NULL, 'Anna', '2012-10-31 09:56:47', '68.98.132.164', 'annad@annadbruce.com', 'hehehehehe this rules!')");
        $this->sql->executeStatement("INSERT INTO `blog_comments` (`id`, `blog`, `user`, `name`, `date`, `ip`, `email`, `comment`) VALUES (999, 999, 4, 'Uploader', '2012-10-31 13:56:47', '192.168.1.2', 'msaperst@gmail.com', 'awesome post')");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('997', '1', 'posts/2031/01/01/sample.jpg', 570, 380, 0, 0)");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('997', '1', 'posts/2031/01/01/sample.jpg', 570, 380, 570, 0)");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('997', '1', 'posts/2031/01/01/sample.jpg', 570, 380, 0, 380)");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('997', '1', 'posts/2031/01/01/sample.jpg', 570, 380, 570, 380)");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('999', '1', 'posts/2031/01/01/sample.jpg', 1140, 760, 0, 0)");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('999', '1', 'posts/2031/01/01/sample.jpg', 1140, 760, 0, 760)");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('999', '1', 'posts/2031/01/01/sample.jpg', 1140, 760, 0, 1520)");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('999', '3', 'posts/2031/01/01/sample.jpg', 1140, 760, 0, 0)");
        $this->sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('999', 29)");
        $this->sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('997', 29)");
        $this->sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('997', 30)");
        $this->sql->executeStatement("INSERT INTO `blog_texts` (`blog`, `contentGroup`, `text`) VALUES ('999', '2', 'Some blog text')");
        $this->sql->executeStatement("INSERT INTO `blog_texts` (`blog`, `contentGroup`, `text`) VALUES ('998', '1', 'I adored having this lovebug over to the studio! I photographed her parents maternity session in DC not too long ago and couldn''t wait to meet her. Alina made the most adorable newborn mermaid and slept like an angel. Congrats Nicole and Tom! Just precious :D. I adored having this lovebug over to the studio! I photographed her parents maternity session in DC not too long ago and couldn''t wait to meet her. Alina made the most adorable newborn mermaid and slept like an angel. Congrats Nicole and Tom! Just precious :D. I adored having this lovebug over to the studio! I photographed her parents maternity session in DC not too long ago and couldn''t wait to meet her. Alina made the most adorable newborn mermaid and slept like an angel. Congrats Nicole and Tom! Just precious :D. I adored having this lovebug over to the studio! I photographed her parents maternity session in DC not too long ago and couldn''t wait to meet her. Alina made the most adorable newborn mermaid and slept like an angel. Congrats Nicole and Tom! Just precious :D')");
    }

    /**
     * @AfterScenario
     * @throws Exception
     */
    public function cleanup() {
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 990;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 991;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 992;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 993;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 994;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 995;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 996;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 997;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 997;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 998;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `blog_images` WHERE `blog_images`.`blog` = 997;");
        $this->sql->executeStatement("DELETE FROM `blog_images` WHERE `blog_images`.`blog` = 999;");
        $this->sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = 997;");
        $this->sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = 999;");
        $this->sql->executeStatement("DELETE FROM `blog_texts` WHERE `blog_texts`.`blog` = 998;");
        $this->sql->executeStatement("DELETE FROM `blog_texts` WHERE `blog_texts`.`blog` = 999;");
        $this->sql->executeStatement("DELETE FROM `blog_comments` WHERE `blog_comments`.`blog` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_comments`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `blog_comments` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
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
     * @When /^I scroll to the bottom of the page$/
     */
    public function iScrollToTheBottomOfThePage() {
        $this->driver->executeScript("window.scrollTo(0, document.body.scrollHeight)");
    }

    /**
     * @When /^I try to leave the comment "([^"]*)"$/
     */
    public function iTryToLeaveTheComment($comment) {
        $this->driver->findElement(WebDriverBy::id('post-comment-message'))->sendKeys($comment);
    }

    /**
     * @When /^I leave the comment "([^"]*)"$/
     */
    public function iLeaveTheComment($comment) {
        $this->iTryToLeaveTheComment($comment);
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('post-comment-submit')));
        $this->driver->findElement(WebDriverBy::id('post-comment-submit'))->click();
    }

    /**
     * @When /^I delete the "([^"]*)" comment$/
     */
    public function iDeleteTheComment($ord) {
        $commentHolder = $this->driver->findElement(WebDriverBy::id('post-comments'));
        $blocks = $commentHolder->findElements(WebDriverBy::tagName('blockquote'));
        $commentBlockSize = $blocks[intval($ord) - 1]->getSize();
        $action = new WebDriverActions($this->driver);
        $action->moveToElement($blocks[intval($ord) - 1], intval($commentBlockSize->getWidth() * 0.5 - 5), intval($commentBlockSize->getHeight() * -0.5 + 5))->click()->perform();
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::className('btn-danger')));
        $this->driver->findElement(WebDriverBy::className('btn-danger'))->click();
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('btn-danger'))));
    }

    private function verifyBlogPost($start) {
        $pullPost = $start;
        do {
            $details = $this->sql->getRow("SELECT * FROM blog_details ORDER BY date DESC LIMIT $pullPost,1;");
            $tags = array_column($this->sql->getRows("SELECT `tags`.* FROM `tags` JOIN `blog_tags` ON tags.id = blog_tags.tag WHERE blog_tags.blog = {$details['id']}"), 'id');
            $pullPost++;
        } while ($this->tag != '' && !in_array($this->tag, $tags));
        $tags = join(', ', array_column($this->sql->getRows("SELECT `tags`.* FROM `tags` JOIN `blog_tags` ON tags.id = blog_tags.tag WHERE blog_tags.blog = {$details['id']}"), 'tag'));
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#post-content > div:nth-child(' . ($start * 2 + 1) . ')')));
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
     */
    public function iSeeAllOfTheCategoriesDisplayed() {
        Assert::assertEquals($this->sql->getRowCount("SELECT DISTINCT tag FROM `blog_tags`"), sizeof($this->driver->findElements(WebDriverBy::cssSelector('#tag-cloud > span'))));
    }

    /**
     * @Then /^I see the full blog post (\d+)$/
     * @param $blog
     */
    public function iSeeTheFullBlogPost($blog) {
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
     * @Then /^I see post (\d+)'s comments$/
     * @param $count
     */
    public function iSeeComments($blog) {
        $commentHolder = $this->driver->findElement(WebDriverBy::id('post-comments'));
        $comments = $this->sql->getRows("SELECT * FROM blog_comments WHERE blog = $blog ORDER BY date DESC");
        $blocks = $commentHolder->findElements(WebDriverBy::tagName('blockquote'));
        Assert::assertStringStartsWith(sizeof($comments) . ' Comment', $commentHolder->findElement(WebDriverBy::className('text-left'))->getText());
        Assert::assertEquals(sizeof($blocks), sizeof($comments));
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
     */
    public function iCanNotDeleteComment($ord) {
        $commentHolder = $this->driver->findElement(WebDriverBy::id('post-comments'));
        $blocks = $commentHolder->findElements(WebDriverBy::tagName('blockquote'));
        Assert::assertNotContains("deletable", $blocks[intval($ord) - 1]->getAttribute('class'));
    }

    /**
     * @Then /^the submit comment button is enabled$/
     */
    public function theSubmitCommentButtonIsEnabled() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('post-comment-submit')));
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('post-comment-submit'))->isEnabled());

    }

    /**
     * @Then /^the submit comment button is disabled$/
     */
    public function theSubmitCommentButtonIsDisabled() {
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('post-comment-submit'))));
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('post-comment-submit'))->isEnabled());
    }
}