<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Testwork\Tester\Result\TestResult;
use Exception;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Sql;
use User;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

/**
 * Defines application behat from the specific context.
 */
class BaseFeatureContext implements Context {

    const string reportDir = __DIR__ . '/../../../reports/behat/';
    const string downloadDir = __DIR__ . '/../../../tmp/behat-downloads/';
    /**
     * @var RemoteWebDriver
     */
    private RemoteWebDriver $driver;
    private string $baseUrl;
    /**
     * @var User
     */
    private User $user;
    private bool $deleteUser = true;

    public function getDriver(): RemoteWebDriver {
        return $this->driver;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getBaseUrl(): string {
        return $this->baseUrl;
    }

    public function getDownloadDirectory(): string {
        return realpath(self::downloadDir) ?: self::downloadDir;
    }

    public function setUser(User $user): void {
        $this->user = $user;
    }

    public function dontDeleteUser(): void {
        $this->deleteUser = false;
    }

    /**
     * @BeforeSuite
     */
    public static function setupTestReport(): void {
        $directories = [
            BaseFeatureContext::reportDir . 'screenshots',
            BaseFeatureContext::downloadDir,
        ];
        foreach ($directories as $directory) {
            if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new \RuntimeException("Unable to create Behat directory: $directory");
            }
        }
    }

    /**
     * @BeforeScenario
     * @throws Exception
     */
    public function setupUser(): void {
        // Each scenario must start with an empty mailbox so email assertions
        // cannot match messages left behind by earlier scenarios.
        \CustomAsserts::clearAllEmails();

        // setup our webdriver instance
        $host = 'http://127.0.0.1:4444';
        $headless = getenv('HEADLESS');
        if (getenv('BROWSER') == 'firefox') {
            $desiredCapabilities = DesiredCapabilities::firefox();
            $firefoxOptions = new FirefoxOptions();
            if ($headless) {
                $firefoxOptions->addArguments(['-headless']);
            }
            $desiredCapabilities->setCapability(FirefoxOptions::CAPABILITY, $firefoxOptions);
        } else {
            $desiredCapabilities = DesiredCapabilities::chrome();
            $chromeOptions = new ChromeOptions();
            if ($headless) {
                $chromeOptions->addArguments(['-headless']);
            }
            $chromeOptions->setExperimentalOption('prefs', [
                'download.default_directory' => $this->getDownloadDirectory(),
                'download.prompt_for_download' => false,
                'download.directory_upgrade' => true,
            ]);
            $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
            $desiredCapabilities->setCapability('goog:loggingPrefs', ['browser' => 'ALL']);
        }
        if (getenv('PROXY') != NULL) {
            $desiredCapabilities->setCapability(WebDriverCapabilityType::PROXY, ['proxyType' => 'MANUAL', 'httpProxy' => getenv('PROXY'), 'ftpProxy' => NULL, 'sslProxy' => NULL, 'noProxy' => NULL]);
        }

        $this->driver = RemoteWebDriver::create($host, $desiredCapabilities);
        $this->baseUrl = 'http://' . getenv('APP_URL') . ':' . getenv('HTTP_PORT') . '/';
        $this->user = new User();

        // setup some basic cookies in the browser
        $this->driver->get($this->baseUrl);
        $cookie = new Cookie('CookiePreferences', '["preferences","analytics","social"]');
        $this->driver->manage()->addCookie($cookie);
        $this->driver->navigate()->refresh();

        // setup a basic user
        $params = [
            'username' => 'testUser',
            'firstName' => 'test',
            'lastName' => 'user',
            'email' => 'msaperst+sstest@gmail.com',
            'password' => '12345'
        ];
        $this->user = User::withParams($params);
    }

