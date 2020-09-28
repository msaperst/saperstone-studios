<?php

namespace coverage\integration;

use Blog;
use Exception;
use PHPUnit\Framework\TestCase;
use SocialMedia;
use Sql;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class BlogIntegrationTest extends TestCase {
    private $sql;

    public function setUp() {
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('898', 'Sample Blog', '2031-01-01', '', 0)");
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('899', 'Sample Blog', '2031-01-01', '/some/img', 0)");
        $this->sql->executeStatement("INSERT INTO `blog_comments` (`id`, `blog`, `user`, `name`, `date`, `ip`, `email`, `comment`) VALUES (898, 899, NULL, 'Anna', '2012-10-31 09:56:47', '68.98.132.164', 'annad@annadbruce.com', 'hehehehehe this rules!')");
        $this->sql->executeStatement("INSERT INTO `blog_comments` (`id`, `blog`, `user`, `name`, `date`, `ip`, `email`, `comment`) VALUES (899, 899, 4, 'Uploader', '2012-10-31 13:56:47', '192.168.1.2', 'msaperst@gmail.com', 'awesome post')");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('899', '1', 'posts/2031/01/01/sample.jpg', 300, 400, 0, 0)");
        $this->sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('899', 29)");
        $this->sql->executeStatement("INSERT INTO `blog_texts` (`blog`, `contentGroup`, `text`) VALUES ('899', '2', 'Some blog text')");
        $oldmask = umask(0);
        mkdir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/blog/posts/2031/01/01', 0777, true);
        touch(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/blog/posts/2031/01/01/sample.jpg');
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/blog/posts/2031/01/01/sample.jpg', 0777);
        mkdir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/tmp', 0777, true);
        copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/tmp/sample.jpg');
        copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/tmp/sample1.jpg');
        copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/tmp/sample2.jpg');
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/tmp/sample.jpg', 0777);
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/tmp/sample1.jpg', 0777);
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/tmp/sample2.jpg', 0777);
        umask($oldmask);
    }

    public function tearDown() {
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 898;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 899;");
        $this->sql->executeStatement("DELETE FROM `blog_images` WHERE `blog_images`.`blog` = 899;");
        $this->sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = 899;");
        $this->sql->executeStatement("DELETE FROM `blog_texts` WHERE `blog_texts`.`blog` = 899;");
        $this->sql->executeStatement("DELETE FROM `blog_comments` WHERE `blog_comments`.`blog` = 899;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_comments`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `blog_comments` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
        system("rm -rf " . escapeshellarg(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/blog/posts'));
        system("rm -rf " . escapeshellarg(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/tmp'));
    }

    public function testNullBlogId() {
        try {
            Blog::withId(NULL);
        } catch (Exception $e) {
            $this->assertEquals("Blog id is required", $e->getMessage());
        }
    }

    public function testBlankBlogId() {
        try {
            Blog::withId("");
        } catch (Exception $e) {
            $this->assertEquals("Blog id can not be blank", $e->getMessage());
        }
    }

    public function testLetterBlogId() {
        try {
            Blog::withId("a");
        } catch (Exception $e) {
            $this->assertEquals("Blog id does not match any blog posts", $e->getMessage());
        }
    }

    public function testBadBlogId() {
        try {
            Blog::withId(8999);
        } catch (Exception $e) {
            $this->assertEquals("Blog id does not match any blog posts", $e->getMessage());
        }
    }

    public function testBadStringBlogId() {
        try {
            Blog::withId("8999");
        } catch (Exception $e) {
            $this->assertEquals("Blog id does not match any blog posts", $e->getMessage());
        }
    }

    public function testGetId() {
        $blog = Blog::withId('899');
        $this->assertEquals(899, $blog->getId());
    }

    public function testGetTitle() {
        $blog = Blog::withId('899');
        $this->assertEquals('Sample Blog', $blog->getTitle());
    }

    public function testGetDate() {
        $blog = Blog::withId('899');
        $this->assertEquals('January 1st, 2031', $blog->getDate());
    }

    public function testGetPreview() {
        $blog = Blog::withId('899');
        $this->assertEquals('/some/img', $blog->getPreview());
    }

    public function testGetOffset() {
        $blog = Blog::withId('899');
        $this->assertEquals(0, $blog->getOffset());
    }

    public function testGetTags() {
        $blog = Blog::withId('899');
        $tags = [
            0 => [
                'id' => '29',
                'tag' => 'Tea Ceremony'
            ]
        ];
        $this->assertEquals($tags, $blog->getTags());
    }

    public function testGetLocation() {
        $blog = Blog::withId('899');
        $this->assertEquals('/some', $blog->getLocation());
    }

    public function testGetTwitter() {
        $blog = Blog::withId('899');
        $this->assertEquals(0, $blog->getTwitter());
    }

    public function testGetImages() {
        $blog = Blog::withId('899');
        $this->assertEquals(['posts/2031/01/01/sample.jpg'], $blog->getImages());
    }

    public function testIsActiveFalse() {
        $blog = Blog::withId('899');
        $this->assertFalse($blog->isActive());
    }

    public function testisActiveTrue() {
        $this->sql->executeStatement("UPDATE blog_details SET active = 1 WHERE id = 899;");
        $blog = Blog::withId('899');
        $this->assertTrue($blog->isActive());
    }

    public function testAllDataLoadedMinimal() {
        date_default_timezone_set("America/New_York");
        $blog = Blog::withId(898);
        $blogInfo = $blog->getDataArray();
        $this->assertEquals(898, $blogInfo['id']);
        $this->assertEquals('Sample Blog', $blogInfo['title']);
        $this->assertNull($blogInfo['safe_title']);
        $this->assertEquals('January 1st, 2031', $blogInfo['date']);
        $this->assertEquals('', $blogInfo['preview']);
        $this->assertEquals('0', $blogInfo['offset']);
        $this->assertEquals('0', $blogInfo['active']);
        $this->assertEquals(0, $blogInfo['twitter']);
        $this->assertEquals(array(), $blogInfo['tags']);
        $this->assertEquals(array(), $blogInfo['comments']);
        $this->assertEquals(array(), $blogInfo['content']);
    }

    public function testAllDataLoaded() {
        date_default_timezone_set("America/New_York");
        $blog = Blog::withId(899);
        $blogInfo = $blog->getDataArray();
        $this->assertEquals(899, $blogInfo['id']);
        $this->assertEquals('Sample Blog', $blogInfo['title']);
        $this->assertNull($blogInfo['safe_title']);
        $this->assertEquals('January 1st, 2031', $blogInfo['date']);
        $this->assertEquals('/some/img', $blogInfo['preview']);
        $this->assertEquals('0', $blogInfo['offset']);
        $this->assertEquals('0', $blogInfo['active']);
        $this->assertEquals(0, $blogInfo['twitter']);
        $this->assertEquals(2, sizeOf($blogInfo['content']));
        $this->assertEquals(1, sizeOf($blogInfo['content'][1]));
        $this->assertEquals(899, $blogInfo['content'][1][0]['blog']);
        $this->assertEquals(1, $blogInfo['content'][1][0]['contentGroup']);
        $this->assertEquals('posts/2031/01/01/sample.jpg', $blogInfo['content'][1][0]['location']);
        $this->assertEquals(300, $blogInfo['content'][1][0]['width']);
        $this->assertEquals(400, $blogInfo['content'][1][0]['height']);
        $this->assertEquals(0, $blogInfo['content'][1][0]['left']);
        $this->assertEquals(0, $blogInfo['content'][1][0]['top']);
        $this->assertEquals(1, sizeOf($blogInfo['content'][2]));
        $this->assertEquals(899, $blogInfo['content'][2][0]['blog']);
        $this->assertEquals(2, $blogInfo['content'][2][0]['contentGroup']);
        $this->assertEquals('Some blog text', $blogInfo['content'][2][0]['text']);
        $this->assertEquals(1, sizeOf($blogInfo['tags']));
        $this->assertEquals(29, $blogInfo['tags'][0]['id']);
        $this->assertEquals('Tea Ceremony', $blogInfo['tags'][0]['tag']);
        $this->assertEquals(2, sizeOf($blogInfo['comments']));
        $this->assertEquals(899, $blogInfo['comments'][0]['id']);
        $this->assertEquals(899, $blogInfo['comments'][0]['blog']);
        $this->assertEquals('awesome post', $blogInfo['comments'][0]['comment']);
        $this->assertEquals('2012-10-31 13:56:47', $blogInfo['comments'][0]['date']);
        $this->assertEquals('msaperst@gmail.com', $blogInfo['comments'][0]['email']);
        $this->assertEquals('192.168.1.2', $blogInfo['comments'][0]['ip']);
        $this->assertEquals('Uploader', $blogInfo['comments'][0]['name']);
        $this->assertEquals(4, $blogInfo['comments'][0]['user']);
        $this->assertEquals(898, $blogInfo['comments'][1]['id']);
        $this->assertEquals(899, $blogInfo['comments'][1]['blog']);
        $this->assertEquals('hehehehehe this rules!', $blogInfo['comments'][1]['comment']);
        $this->assertEquals('2012-10-31 09:56:47', $blogInfo['comments'][1]['date']);
        $this->assertEquals('annad@annadbruce.com', $blogInfo['comments'][1]['email']);
        $this->assertEquals('68.98.132.164', $blogInfo['comments'][1]['ip']);
        $this->assertEquals('Anna', $blogInfo['comments'][1]['name']);
        $this->assertNull($blogInfo['comments'][1]['user']);
    }

    public function testWithParamsNullParams() {
        try {
            Blog::withParams(NULL);
        } catch (Exception $e) {
            $this->assertEquals('Blog title is required', $e->getMessage());
        }
    }

    public function testWithParamsNoTitle() {
        try {
            Blog::withParams(array());
        } catch (Exception $e) {
            $this->assertEquals('Blog title is required', $e->getMessage());
        }
    }

    public function testWithParamsBlankTitle() {
        $params = [
            'title' => ''
        ];
        try {
            Blog::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog title can not be blank', $e->getMessage());
        }
    }

    public function testWithParamsNoDate() {
        $params = [
            'title' => 'Some Album'
        ];
        try {
            Blog::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog date is required', $e->getMessage());
        }
    }

    public function testWithParamsBlankDate() {
        $params = [
            'title' => 'Some Album',
            'date' => ''
        ];
        try {
            Blog::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog date can not be blank', $e->getMessage());
        }
    }

    public function testWithParamsBadDate() {
        $params = [
            'title' => 'Sample Album',
            'date' => 'some date'
        ];
        try {
            Blog::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog date is not the correct format', $e->getMessage());
        }
    }

    public function testWithParamsNoPreviewImage() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01'
        ];
        try {
            Blog::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog preview image is required', $e->getMessage());
        }
    }

    public function testWithParamsEmptyPreviewImage() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => ''
        ];
        try {
            Blog::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog preview image is required', $e->getMessage());
        }
    }

    public function testWithParamsArrayPreviewImage() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => array()
        ];
        try {
            Blog::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog preview image is required', $e->getMessage());
        }
    }

    public function testWithParamsBlankPreviewImage() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => ''
            ]
        ];
        try {
            Blog::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog preview image can not be blank', $e->getMessage());
        }
    }

    public function testWithParamsNoContent() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => '/some/img'
            ]
        ];
        try {
            Blog::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog content is required', $e->getMessage());
        }
    }

    public function testWithParamsBlankContent() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => '/some/img'
            ],
            'content' => ''
        ];
        try {
            Blog::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog content can not be empty', $e->getMessage());
        }
    }

    public function testWithParamsEmptyContent() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => '/some/img'
            ],
            'content' => array()
        ];
        try {
            Blog::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog content can not be empty', $e->getMessage());
        }
    }

    public function testWithParamsStillEmptyContent() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => '/some/img'
            ],
            'content' => [
                array()
            ]
        ];
        try {
            Blog::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog content is not the correct format', $e->getMessage());
        }
    }

    public function testWithParamsNoContentType() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => '/some/img'
            ],
            'content' => [
                [
                    'text' => '123'
                ]
            ]
        ];
        try {
            Blog::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog content is not the correct format', $e->getMessage());
        }
    }

    public function testWithParamsBadContentType() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => '/some/img'
            ],
            'content' => [
                [
                    'type' => '123'
                ]
            ]
        ];
        try {
            Blog::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog content is not the correct format', $e->getMessage());
        }
    }

    public function testCreateNoAccess() {
        $blog = Blog::withId(899);
        try {
            $blog->create();
        } catch (Exception $e) {
            $this->assertEquals("User not authorized to create blog post", $e->getMessage());
        }
    }

    public function testCreateSimpleBlog() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => '../tmp/sample.jpg'
            ],
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'some sample text',
                    'group' => 1
                ]
            ]
        ];
        try {
            $blog = Blog::withParams($params);
            $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $blogId = $blog->create();
            $blogDetails = $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = $blogId;");
            $this->assertEquals($blogId, $blogDetails['id']);
            $this->assertEquals("Some Album", $blogDetails['title']);
            $this->assertNull($blogDetails['safe_title']);
            $this->assertEquals("2020-01-01", $blogDetails['date']);
            $this->assertEquals("posts/2020/01/01/preview_image-$blogId.jpg", $blogDetails['preview']);
            $this->assertEquals("0", $blogDetails['offset']);
            $this->assertEquals("0", $blogDetails['active']);
            $this->assertEquals("0", $blogDetails['twitter']);
            $blogTexts = $this->sql->getRows("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = $blogId;");
            $this->assertEquals(1, sizeof($blogTexts));
            $this->assertEquals($blogId, $blogTexts[0]['blog']);
            $this->assertEquals(1, $blogTexts[0]['contentGroup']);
            $this->assertEquals('some sample text', $blogTexts[0]['text']);
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = $blogId;"));
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = $blogId;"));
            $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "public/blog/posts/2020/01/01/preview_image-$blogId.jpg"));
        } finally {
            // cleanup
            unset($_SESSION['hash']);
            $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = $blogId;");
            $this->sql->executeStatement("DELETE FROM `blog_images` WHERE `blog_images`.`blog` = $blogId;");
            $this->sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = $blogId;");
            $this->sql->executeStatement("DELETE FROM `blog_texts` WHERE `blog_texts`.`blog` = $blogId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
        }
    }

    public function testCreateComplexBlog() {
        $params = [
            'title' => 'Sample Blog',
            'date' => '2030-01-01',
            'preview' => [
                'img' => '../tmp/sample.jpg',
                'offset' => '33px'
            ],
            'tags' => [
                "4", "2"
            ],
            'content' => [
                1 => [
                    'group' => 1,
                    'type' => 'images',
                    'imgs' => [
                        0 => [
                            'location' => '../tmp/sample1.jpg',
                            'top' => '0px',
                            'left' => '0px',
                            'width' => '1140px',
                            'height' => '647px',
                        ],
                        1 => [
                            'location' => '../tmp/sample2.jpg',
                            'top' => '647px',
                            'left' => '0px',
                            'width' => '1140px',
                            'height' => '647px',
                        ]
                    ]
                ],
                2 => [
                    'group' => 2,
                    'type' => 'text',
                    'text' => 'Some blog text'
                ]
            ]
        ];
        try {
            $blog = Blog::withParams($params);
            $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $blogId = $blog->create();
            $blogDetails = $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = $blogId;");
            $this->assertEquals($blogId, $blogDetails['id']);
            $this->assertEquals("Sample Blog", $blogDetails['title']);
            $this->assertNull($blogDetails['safe_title']);
            $this->assertEquals("2030-01-01", $blogDetails['date']);
            $this->assertEquals("posts/2030/01/01/preview_image-$blogId.jpg", $blogDetails['preview']);
            $this->assertEquals("33", $blogDetails['offset']);
            $this->assertEquals("0", $blogDetails['active']);
            $this->assertEquals("0", $blogDetails['twitter']);
            $blogImages = $this->sql->getRows("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = $blogId;");
            $this->assertEquals(2, sizeof($blogImages));
            $this->assertEquals($blogId, $blogImages[0]['blog']);
            $this->assertEquals(1, $blogImages[0]['contentGroup']);
            $this->assertEquals('posts/2030/01/01/sample1.jpg', $blogImages[0]['location']);
            $this->assertEquals('1140', $blogImages[0]['width']);
            $this->assertEquals('647', $blogImages[0]['height']);
            $this->assertEquals('0', $blogImages[0]['left']);
            $this->assertEquals('0', $blogImages[0]['top']);
            $this->assertEquals($blogId, $blogImages[1]['blog']);
            $this->assertEquals(1, $blogImages[1]['contentGroup']);
            $this->assertEquals('posts/2030/01/01/sample2.jpg', $blogImages[1]['location']);
            $this->assertEquals('1140', $blogImages[1]['width']);
            $this->assertEquals('647', $blogImages[1]['height']);
            $this->assertEquals('0', $blogImages[1]['left']);
            $this->assertEquals('647', $blogImages[1]['top']);
            $blogTags = $this->sql->getRows("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = $blogId;");
            $this->assertEquals(2, sizeOf($blogTags));
            $this->assertEquals($blogId, $blogTags[0]['blog']);
            $this->assertEquals(4, $blogTags[0]['tag']);
            $this->assertEquals($blogId, $blogTags[1]['blog']);
            $this->assertEquals(2, $blogTags[1]['tag']);
            $blogTexts = $this->sql->getRow("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = $blogId;");
            $this->assertEquals($blogId, $blogTexts['blog']);
            $this->assertEquals(2, $blogTexts['contentGroup']);
            $this->assertEquals('Some blog text', $blogTexts['text']);
            $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "public/blog/posts/2030/01/01/preview_image-$blogId.jpg"));
            $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "public/blog/posts/2030/01/01/sample1.jpg"));
            $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "public/blog/posts/2030/01/01/sample2.jpg"));
        } finally {
            // cleanup
            unset($_SESSION['hash']);
            $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = $blogId;");
            $this->sql->executeStatement("DELETE FROM `blog_images` WHERE `blog_images`.`blog` = $blogId;");
            $this->sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = $blogId;");
            $this->sql->executeStatement("DELETE FROM `blog_texts` WHERE `blog_texts`.`blog` = $blogId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
        }
    }

    public function testGetImagesMultiple() {
        $params = [
            'title' => 'Sample Blog',
            'date' => '2030-01-01',
            'preview' => [
                'img' => '../tmp/sample.jpg',
                'offset' => '33px'
            ],
            'tags' => [
                "4", "2"
            ],
            'content' => [
                1 => [
                    'group' => 1,
                    'type' => 'images',
                    'imgs' => [
                        0 => [
                            'location' => '../tmp/sample1.jpg',
                            'top' => '0px',
                            'left' => '0px',
                            'width' => '1140px',
                            'height' => '647px',
                        ],
                        1 => [
                            'location' => '../tmp/sample2.jpg',
                            'top' => '647px',
                            'left' => '0px',
                            'width' => '1140px',
                            'height' => '647px',
                        ]
                    ]
                ],
                2 => [
                    'group' => 2,
                    'type' => 'text',
                    'text' => 'Some blog text'
                ]
            ]
        ];
        try {
            $blog = Blog::withParams($params);
            $this->assertEquals(['../tmp/sample1.jpg','../tmp/sample2.jpg'], $blog->getImages());
        } finally {
            // cleanup
            $blogId = $blog->getId();
            $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = $blogId;");
            $this->sql->executeStatement("DELETE FROM `blog_images` WHERE `blog_images`.`blog` = $blogId;");
            $this->sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = $blogId;");
            $this->sql->executeStatement("DELETE FROM `blog_texts` WHERE `blog_texts`.`blog` = $blogId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
        }
    }

    public function testUpdateNoAccess() {
        $blog = Blog::withId(899);
        try {
            $blog->update(null);
        } catch (Exception $e) {
            $this->assertEquals("User not authorized to update blog post", $e->getMessage());
        }
    }

    public function testUpdateNullParams() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update(null);
        } catch (Exception $e) {
            $this->assertEquals('Blog title is required', $e->getMessage());
        } finally {
            unset( $_SESSION['hash']);
        }
    }

    public function testUpdateNoTitle() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update(array());
        } catch (Exception $e) {
            $this->assertEquals('Blog title is required', $e->getMessage());
        } finally {
            unset( $_SESSION['hash']);
        }
    }

    public function testUpdateBlankTitle() {
        $params = [
            'title' => ''
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog title can not be blank', $e->getMessage());
        } finally {
            unset( $_SESSION['hash']);
        }
    }

    public function testUpdateNoDate() {
        $params = [
            'title' => 'Some Album'
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog date is required', $e->getMessage());
        } finally {
            unset( $_SESSION['hash']);
        }
    }

    public function testUpdateBlankDate() {
        $params = [
            'title' => 'Some Album',
            'date' => ''
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog date can not be blank', $e->getMessage());
        }
    }

    public function testUpdateBadDate() {
        $params = [
            'title' => 'Sample Album',
            'date' => 'some date'
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog date is not the correct format', $e->getMessage());
        } finally {
            unset( $_SESSION['hash']);
        }
    }

    public function testUpdateNoPreviewImage() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01'
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog preview image is required', $e->getMessage());
        } finally {
            unset( $_SESSION['hash']);
        }
    }

    public function testUpdateEmptyPreviewImage() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => ''
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog preview image is required', $e->getMessage());
        } finally {
            unset( $_SESSION['hash']);
        }
    }

    public function testUpdateArrayPreviewImage() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => array()
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog preview image is required', $e->getMessage());
        } finally {
            unset( $_SESSION['hash']);
        }
    }

    public function testUpdateBlankPreviewImage() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => ''
            ]
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog preview image can not be blank', $e->getMessage());
        } finally {
            unset( $_SESSION['hash']);
        }
    }

    public function testUpdateNoContent() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => '../tmp/sample.jpg'
            ]
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update($params);
            //checkout our sql data
            $blogDetails = $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 899;");
            $this->assertEquals(899, $blogDetails['id']);
            $this->assertEquals("Some Album", $blogDetails['title']);
            $this->assertNull($blogDetails['safe_title']);
            $this->assertEquals("2020-01-01", $blogDetails['date']);
            $this->assertEquals("/some/img", $blogDetails['preview']);
            $this->assertEquals("0", $blogDetails['offset']);
            $this->assertEquals("0", $blogDetails['active']);
            $this->assertEquals("0", $blogDetails['twitter']);
            $blogImages = $this->sql->getRows("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = 899;");
            $this->assertEquals(1, sizeof($blogImages));
            $this->assertEquals(899, $blogImages[0]['blog']);
            $this->assertEquals(1, $blogImages[0]['contentGroup']);
            $this->assertEquals('posts/2031/01/01/sample.jpg', $blogImages[0]['location']);
            $this->assertEquals('300', $blogImages[0]['width']);
            $this->assertEquals('400', $blogImages[0]['height']);
            $this->assertEquals('0', $blogImages[0]['left']);
            $this->assertEquals('0', $blogImages[0]['top']);
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = 899;"));
            $blogTexts = $this->sql->getRow("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = 899;");
            $this->assertEquals(899, $blogTexts['blog']);
            $this->assertEquals(2, $blogTexts['contentGroup']);
            $this->assertEquals('Some blog text', $blogTexts['text']);
            $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "public/blog/posts/2020/01/01/preview_image-899.jpg"));
            //checkout our raw data
            $blogInfo = $blog->getDataArray();
            $this->assertEquals(899, $blogInfo['id']);
            $this->assertEquals('Some Album', $blogInfo['title']);
            $this->assertNull($blogInfo['safe_title']);
            $this->assertEquals('January 1st, 2020', $blogInfo['date']);
            $this->assertEquals('/some/img', $blogInfo['preview']);
            $this->assertEquals('0', $blogInfo['offset']);
            $this->assertEquals('0', $blogInfo['active']);
            $this->assertEquals(0, $blogInfo['twitter']);
            $this->assertEquals(2, sizeOf($blogInfo['content']));
            $this->assertEquals(1, sizeOf($blogInfo['content'][1]));
            $this->assertEquals(899, $blogInfo['content'][1][0]['blog']);
            $this->assertEquals(1, $blogInfo['content'][1][0]['contentGroup']);
            $this->assertEquals('posts/2031/01/01/sample.jpg', $blogInfo['content'][1][0]['location']);
            $this->assertEquals(300, $blogInfo['content'][1][0]['width']);
            $this->assertEquals(400, $blogInfo['content'][1][0]['height']);
            $this->assertEquals(0, $blogInfo['content'][1][0]['left']);
            $this->assertEquals(0, $blogInfo['content'][1][0]['top']);
            $this->assertEquals(1, sizeOf($blogInfo['content'][2]));
            $this->assertEquals(899, $blogInfo['content'][2][0]['blog']);
            $this->assertEquals(2, $blogInfo['content'][2][0]['contentGroup']);
            $this->assertEquals('Some blog text', $blogInfo['content'][2][0]['text']);
            $this->assertEquals(0, sizeOf($blogInfo['tags']));
            $this->assertEquals(2, sizeOf($blogInfo['comments']));
            $this->assertEquals(899, $blogInfo['comments'][0]['id']);
            $this->assertEquals(899, $blogInfo['comments'][0]['blog']);
            $this->assertEquals('awesome post', $blogInfo['comments'][0]['comment']);
            $this->assertEquals('2012-10-31 13:56:47', $blogInfo['comments'][0]['date']);
            $this->assertEquals('msaperst@gmail.com', $blogInfo['comments'][0]['email']);
            $this->assertEquals('192.168.1.2', $blogInfo['comments'][0]['ip']);
            $this->assertEquals('Uploader', $blogInfo['comments'][0]['name']);
            $this->assertEquals(4, $blogInfo['comments'][0]['user']);
            $this->assertEquals(898, $blogInfo['comments'][1]['id']);
            $this->assertEquals(899, $blogInfo['comments'][1]['blog']);
            $this->assertEquals('hehehehehe this rules!', $blogInfo['comments'][1]['comment']);
            $this->assertEquals('2012-10-31 09:56:47', $blogInfo['comments'][1]['date']);
            $this->assertEquals('annad@annadbruce.com', $blogInfo['comments'][1]['email']);
            $this->assertEquals('68.98.132.164', $blogInfo['comments'][1]['ip']);
            $this->assertEquals('Anna', $blogInfo['comments'][1]['name']);
            $this->assertNull($blogInfo['comments'][1]['user']);
        } finally {
            unset( $_SESSION['hash']);
        }
    }

    public function testUpdateBlankContent() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => '../tmp/sample.jpg'
            ],
            'content' => ''
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update($params);
            //checkout our sql data
            $blogDetails = $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 899;");
            $this->assertEquals(899, $blogDetails['id']);
            $this->assertEquals("Some Album", $blogDetails['title']);
            $this->assertNull($blogDetails['safe_title']);
            $this->assertEquals("2020-01-01", $blogDetails['date']);
            $this->assertEquals("/some/img", $blogDetails['preview']);
            $this->assertEquals("0", $blogDetails['offset']);
            $this->assertEquals("0", $blogDetails['active']);
            $this->assertEquals("0", $blogDetails['twitter']);
            $blogImages = $this->sql->getRows("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = 899;");
            $this->assertEquals(1, sizeof($blogImages));
            $this->assertEquals(899, $blogImages[0]['blog']);
            $this->assertEquals(1, $blogImages[0]['contentGroup']);
            $this->assertEquals('posts/2031/01/01/sample.jpg', $blogImages[0]['location']);
            $this->assertEquals('300', $blogImages[0]['width']);
            $this->assertEquals('400', $blogImages[0]['height']);
            $this->assertEquals('0', $blogImages[0]['left']);
            $this->assertEquals('0', $blogImages[0]['top']);
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = 899;"));
            $blogTexts = $this->sql->getRow("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = 899;");
            $this->assertEquals(899, $blogTexts['blog']);
            $this->assertEquals(2, $blogTexts['contentGroup']);
            $this->assertEquals('Some blog text', $blogTexts['text']);
            $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "public/blog/posts/2020/01/01/preview_image-899.jpg"));
            //checkout our raw data
            $blogInfo = $blog->getDataArray();
            $this->assertEquals(899, $blogInfo['id']);
            $this->assertEquals('Some Album', $blogInfo['title']);
            $this->assertNull($blogInfo['safe_title']);
            $this->assertEquals('January 1st, 2020', $blogInfo['date']);
            $this->assertEquals('/some/img', $blogInfo['preview']);
            $this->assertEquals('0', $blogInfo['offset']);
            $this->assertEquals('0', $blogInfo['active']);
            $this->assertEquals(0, $blogInfo['twitter']);
            $this->assertEquals(2, sizeOf($blogInfo['content']));
            $this->assertEquals(1, sizeOf($blogInfo['content'][1]));
            $this->assertEquals(899, $blogInfo['content'][1][0]['blog']);
            $this->assertEquals(1, $blogInfo['content'][1][0]['contentGroup']);
            $this->assertEquals('posts/2031/01/01/sample.jpg', $blogInfo['content'][1][0]['location']);
            $this->assertEquals(300, $blogInfo['content'][1][0]['width']);
            $this->assertEquals(400, $blogInfo['content'][1][0]['height']);
            $this->assertEquals(0, $blogInfo['content'][1][0]['left']);
            $this->assertEquals(0, $blogInfo['content'][1][0]['top']);
            $this->assertEquals(1, sizeOf($blogInfo['content'][2]));
            $this->assertEquals(899, $blogInfo['content'][2][0]['blog']);
            $this->assertEquals(2, $blogInfo['content'][2][0]['contentGroup']);
            $this->assertEquals('Some blog text', $blogInfo['content'][2][0]['text']);
            $this->assertEquals(0, sizeOf($blogInfo['tags']));
            $this->assertEquals(2, sizeOf($blogInfo['comments']));
            $this->assertEquals(899, $blogInfo['comments'][0]['id']);
            $this->assertEquals(899, $blogInfo['comments'][0]['blog']);
            $this->assertEquals('awesome post', $blogInfo['comments'][0]['comment']);
            $this->assertEquals('2012-10-31 13:56:47', $blogInfo['comments'][0]['date']);
            $this->assertEquals('msaperst@gmail.com', $blogInfo['comments'][0]['email']);
            $this->assertEquals('192.168.1.2', $blogInfo['comments'][0]['ip']);
            $this->assertEquals('Uploader', $blogInfo['comments'][0]['name']);
            $this->assertEquals(4, $blogInfo['comments'][0]['user']);
            $this->assertEquals(898, $blogInfo['comments'][1]['id']);
            $this->assertEquals(899, $blogInfo['comments'][1]['blog']);
            $this->assertEquals('hehehehehe this rules!', $blogInfo['comments'][1]['comment']);
            $this->assertEquals('2012-10-31 09:56:47', $blogInfo['comments'][1]['date']);
            $this->assertEquals('annad@annadbruce.com', $blogInfo['comments'][1]['email']);
            $this->assertEquals('68.98.132.164', $blogInfo['comments'][1]['ip']);
            $this->assertEquals('Anna', $blogInfo['comments'][1]['name']);
            $this->assertNull($blogInfo['comments'][1]['user']);
        } finally {
            unset( $_SESSION['hash']);
        }
    }

    public function testUpdateEmptyContent() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => '../tmp/sample.jpg'
            ],
            'content' => array()
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update($params);
            //checkout our sql data
            $blogDetails = $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 899;");
            $this->assertEquals(899, $blogDetails['id']);
            $this->assertEquals("Some Album", $blogDetails['title']);
            $this->assertNull($blogDetails['safe_title']);
            $this->assertEquals("2020-01-01", $blogDetails['date']);
            $this->assertEquals("/some/img", $blogDetails['preview']);
            $this->assertEquals("0", $blogDetails['offset']);
            $this->assertEquals("0", $blogDetails['active']);
            $this->assertEquals("0", $blogDetails['twitter']);
            $blogImages = $this->sql->getRows("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = 899;");
            $this->assertEquals(1, sizeof($blogImages));
            $this->assertEquals(899, $blogImages[0]['blog']);
            $this->assertEquals(1, $blogImages[0]['contentGroup']);
            $this->assertEquals('posts/2031/01/01/sample.jpg', $blogImages[0]['location']);
            $this->assertEquals('300', $blogImages[0]['width']);
            $this->assertEquals('400', $blogImages[0]['height']);
            $this->assertEquals('0', $blogImages[0]['left']);
            $this->assertEquals('0', $blogImages[0]['top']);
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = 899;"));
            $blogTexts = $this->sql->getRow("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = 899;");
            $this->assertEquals(899, $blogTexts['blog']);
            $this->assertEquals(2, $blogTexts['contentGroup']);
            $this->assertEquals('Some blog text', $blogTexts['text']);
            $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "public/blog/posts/2020/01/01/preview_image-899.jpg"));
            //checkout our raw data
            $blogInfo = $blog->getDataArray();
            $this->assertEquals(899, $blogInfo['id']);
            $this->assertEquals('Some Album', $blogInfo['title']);
            $this->assertNull($blogInfo['safe_title']);
            $this->assertEquals('January 1st, 2020', $blogInfo['date']);
            $this->assertEquals('/some/img', $blogInfo['preview']);
            $this->assertEquals('0', $blogInfo['offset']);
            $this->assertEquals('0', $blogInfo['active']);
            $this->assertEquals(0, $blogInfo['twitter']);
            $this->assertEquals(2, sizeOf($blogInfo['content']));
            $this->assertEquals(1, sizeOf($blogInfo['content'][1]));
            $this->assertEquals(899, $blogInfo['content'][1][0]['blog']);
            $this->assertEquals(1, $blogInfo['content'][1][0]['contentGroup']);
            $this->assertEquals('posts/2031/01/01/sample.jpg', $blogInfo['content'][1][0]['location']);
            $this->assertEquals(300, $blogInfo['content'][1][0]['width']);
            $this->assertEquals(400, $blogInfo['content'][1][0]['height']);
            $this->assertEquals(0, $blogInfo['content'][1][0]['left']);
            $this->assertEquals(0, $blogInfo['content'][1][0]['top']);
            $this->assertEquals(1, sizeOf($blogInfo['content'][2]));
            $this->assertEquals(899, $blogInfo['content'][2][0]['blog']);
            $this->assertEquals(2, $blogInfo['content'][2][0]['contentGroup']);
            $this->assertEquals('Some blog text', $blogInfo['content'][2][0]['text']);
            $this->assertEquals(0, sizeOf($blogInfo['tags']));
            $this->assertEquals(2, sizeOf($blogInfo['comments']));
            $this->assertEquals(899, $blogInfo['comments'][0]['id']);
            $this->assertEquals(899, $blogInfo['comments'][0]['blog']);
            $this->assertEquals('awesome post', $blogInfo['comments'][0]['comment']);
            $this->assertEquals('2012-10-31 13:56:47', $blogInfo['comments'][0]['date']);
            $this->assertEquals('msaperst@gmail.com', $blogInfo['comments'][0]['email']);
            $this->assertEquals('192.168.1.2', $blogInfo['comments'][0]['ip']);
            $this->assertEquals('Uploader', $blogInfo['comments'][0]['name']);
            $this->assertEquals(4, $blogInfo['comments'][0]['user']);
            $this->assertEquals(898, $blogInfo['comments'][1]['id']);
            $this->assertEquals(899, $blogInfo['comments'][1]['blog']);
            $this->assertEquals('hehehehehe this rules!', $blogInfo['comments'][1]['comment']);
            $this->assertEquals('2012-10-31 09:56:47', $blogInfo['comments'][1]['date']);
            $this->assertEquals('annad@annadbruce.com', $blogInfo['comments'][1]['email']);
            $this->assertEquals('68.98.132.164', $blogInfo['comments'][1]['ip']);
            $this->assertEquals('Anna', $blogInfo['comments'][1]['name']);
            $this->assertNull($blogInfo['comments'][1]['user']);
        } finally {
            unset( $_SESSION['hash']);
        }
    }

    public function testUpdateStillEmptyContent() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => '../tmp/sample.jpg'
            ],
            'content' => [
                array()
            ]
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog content is not the correct format', $e->getMessage());
        } finally {
            unset( $_SESSION['hash']);
        }
    }

    public function testUpdateNoContentType() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => '../tmp/sample.jpg'
            ],
            'content' => [
                [
                    'text' => '123'
                ]
            ]
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog content is not the correct format', $e->getMessage());
        } finally {
            unset( $_SESSION['hash']);
        }
    }

    public function testUpdateBadContentType() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => '../tmp/sample.jpg'
            ],
            'content' => [
                [
                    'type' => '123'
                ]
            ]
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update($params);
        } catch (Exception $e) {
            $this->assertEquals('Blog content is not the correct format', $e->getMessage());
        } finally {
            unset( $_SESSION['hash']);
        }
    }

    public function testUpdateBlankTags() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => '../tmp/sample.jpg'
            ],
            'tags' => ''
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update($params);
            //checkout our sql data
            $blogDetails = $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 899;");
            $this->assertEquals(899, $blogDetails['id']);
            $this->assertEquals("Some Album", $blogDetails['title']);
            $this->assertNull($blogDetails['safe_title']);
            $this->assertEquals("2020-01-01", $blogDetails['date']);
            $this->assertEquals("/some/img", $blogDetails['preview']);
            $this->assertEquals("0", $blogDetails['offset']);
            $this->assertEquals("0", $blogDetails['active']);
            $this->assertEquals("0", $blogDetails['twitter']);
            $blogImages = $this->sql->getRows("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = 899;");
            $this->assertEquals(1, sizeof($blogImages));
            $this->assertEquals(899, $blogImages[0]['blog']);
            $this->assertEquals(1, $blogImages[0]['contentGroup']);
            $this->assertEquals('posts/2031/01/01/sample.jpg', $blogImages[0]['location']);
            $this->assertEquals('300', $blogImages[0]['width']);
            $this->assertEquals('400', $blogImages[0]['height']);
            $this->assertEquals('0', $blogImages[0]['left']);
            $this->assertEquals('0', $blogImages[0]['top']);
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = 899;"));
            $blogTexts = $this->sql->getRow("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = 899;");
            $this->assertEquals(899, $blogTexts['blog']);
            $this->assertEquals(2, $blogTexts['contentGroup']);
            $this->assertEquals('Some blog text', $blogTexts['text']);
            $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "public/blog/posts/2020/01/01/preview_image-899.jpg"));
            //checkout our raw data
            $blogInfo = $blog->getDataArray();
            $this->assertEquals(899, $blogInfo['id']);
            $this->assertEquals('Some Album', $blogInfo['title']);
            $this->assertNull($blogInfo['safe_title']);
            $this->assertEquals('January 1st, 2020', $blogInfo['date']);
            $this->assertEquals('/some/img', $blogInfo['preview']);
            $this->assertEquals('0', $blogInfo['offset']);
            $this->assertEquals('0', $blogInfo['active']);
            $this->assertEquals(0, $blogInfo['twitter']);
            $this->assertEquals(2, sizeOf($blogInfo['content']));
            $this->assertEquals(1, sizeOf($blogInfo['content'][1]));
            $this->assertEquals(899, $blogInfo['content'][1][0]['blog']);
            $this->assertEquals(1, $blogInfo['content'][1][0]['contentGroup']);
            $this->assertEquals('posts/2031/01/01/sample.jpg', $blogInfo['content'][1][0]['location']);
            $this->assertEquals(300, $blogInfo['content'][1][0]['width']);
            $this->assertEquals(400, $blogInfo['content'][1][0]['height']);
            $this->assertEquals(0, $blogInfo['content'][1][0]['left']);
            $this->assertEquals(0, $blogInfo['content'][1][0]['top']);
            $this->assertEquals(1, sizeOf($blogInfo['content'][2]));
            $this->assertEquals(899, $blogInfo['content'][2][0]['blog']);
            $this->assertEquals(2, $blogInfo['content'][2][0]['contentGroup']);
            $this->assertEquals('Some blog text', $blogInfo['content'][2][0]['text']);
            $this->assertEquals(0, sizeOf($blogInfo['tags']));
            $this->assertEquals(2, sizeOf($blogInfo['comments']));
            $this->assertEquals(899, $blogInfo['comments'][0]['id']);
            $this->assertEquals(899, $blogInfo['comments'][0]['blog']);
            $this->assertEquals('awesome post', $blogInfo['comments'][0]['comment']);
            $this->assertEquals('2012-10-31 13:56:47', $blogInfo['comments'][0]['date']);
            $this->assertEquals('msaperst@gmail.com', $blogInfo['comments'][0]['email']);
            $this->assertEquals('192.168.1.2', $blogInfo['comments'][0]['ip']);
            $this->assertEquals('Uploader', $blogInfo['comments'][0]['name']);
            $this->assertEquals(4, $blogInfo['comments'][0]['user']);
            $this->assertEquals(898, $blogInfo['comments'][1]['id']);
            $this->assertEquals(899, $blogInfo['comments'][1]['blog']);
            $this->assertEquals('hehehehehe this rules!', $blogInfo['comments'][1]['comment']);
            $this->assertEquals('2012-10-31 09:56:47', $blogInfo['comments'][1]['date']);
            $this->assertEquals('annad@annadbruce.com', $blogInfo['comments'][1]['email']);
            $this->assertEquals('68.98.132.164', $blogInfo['comments'][1]['ip']);
            $this->assertEquals('Anna', $blogInfo['comments'][1]['name']);
            $this->assertNull($blogInfo['comments'][1]['user']);
        } finally {
            unset( $_SESSION['hash']);
        }
    }

    public function testUpdateTags() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => '../tmp/sample.jpg'
            ],
            'tags' => [
                21,
                56
            ]
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update($params);
            //checkout our sql data
            $blogDetails = $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 899;");
            $this->assertEquals(899, $blogDetails['id']);
            $this->assertEquals("Some Album", $blogDetails['title']);
            $this->assertNull($blogDetails['safe_title']);
            $this->assertEquals("2020-01-01", $blogDetails['date']);
            $this->assertEquals("/some/img", $blogDetails['preview']);
            $this->assertEquals("0", $blogDetails['offset']);
            $this->assertEquals("0", $blogDetails['active']);
            $this->assertEquals("0", $blogDetails['twitter']);
            $blogImages = $this->sql->getRows("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = 899;");
            $this->assertEquals(1, sizeof($blogImages));
            $this->assertEquals(899, $blogImages[0]['blog']);
            $this->assertEquals(1, $blogImages[0]['contentGroup']);
            $this->assertEquals('posts/2031/01/01/sample.jpg', $blogImages[0]['location']);
            $this->assertEquals('300', $blogImages[0]['width']);
            $this->assertEquals('400', $blogImages[0]['height']);
            $this->assertEquals('0', $blogImages[0]['left']);
            $this->assertEquals('0', $blogImages[0]['top']);
            $blogTags = $this->sql->getRows("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = 899;");
            $this->assertEquals(2, sizeOf($blogTags));
            $this->assertEquals(899, $blogTags[0]['blog']);
            $this->assertEquals(21, $blogTags[0]['tag']);
            $this->assertEquals(899, $blogTags[1]['blog']);
            $this->assertEquals(56, $blogTags[1]['tag']);
            $blogTexts = $this->sql->getRow("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = 899;");
            $this->assertEquals(899, $blogTexts['blog']);
            $this->assertEquals(2, $blogTexts['contentGroup']);
            $this->assertEquals('Some blog text', $blogTexts['text']);
            $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "public/blog/posts/2020/01/01/preview_image-899.jpg"));
            //checkout our raw data
            $blogInfo = $blog->getDataArray();
            $this->assertEquals(899, $blogInfo['id']);
            $this->assertEquals('Some Album', $blogInfo['title']);
            $this->assertNull($blogInfo['safe_title']);
            $this->assertEquals('January 1st, 2020', $blogInfo['date']);
            $this->assertEquals('/some/img', $blogInfo['preview']);
            $this->assertEquals('0', $blogInfo['offset']);
            $this->assertEquals('0', $blogInfo['active']);
            $this->assertEquals(0, $blogInfo['twitter']);
            $this->assertEquals(2, sizeOf($blogInfo['content']));
            $this->assertEquals(1, sizeOf($blogInfo['content'][1]));
            $this->assertEquals(899, $blogInfo['content'][1][0]['blog']);
            $this->assertEquals(1, $blogInfo['content'][1][0]['contentGroup']);
            $this->assertEquals('posts/2031/01/01/sample.jpg', $blogInfo['content'][1][0]['location']);
            $this->assertEquals(300, $blogInfo['content'][1][0]['width']);
            $this->assertEquals(400, $blogInfo['content'][1][0]['height']);
            $this->assertEquals(0, $blogInfo['content'][1][0]['left']);
            $this->assertEquals(0, $blogInfo['content'][1][0]['top']);
            $this->assertEquals(1, sizeOf($blogInfo['content'][2]));
            $this->assertEquals(899, $blogInfo['content'][2][0]['blog']);
            $this->assertEquals(2, $blogInfo['content'][2][0]['contentGroup']);
            $this->assertEquals('Some blog text', $blogInfo['content'][2][0]['text']);
            $this->assertEquals(2, sizeOf($blogInfo['tags']));
            $this->assertEquals(21, $blogInfo['tags'][0]['id']);
            $this->assertEquals('Pets', $blogInfo['tags'][0]['tag']);
            $this->assertEquals(56, $blogInfo['tags'][1]['id']);
            $this->assertEquals('Oatlands Plantation', $blogInfo['tags'][1]['tag']);
            $this->assertEquals(2, sizeOf($blogInfo['comments']));
            $this->assertEquals(899, $blogInfo['comments'][0]['id']);
            $this->assertEquals(899, $blogInfo['comments'][0]['blog']);
            $this->assertEquals('awesome post', $blogInfo['comments'][0]['comment']);
            $this->assertEquals('2012-10-31 13:56:47', $blogInfo['comments'][0]['date']);
            $this->assertEquals('msaperst@gmail.com', $blogInfo['comments'][0]['email']);
            $this->assertEquals('192.168.1.2', $blogInfo['comments'][0]['ip']);
            $this->assertEquals('Uploader', $blogInfo['comments'][0]['name']);
            $this->assertEquals(4, $blogInfo['comments'][0]['user']);
            $this->assertEquals(898, $blogInfo['comments'][1]['id']);
            $this->assertEquals(899, $blogInfo['comments'][1]['blog']);
            $this->assertEquals('hehehehehe this rules!', $blogInfo['comments'][1]['comment']);
            $this->assertEquals('2012-10-31 09:56:47', $blogInfo['comments'][1]['date']);
            $this->assertEquals('annad@annadbruce.com', $blogInfo['comments'][1]['email']);
            $this->assertEquals('68.98.132.164', $blogInfo['comments'][1]['ip']);
            $this->assertEquals('Anna', $blogInfo['comments'][1]['name']);
            $this->assertNull($blogInfo['comments'][1]['user']);
        } finally {
            unset( $_SESSION['hash']);
        }
    }

    public function testUpdateContent() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => '../tmp/sample.jpg'
            ],
            'content' => [
                1 => [
                    'group' => 1,
                    'type' => 'images',
                    'imgs' => [
                        0 => [
                            'location' => '../tmp/sample1.jpg',
                            'top' => '0px',
                            'left' => '0px',
                            'width' => '1140px',
                            'height' => '647px',
                        ],
                        1 => [
                            'location' => '../tmp/sample2.jpg',
                            'top' => '647px',
                            'left' => '0px',
                            'width' => '1140px',
                            'height' => '647px',
                        ]
                    ]
                ],
                2 => [
                    'group' => 2,
                    'type' => 'text',
                    'text' => 'Some more blog text'
                ]
            ]
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $blog = Blog::withId(899);
            $blog->update($params);
            //checkout our sql data
            $blogDetails = $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 899;");
            $this->assertEquals(899, $blogDetails['id']);
            $this->assertEquals("Some Album", $blogDetails['title']);
            $this->assertNull($blogDetails['safe_title']);
            $this->assertEquals("2020-01-01", $blogDetails['date']);
            $this->assertEquals("/some/img", $blogDetails['preview']);
            $this->assertEquals("0", $blogDetails['offset']);
            $this->assertEquals("0", $blogDetails['active']);
            $this->assertEquals("0", $blogDetails['twitter']);
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = 899;"));
            $blogImages = $this->sql->getRows("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = 899;");
            $this->assertEquals(2, sizeof($blogImages));
            $this->assertEquals(899, $blogImages[0]['blog']);
            $this->assertEquals(1, $blogImages[0]['contentGroup']);
            $this->assertEquals('posts/2020/01/01/sample1.jpg', $blogImages[0]['location']);
            $this->assertEquals('1140', $blogImages[0]['width']);
            $this->assertEquals('647', $blogImages[0]['height']);
            $this->assertEquals('0', $blogImages[0]['left']);
            $this->assertEquals('0', $blogImages[0]['top']);
            $this->assertEquals(899, $blogImages[1]['blog']);
            $this->assertEquals(1, $blogImages[1]['contentGroup']);
            $this->assertEquals('posts/2020/01/01/sample2.jpg', $blogImages[1]['location']);
            $this->assertEquals('1140', $blogImages[1]['width']);
            $this->assertEquals('647', $blogImages[1]['height']);
            $this->assertEquals('0', $blogImages[1]['left']);
            $this->assertEquals('647', $blogImages[1]['top']);
            $blogTexts = $this->sql->getRow("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = 899;");
            $this->assertEquals(899, $blogTexts['blog']);
            $this->assertEquals(2, $blogTexts['contentGroup']);
            $this->assertEquals('Some more blog text', $blogTexts['text']);
            $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "public/blog/posts/2020/01/01/preview_image-899.jpg"));
            $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "public/blog/posts/2020/01/01/sample1.jpg"));
            $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "public/blog/posts/2020/01/01/sample2.jpg"));
            //checkout our raw data
            $blogInfo = $blog->getDataArray();
            $this->assertEquals(899, $blogInfo['id']);
            $this->assertEquals('Some Album', $blogInfo['title']);
            $this->assertNull($blogInfo['safe_title']);
            $this->assertEquals('January 1st, 2020', $blogInfo['date']);
            $this->assertEquals('/some/img', $blogInfo['preview']);
            $this->assertEquals('0', $blogInfo['offset']);
            $this->assertEquals('0', $blogInfo['active']);
            $this->assertEquals(0, $blogInfo['twitter']);
            $this->assertEquals(2, sizeOf($blogInfo['content']));
            $this->assertEquals(2, sizeOf($blogInfo['content'][1]));
            $this->assertEquals(899, $blogInfo['content'][1][0]['blog']);
            $this->assertEquals(1, $blogInfo['content'][1][0]['contentGroup']);
            $this->assertEquals('posts/2020/01/01/sample1.jpg', $blogInfo['content'][1][0]['location']);
            $this->assertEquals(1140, $blogInfo['content'][1][0]['width']);
            $this->assertEquals(647, $blogInfo['content'][1][0]['height']);
            $this->assertEquals(0, $blogInfo['content'][1][0]['left']);
            $this->assertEquals(0, $blogInfo['content'][1][0]['top']);
            $this->assertEquals(899, $blogInfo['content'][1][1]['blog']);
            $this->assertEquals(1, $blogInfo['content'][1][1]['contentGroup']);
            $this->assertEquals('posts/2020/01/01/sample2.jpg', $blogInfo['content'][1][1]['location']);
            $this->assertEquals(1140, $blogInfo['content'][1][1]['width']);
            $this->assertEquals(647, $blogInfo['content'][1][1]['height']);
            $this->assertEquals(0, $blogInfo['content'][1][1]['left']);
            $this->assertEquals(647, $blogInfo['content'][1][1]['top']);
            $this->assertEquals(1, sizeOf($blogInfo['content'][2]));
            $this->assertEquals(899, $blogInfo['content'][2][0]['blog']);
            $this->assertEquals(2, $blogInfo['content'][2][0]['contentGroup']);
            $this->assertEquals('Some more blog text', $blogInfo['content'][2][0]['text']);
            $this->assertEquals(0, sizeOf($blogInfo['tags']));
            $this->assertEquals(2, sizeOf($blogInfo['comments']));
            $this->assertEquals(899, $blogInfo['comments'][0]['id']);
            $this->assertEquals(899, $blogInfo['comments'][0]['blog']);
            $this->assertEquals('awesome post', $blogInfo['comments'][0]['comment']);
            $this->assertEquals('2012-10-31 13:56:47', $blogInfo['comments'][0]['date']);
            $this->assertEquals('msaperst@gmail.com', $blogInfo['comments'][0]['email']);
            $this->assertEquals('192.168.1.2', $blogInfo['comments'][0]['ip']);
            $this->assertEquals('Uploader', $blogInfo['comments'][0]['name']);
            $this->assertEquals(4, $blogInfo['comments'][0]['user']);
            $this->assertEquals(898, $blogInfo['comments'][1]['id']);
            $this->assertEquals(899, $blogInfo['comments'][1]['blog']);
            $this->assertEquals('hehehehehe this rules!', $blogInfo['comments'][1]['comment']);
            $this->assertEquals('2012-10-31 09:56:47', $blogInfo['comments'][1]['date']);
            $this->assertEquals('annad@annadbruce.com', $blogInfo['comments'][1]['email']);
            $this->assertEquals('68.98.132.164', $blogInfo['comments'][1]['ip']);
            $this->assertEquals('Anna', $blogInfo['comments'][1]['name']);
            $this->assertNull($blogInfo['comments'][1]['user']);
        } finally {
            unset( $_SESSION['hash']);
        }
    }

    public function testUpdateMakeActive() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => '../tmp/sample.jpg'
            ],
            'active' => 1
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $_SERVER['SERVER_NAME'] = "www.examples.com";
        $_SERVER['SERVER_PORT'] = "90";
        try {
            $blog = Blog::withId(899);
            $blog->update($params);
            //checkout our sql data
            $blogDetails = $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 899;");
            $this->assertEquals(899, $blogDetails['id']);
            $this->assertEquals("Some Album", $blogDetails['title']);
            $this->assertNull($blogDetails['safe_title']);
            $this->assertEquals("2020-01-01", $blogDetails['date']);
            $this->assertEquals("/some/img", $blogDetails['preview']);
            $this->assertEquals("0", $blogDetails['offset']);
            $this->assertEquals("1", $blogDetails['active']);
            $this->assertNotEquals("0", $blogDetails['twitter']);
            $blogImages = $this->sql->getRows("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = 899;");
            $this->assertEquals(1, sizeof($blogImages));
            $this->assertEquals(899, $blogImages[0]['blog']);
            $this->assertEquals(1, $blogImages[0]['contentGroup']);
            $this->assertEquals('posts/2031/01/01/sample.jpg', $blogImages[0]['location']);
            $this->assertEquals('300', $blogImages[0]['width']);
            $this->assertEquals('400', $blogImages[0]['height']);
            $this->assertEquals('0', $blogImages[0]['left']);
            $this->assertEquals('0', $blogImages[0]['top']);
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = 899;"));
            $blogTexts = $this->sql->getRow("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = 899;");
            $this->assertEquals(899, $blogTexts['blog']);
            $this->assertEquals(2, $blogTexts['contentGroup']);
            $this->assertEquals('Some blog text', $blogTexts['text']);
            $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "public/blog/posts/2020/01/01/preview_image-899.jpg"));
            //checkout our raw data
            $blogInfo = $blog->getDataArray();
            $this->assertEquals(899, $blogInfo['id']);
            $this->assertEquals('Some Album', $blogInfo['title']);
            $this->assertNull($blogInfo['safe_title']);
            $this->assertEquals('January 1st, 2020', $blogInfo['date']);
            $this->assertEquals('/some/img', $blogInfo['preview']);
            $this->assertEquals('0', $blogInfo['offset']);
            $this->assertEquals('1', $blogInfo['active']);
            $this->assertNotEquals(0, $blogInfo['twitter']);
            $this->assertEquals(2, sizeOf($blogInfo['content']));
            $this->assertEquals(1, sizeOf($blogInfo['content'][1]));
            $this->assertEquals(899, $blogInfo['content'][1][0]['blog']);
            $this->assertEquals(1, $blogInfo['content'][1][0]['contentGroup']);
            $this->assertEquals('posts/2031/01/01/sample.jpg', $blogInfo['content'][1][0]['location']);
            $this->assertEquals(300, $blogInfo['content'][1][0]['width']);
            $this->assertEquals(400, $blogInfo['content'][1][0]['height']);
            $this->assertEquals(0, $blogInfo['content'][1][0]['left']);
            $this->assertEquals(0, $blogInfo['content'][1][0]['top']);
            $this->assertEquals(1, sizeOf($blogInfo['content'][2]));
            $this->assertEquals(899, $blogInfo['content'][2][0]['blog']);
            $this->assertEquals(2, $blogInfo['content'][2][0]['contentGroup']);
            $this->assertEquals('Some blog text', $blogInfo['content'][2][0]['text']);
            $this->assertEquals(0, sizeOf($blogInfo['tags']));
            $this->assertEquals(2, sizeOf($blogInfo['comments']));
            $this->assertEquals(899, $blogInfo['comments'][0]['id']);
            $this->assertEquals(899, $blogInfo['comments'][0]['blog']);
            $this->assertEquals('awesome post', $blogInfo['comments'][0]['comment']);
            $this->assertEquals('2012-10-31 13:56:47', $blogInfo['comments'][0]['date']);
            $this->assertEquals('msaperst@gmail.com', $blogInfo['comments'][0]['email']);
            $this->assertEquals('192.168.1.2', $blogInfo['comments'][0]['ip']);
            $this->assertEquals('Uploader', $blogInfo['comments'][0]['name']);
            $this->assertEquals(4, $blogInfo['comments'][0]['user']);
            $this->assertEquals(898, $blogInfo['comments'][1]['id']);
            $this->assertEquals(899, $blogInfo['comments'][1]['blog']);
            $this->assertEquals('hehehehehe this rules!', $blogInfo['comments'][1]['comment']);
            $this->assertEquals('2012-10-31 09:56:47', $blogInfo['comments'][1]['date']);
            $this->assertEquals('annad@annadbruce.com', $blogInfo['comments'][1]['email']);
            $this->assertEquals('68.98.132.164', $blogInfo['comments'][1]['ip']);
            $this->assertEquals('Anna', $blogInfo['comments'][1]['name']);
            $this->assertNull($blogInfo['comments'][1]['user']);
        } finally {
            unset( $_SESSION['hash']);
            unset( $_SERVER['SERVER_NAME']);
            unset( $_SERVER['SERVER_PORT']);
            $socialMedia = new SocialMedia();
            $socialMedia->removeBlogFromTwitter($blog);
        }
    }

    public function testUpdateMakeInActive() {
        $params = [
            'title' => 'Some Album',
            'date' => '2020-01-01',
            'preview' => [
                'img' => '../tmp/sample.jpg'
            ],
            'active' => 0
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $_SERVER['SERVER_NAME'] = "www.examples.com";
        $_SERVER['SERVER_PORT'] = "90";
        try {
            $this->sql->executeStatement("UPDATE `blog_details` SET `active` = '1', `twitter` = 1234567 WHERE `id` = 899;");
            $blog = Blog::withId(899);
            $blog->update($params);
            //checkout our sql data
            $blogDetails = $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 899;");
            $this->assertEquals(899, $blogDetails['id']);
            $this->assertEquals("Some Album", $blogDetails['title']);
            $this->assertNull($blogDetails['safe_title']);
            $this->assertEquals("2020-01-01", $blogDetails['date']);
            $this->assertEquals("/some/img", $blogDetails['preview']);
            $this->assertEquals("0", $blogDetails['offset']);
            $this->assertEquals("0", $blogDetails['active']);
            $this->assertEquals("0", $blogDetails['twitter']);
            $blogImages = $this->sql->getRows("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = 899;");
            $this->assertEquals(1, sizeof($blogImages));
            $this->assertEquals(899, $blogImages[0]['blog']);
            $this->assertEquals(1, $blogImages[0]['contentGroup']);
            $this->assertEquals('posts/2031/01/01/sample.jpg', $blogImages[0]['location']);
            $this->assertEquals('300', $blogImages[0]['width']);
            $this->assertEquals('400', $blogImages[0]['height']);
            $this->assertEquals('0', $blogImages[0]['left']);
            $this->assertEquals('0', $blogImages[0]['top']);
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = 899;"));
            $blogTexts = $this->sql->getRow("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = 899;");
            $this->assertEquals(899, $blogTexts['blog']);
            $this->assertEquals(2, $blogTexts['contentGroup']);
            $this->assertEquals('Some blog text', $blogTexts['text']);
            $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "public/blog/posts/2020/01/01/preview_image-899.jpg"));
            //checkout our raw data
            $blogInfo = $blog->getDataArray();
            $this->assertEquals(899, $blogInfo['id']);
            $this->assertEquals('Some Album', $blogInfo['title']);
            $this->assertNull($blogInfo['safe_title']);
            $this->assertEquals('January 1st, 2020', $blogInfo['date']);
            $this->assertEquals('/some/img', $blogInfo['preview']);
            $this->assertEquals('0', $blogInfo['offset']);
            $this->assertEquals('0', $blogInfo['active']);
            $this->assertEquals(0, $blogInfo['twitter']);
            $this->assertEquals(2, sizeOf($blogInfo['content']));
            $this->assertEquals(1, sizeOf($blogInfo['content'][1]));
            $this->assertEquals(899, $blogInfo['content'][1][0]['blog']);
            $this->assertEquals(1, $blogInfo['content'][1][0]['contentGroup']);
            $this->assertEquals('posts/2031/01/01/sample.jpg', $blogInfo['content'][1][0]['location']);
            $this->assertEquals(300, $blogInfo['content'][1][0]['width']);
            $this->assertEquals(400, $blogInfo['content'][1][0]['height']);
            $this->assertEquals(0, $blogInfo['content'][1][0]['left']);
            $this->assertEquals(0, $blogInfo['content'][1][0]['top']);
            $this->assertEquals(1, sizeOf($blogInfo['content'][2]));
            $this->assertEquals(899, $blogInfo['content'][2][0]['blog']);
            $this->assertEquals(2, $blogInfo['content'][2][0]['contentGroup']);
            $this->assertEquals('Some blog text', $blogInfo['content'][2][0]['text']);
            $this->assertEquals(0, sizeOf($blogInfo['tags']));
            $this->assertEquals(2, sizeOf($blogInfo['comments']));
            $this->assertEquals(899, $blogInfo['comments'][0]['id']);
            $this->assertEquals(899, $blogInfo['comments'][0]['blog']);
            $this->assertEquals('awesome post', $blogInfo['comments'][0]['comment']);
            $this->assertEquals('2012-10-31 13:56:47', $blogInfo['comments'][0]['date']);
            $this->assertEquals('msaperst@gmail.com', $blogInfo['comments'][0]['email']);
            $this->assertEquals('192.168.1.2', $blogInfo['comments'][0]['ip']);
            $this->assertEquals('Uploader', $blogInfo['comments'][0]['name']);
            $this->assertEquals(4, $blogInfo['comments'][0]['user']);
            $this->assertEquals(898, $blogInfo['comments'][1]['id']);
            $this->assertEquals(899, $blogInfo['comments'][1]['blog']);
            $this->assertEquals('hehehehehe this rules!', $blogInfo['comments'][1]['comment']);
            $this->assertEquals('2012-10-31 09:56:47', $blogInfo['comments'][1]['date']);
            $this->assertEquals('annad@annadbruce.com', $blogInfo['comments'][1]['email']);
            $this->assertEquals('68.98.132.164', $blogInfo['comments'][1]['ip']);
            $this->assertEquals('Anna', $blogInfo['comments'][1]['name']);
            $this->assertNull($blogInfo['comments'][1]['user']);
        } finally {
            unset( $_SESSION['hash']);
            unset( $_SERVER['SERVER_NAME']);
            unset( $_SERVER['SERVER_PORT']);
            $socialMedia = new SocialMedia();
            $socialMedia->removeBlogFromTwitter($blog);
        }
    }

    public function testDeleteNoAccess() {
        $blog = Blog::withId(899);
        try {
            $blog->delete();
        } catch (Exception $e) {
            $this->assertEquals("User not authorized to delete blog post", $e->getMessage());
        }
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 899;"));
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = 899;"));
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = 899;"));
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = 899;"));
        $this->assertEquals(2, $this->sql->getRowCount("SELECT * FROM `blog_comments` WHERE `blog_comments`.`blog` = 899;"));
        $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/blog/posts/2031/01/01/sample.jpg'));
    }

    public function testDelete() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $blog = Blog::withId(899);
        $blog->delete();
        unset($_SESSION ['hash']);
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 899;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = 899;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = 899;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = 899;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_comments` WHERE `blog_comments`.`blog` = 899;"));
        $this->assertFalse(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/blog/posts/2031/01/01/sample.jpg'));
    }
}