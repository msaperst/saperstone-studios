<?php

namespace api;

use Blog;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use SocialMedia;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UpdateBlogPostTest extends TestCase {
    /**
     * @var Client
     */
    private $http;
    /**
     * @var Sql
     */
    private $sql;

    /**
     * @throws Exception
     */
    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('998', 'Sample Blog', '2031-01-01', '', 0)");
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('999', 'Sample Blog', '2031-01-01', 'posts/2030/01/01/preview_image-999.jpg', 0)");
        $this->sql->executeStatement("INSERT INTO `blog_comments` (`id`, `blog`, `user`, `name`, `date`, `ip`, `email`, `comment`) VALUES (998, 999, NULL, 'Anna', '2012-10-31 09:56:47', '68.98.132.164', 'annad@annadbruce.com', 'hehehehehe this rules!')");
        $this->sql->executeStatement("INSERT INTO `blog_comments` (`id`, `blog`, `user`, `name`, `date`, `ip`, `email`, `comment`) VALUES (999, 999, 4, 'Uploader', '2012-10-31 13:56:47', '192.168.1.2', 'msaperst@gmail.com', 'awesome post')");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('999', '1', 'posts/2031/01/01/sample.jpg', 300, 400, 0, 0)");
        $this->sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('999', 29)");
        $this->sql->executeStatement("INSERT INTO `blog_texts` (`blog`, `contentGroup`, `text`) VALUES ('999', '2', 'Some blog text')");
    }

    /**
     * @throws Exception
     */
    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 998;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `blog_images` WHERE `blog_images`.`blog` = 999;");
        $this->sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = 999;");
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

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/update-blog-post.php');
        } catch (GuzzleException | ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        try {
            $this->http->request('POST', 'api/update-blog-post.php', [
                'cookies' => $cookieJar
            ]);
        } catch (GuzzleException | ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testNoBlogId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-blog-post.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-blog-post.php', [
            'form_params' => [
                'post' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testLetterAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-blog-post.php', [
            'form_params' => [
                'post' => 'a'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id does not match any blog posts", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBadAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-blog-post.php', [
            'form_params' => [
                'post' => 9999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id does not match any blog posts", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoTitle() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-blog-post.php', [
            'form_params' => [
                'post' => 999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog title is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankTitle() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-blog-post.php', [
            'form_params' => [
                'post' => 999,
                'title' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog title can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoDate() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-blog-post.php', [
            'form_params' => [
                'post' => 999,
                'title' => 'Sample Blog'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog date is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankDate() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-blog-post.php', [
            'form_params' => [
                'post' => 999,
                'title' => 'Sample Blog',
                'date' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog date can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBadDate() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-blog-post.php', [
            'form_params' => [
                'post' => 999,
                'title' => 'Sample Blog',
                'date' => '1234'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog date is not the correct format", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoPreviewImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-blog-post.php', [
            'form_params' => [
                'post' => 999,
                'title' => 'Sample Blog',
                'date' => date("Y-m-d")
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog preview image is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoPreviewImage2() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-blog-post.php', [
            'form_params' => [
                'post' => 999,
                'title' => 'Sample Blog',
                'date' => date("Y-m-d"),
                'preview' => array()
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog preview image is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankPreviewImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-blog-post.php', [
            'form_params' => [
                'post' => 999,
                'title' => 'Sample Blog',
                'date' => date("Y-m-d"),
                'preview' => [
                    'img' => ''
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog preview image can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoContent() {
        try {
            copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/tmp_flower.jpeg');
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/update-blog-post.php', [
                'form_params' => [
                    'post' => 999,
                    'title' => 'Sample Blog',
                    'date' => date("Y-m-d"),
                    'preview' => [
                        'img' => '../blog/posts/tmp_flower.jpeg'
                    ]
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals('', (string)$response->getBody());
            $blogDetails = $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 999;");
            $this->assertEquals(999, $blogDetails['id']);
            $this->assertEquals("Sample Blog", $blogDetails['title']);
            $this->assertNull($blogDetails['safe_title']);
            $this->assertEquals(date("Y-m-d"), $blogDetails['date']);
            $this->assertEquals("posts/2030/01/01/preview_image-999.jpg", $blogDetails['preview']);
            $this->assertEquals("0", $blogDetails['offset']);
            $this->assertEquals("0", $blogDetails['active']);
            $blogImages = $this->sql->getRow("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = 999;");
            $this->assertEquals(999, $blogImages['blog']);
            $this->assertEquals(1, $blogImages['contentGroup']);
            $this->assertEquals('posts/2031/01/01/sample.jpg', $blogImages['location']);
            $this->assertEquals('300', $blogImages['width']);
            $this->assertEquals('400', $blogImages['height']);
            $this->assertEquals('0', $blogImages['left']);
            $this->assertEquals('0', $blogImages['top']);
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = 999;"));
            $blogTexts = $this->sql->getRows("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = 999;");
            $this->assertEquals(1, sizeof($blogTexts));
            $this->assertEquals(999, $blogTexts[0]['blog']);
            $this->assertEquals(2, $blogTexts[0]['contentGroup']);
            $this->assertEquals('Some blog text', $blogTexts[0]['text']);
        } finally {
            unlink(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/tmp_flower.jpeg');
        }
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function testPreviewOffsetOnlyImage() {
        try {
            touch(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . 'image.jpg');
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/update-blog-post.php', [
                'form_params' => [
                    'post' => 999,
                    'title' => 'Sample Blog',
                    'date' => '2030-01-01',
                    'preview' => [
                        'img' => '../blog/posts/image.jpg',
                        'offset' => '10px'
                    ],
                    'content' => [
                        1 => [
                            'group' => 1,
                            'type' => 'images',
                            'imgs' => [
                                0 => [
                                    'location' => '../blog/posts/image.jpg',
                                    'top' => '0px',
                                    'left' => '0px',
                                    'width' => '1140px',
                                    'height' => '647px',
                                ]
                            ]
                        ]
                    ]
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals('', (string)$response->getBody());
            $blogDetails = $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 999;");
            $this->assertEquals(999, $blogDetails['id']);
            $this->assertEquals("Sample Blog", $blogDetails['title']);
            $this->assertNull($blogDetails['safe_title']);
            $this->assertEquals("2030-01-01", $blogDetails['date']);
            $this->assertEquals("posts/2030/01/01/preview_image-999.jpg", $blogDetails['preview']);
            $this->assertEquals("10", $blogDetails['offset']);
            $this->assertEquals("0", $blogDetails['active']);
            $blogImages = $this->sql->getRow("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = 999;");
            $this->assertEquals(999, $blogImages['blog']);
            $this->assertEquals(1, $blogImages['contentGroup']);
            $this->assertEquals('posts/2030/01/01/image.jpg', $blogImages['location']);
            $this->assertEquals('1140', $blogImages['width']);
            $this->assertEquals('647', $blogImages['height']);
            $this->assertEquals('0', $blogImages['left']);
            $this->assertEquals('0', $blogImages['top']);
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = 999;"));
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = 999;"));
            $this->assertTrue(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "content/blog/2030/01/01/preview_image-999.jpg"));
            $this->assertTrue(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "content/blog/2030/01/01/image.jpg"));
        } finally {
            // cleanup
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $this->http->request('POST', 'api/delete-blog.php', [
                'form_params' => [
                    'post' => 999
                ],
                'cookies' => $cookieJar
            ]);
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testNoPreviewOffsetOnlyText() {
        try {
            touch(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . 'image.jpg');
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/update-blog-post.php', [
                'form_params' => [
                    'post' => 999,
                    'title' => 'Sample Blog',
                    'date' => '2030-01-01',
                    'preview' => [
                        'img' => '../blog/posts/image.jpg',
                    ],
                    'content' => [
                        1 => [
                            'group' => 1,
                            'type' => 'text',
                            'text' => 'Some blog text'
                        ]
                    ]
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals('', (string)$response->getBody());
            $blogDetails = $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 999;");
            $this->assertEquals(999, $blogDetails['id']);
            $this->assertEquals("Sample Blog", $blogDetails['title']);
            $this->assertNull($blogDetails['safe_title']);
            $this->assertEquals("2030-01-01", $blogDetails['date']);
            $this->assertEquals("posts/2030/01/01/preview_image-999.jpg", $blogDetails['preview']);
            $this->assertEquals("0", $blogDetails['offset']);
            $this->assertEquals("0", $blogDetails['active']);
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = 999;"));
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = 999;"));
            $blogTexts = $this->sql->getRow("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = 999;");
            $this->assertEquals(999, $blogTexts['blog']);
            $this->assertEquals(1, $blogTexts['contentGroup']);
            $this->assertEquals('Some blog text', $blogTexts['text']);
        } finally {
            // cleanup
            unlink(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/image.jpg');
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $this->http->request('POST', 'api/delete-blog.php', [
                'form_params' => [
                    'post' => 999
                ],
                'cookies' => $cookieJar
            ]);
        }
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function testTagsOffsetImagesText() {
        try {
            copy(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/image.jpg');
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/update-blog-post.php', [
                'form_params' => [
                    'post' => 999,
                    'title' => 'Sample Blog',
                    'date' => '2030-01-01',
                    'preview' => [
                        'img' => '../blog/posts/image.jpg',
                        'offset' => '33px'
                    ],
                    'tags' => [
                        "4", "2"
                    ],
                    'active' => 1,
                    'content' => [
                        1 => [
                            'group' => 1,
                            'type' => 'images',
                            'imgs' => [
                                0 => [
                                    'location' => '../blog/posts/image.jpg',
                                    'top' => '0px',
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
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals('', (string)$response->getBody());
            $blogDetails = $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 999;");
            $this->assertEquals(999, $blogDetails['id']);
            $this->assertEquals("Sample Blog", $blogDetails['title']);
            $this->assertNull($blogDetails['safe_title']);
            $this->assertEquals("2030-01-01", $blogDetails['date']);
            $this->assertEquals("posts/2030/01/01/preview_image-999.jpg", $blogDetails['preview']);
            $this->assertEquals("33", $blogDetails['offset']);
            $this->assertEquals(1, $blogDetails['active']);
            $blogImages = $this->sql->getRow("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = 999;");
            $this->assertEquals(999, $blogImages['blog']);
            $this->assertEquals(1, $blogImages['contentGroup']);
            $this->assertEquals('posts/2030/01/01/image.jpg', $blogImages['location']);
            $this->assertEquals('1140', $blogImages['width']);
            $this->assertEquals('647', $blogImages['height']);
            $this->assertEquals('0', $blogImages['left']);
            $this->assertEquals('0', $blogImages['top']);
            $blogTags = $this->sql->getRows("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = 999;");
            $this->assertEquals(2, sizeOf($blogTags));
            $this->assertEquals(999, $blogTags[0]['blog']);
            $this->assertEquals(4, $blogTags[0]['tag']);
            $this->assertEquals(999, $blogTags[1]['blog']);
            $this->assertEquals(2, $blogTags[1]['tag']);
            $blogTexts = $this->sql->getRow("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = 999;");
            $this->assertEquals(999, $blogTexts['blog']);
            $this->assertEquals(2, $blogTexts['contentGroup']);
            $this->assertEquals('Some blog text', $blogTexts['text']);
        } finally {
            $_SERVER['SERVER_NAME'] = "www.examples.com";
            $_SERVER['SERVER_PORT'] = "90";
            unset($_SERVER['SERVER_NAME']);
            unset($_SERVER['SERVER_PORT']);
            // cleanup
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $this->http->request('POST', 'api/delete-blog.php', [
                'form_params' => [
                    'post' => 999
                ],
                'cookies' => $cookieJar
            ]);
        }
    }
}