    /**
     * @AfterScenario
     * @param AfterScenarioScope $scope
     * @throws Exception
     */
    public function cleanup(AfterScenarioScope $scope): void {
        if ($scope->getTestResult()->getResultCode() !== TestResult::PASSED) {
            $scenarioName = $scope->getFeature()->getTitle() . ' : ' . $scope->getScenario()->getTitle() . ' : ' . $scope->getScenario()->getLine();
            $safeScenarioName = preg_replace('/[^A-Za-z0-9._-]+/', '-', $scenarioName);
            $this->driver->takeScreenshot(BaseFeatureContext::reportDir . 'screenshots' . DIRECTORY_SEPARATOR . $safeScenarioName . '.png');
            try {
                $logs = $this->driver->manage()->getLog('browser');
                file_put_contents(
                    BaseFeatureContext::reportDir . 'screenshots' . DIRECTORY_SEPARATOR . $safeScenarioName . '.json',
                    json_encode($logs, JSON_PRETTY_PRINT)
                );
            } catch (\Throwable $exception) {
                error_log('Browser console unavailable: ' . $exception->getMessage());
            }

            // Capture enough browser state to distinguish a missing event binding,
            // an unfinished metadata request, and a server/AJAX failure. Screenshots
            // alone show only the final page, not why a click had no effect.
            try {
                $diagnostic = $this->driver->executeScript(<<<'JS'
                    return (function () {
                        var jq = window.jQuery;
                        function inspect(selector) {
                            var element = document.querySelector(selector);
                            if (!element) {
                                return {exists: false};
                            }
                            var style = window.getComputedStyle(element);
                            var events = jq && typeof jq._data === 'function'
                                ? jq._data(element, 'events') || {} : {};
                            var rect = element.getBoundingClientRect();
                            var center = document.elementFromPoint(
                                rect.left + rect.width / 2, rect.top + rect.height / 2
                            );
                            return {
                                exists: true,
                                visible: style.display !== 'none' && style.visibility !== 'hidden'
                                    && element.getClientRects().length > 0,
                                disabled: !!element.disabled,
                                classes: String(element.className || ''),
                                opacity: style.opacity,
                                pointerEvents: style.pointerEvents,
                                hovered: element.matches(':hover'),
                                dataLoaded: element.getAttribute('data-loaded'),
                                centerHit: center ? center.tagName.toLowerCase() + '.' +
                                    String(center.className || '').replace(/\\s+/g, '.') : null,
                                listeners: Object.keys(events)
                            };
                        }
                        var album = window.album;
                        return {
                            url: window.location.pathname + window.location.search + window.location.hash,
                            readyState: document.readyState,
                            jqueryActiveRequests: jq ? jq.active : null,
                            album: album ? {
                                initialized: album.initialized,
                                loading: album.loading,
                                loadFailed: album.loadFailed,
                                imagesLoaded: album.images.length,
                                totalImages: album.totalImages,
                                currentImageId: album.currentImageId,
                                pendingImageId: album.pendingImageId
                            } : null,
                            cardCount: document.querySelectorAll('#album-grid .album-card').length,
                            viewerImageId: document.querySelector('#album-viewer-overlay')
                                ? document.querySelector('#album-viewer-overlay').getAttribute('image-id') : null,
                            ui: {
                                viewer: inspect('#album-viewer-overlay'),
                                viewerClose: inspect('#album-viewer-close'),
                                viewerNext: inspect('#album-next-btn'),
                                viewerPrevious: inspect('#album-prev-btn'),
                                viewerFavoriteSet: inspect('#set-favorite-image-btn'),
                                viewerFavoriteUnset: inspect('#unset-favorite-image-btn'),
                                firstCard: inspect('#album-grid .album-card'),
                                firstCardMedia: inspect('#album-grid .album-card .album-card-media'),
                                firstCardOverlay: inspect('#album-grid .album-card .album-card-overlay'),
                                firstCardActions: inspect('#album-grid .album-card .album-card-actions'),
                                imageSubmit: inspect('#submit-image-btn'),
                                imageDownload: inspect('#downloadable-image-btn'),
                                notifySubmit: inspect('#notify-submit'),
                                favoriteButton: inspect('#favorite-btn'),
                                albumGrid: inspect('#album-grid'),
                                newAlbumButton: inspect('.add-album-btn'),
                                editAlbumButton: inspect('.edit-album-btn'),
                                submitModal: inspect('#submit'),
                                bootstrapDialog: inspect('.bootstrap-dialog')
                            },
                            ajaxResources: window.performance.getEntriesByType('resource')
                                .filter(function (entry) {
                                    return entry.name.indexOf('/api/') !== -1;
                                })
                                .slice(-20)
                                .map(function (entry) {
                                    return {
                                        path: new URL(entry.name).pathname,
                                        status: entry.responseStatus || null,
                                        duration: Math.round(entry.duration)
                                    };
                                })
                        };
                    })();
                    JS);
                file_put_contents(
                    BaseFeatureContext::reportDir . 'screenshots' . DIRECTORY_SEPARATOR . $safeScenarioName . '.state.json',
                    json_encode($diagnostic, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                );
            } catch (\Throwable $exception) {
                error_log('Browser state unavailable: ' . $exception->getMessage());
            }
        }
        $this->driver->quit();
        // if we created a new user
        if ($this->user->getId() != '' && $this->deleteUser) {
            $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $this->user->delete();
            unset($_SESSION ['hash']);
            $sql = new Sql();
            //$sql->executeStatement("DELETE FROM users WHERE usr = 'testUser';");
            $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `users`;")['count'];
            $count++;
            $sql->executeStatement("ALTER TABLE `users` AUTO_INCREMENT = $count;");
            $sql->disconnect();
        }
    }

}
