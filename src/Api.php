<?php

//TODO - redo this by throwing errors?

class Api {
    public const CSRF_ERROR = 'Your session has expired. Please refresh the page and try again.';

    private $user;

    function __construct() {
        $this->user = User::fromSystem();
    }

    protected static function requestMethodMatches(string $method): bool {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === strtoupper($method);
    }

    public static function requireMethod(string $method): bool {
        $method = strtoupper($method);

        if (!self::requestMethodMatches($method)) {
            header('Allow: ' . $method);
            http_response_code(405);
            return false;
        }

        return true;
    }

    /**
     * Protect authenticated state-changing requests against cross-site request forgery.
     *
     * Browser requests normally provide both a same-origin Origin header and the
     * synchronizer token added by nav.js. Accept either defense so normal requests
     * remain robust if a proxy or browser strips one of them.
     */
    public static function requireCsrfProtection(): bool {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }

        $session = new Session();
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? null);
        if ($session->isCsrfTokenValid($token)) {
            return true;
        }

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ($origin !== '' && rtrim(strtolower($origin), '/') === strtolower($session->getBaseURL())) {
            return true;
        }

        http_response_code(403);
        return false;
    }

    /**
     * Resolve a browser-supplied path without allowing it to escape the public tree.
     *
     * Absolute-style paths are treated as public-root relative. Relative paths are
     * resolved from the supplied public subdirectory, which is "api" for legacy
     * image-edit requests such as ../img/main/example.jpg.
     */
    public static function resolvePublicPath(string $path, string $base = ''): ?string {
        if (str_contains($path, "\0")) {
            return null;
        }

        $path = str_replace('\\', '/', $path);
        $base = str_replace('\\', '/', $base);
        $relativePath = str_starts_with($path, '/')
            ? ltrim($path, '/')
            : trim($base, '/') . ($base === '' ? '' : '/') . $path;

        $segments = [];
        foreach (explode('/', $relativePath) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                if ($segments === []) {
                    return null;
                }
                array_pop($segments);
                continue;
            }
            $segments[] = $segment;
        }

        $publicRoot = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public';
        return $publicRoot . ($segments === []
            ? ''
            : DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $segments));
    }

    private function retrievePost($variable, $variableName, $type) {
        if (isset ($_POST [$variable]) && $_POST [$variable] != "") {
            switch ($type) {
                case "int":
                    return (int)$_POST [$variable];
                case "float":
                    return floatval(str_replace('$', '', $_POST [$variable]));
                case "string":
                default:
                    return $_POST [$variable];
            }
        } else {
            if (!isset ($_POST [$variable])) {
                throw new BadRequestException("$variableName is required");
            } else {
                throw new BadRequestException("$variableName can not be blank");
            }
        }
    }

    /**
     * @throws Exception
     */
    function retrieveValidatedPost($variable, $variableName, $validation): string {
        if (isset ($_POST [$variable]) && filter_var($_POST [$variable], $validation)) {
            return $_POST [$variable];
        } else {
            if (!isset ($_POST [$variable])) {
                throw new BadRequestException("$variableName is required");
            } elseif ($_POST [$variable] == "") {
                throw new BadRequestException("$variableName can not be blank");
            } else {
                throw new BadRequestException("$variableName is not valid");
            }
        }
    }

    function retrievePostDateTime($variable, $variableName, $format) {
        if (isset ($_POST [$variable]) && $_POST [$variable] != "") {
            $date = $_POST [$variable];
            $d = DateTime::createFromFormat($format, $date);
            if (!($d && $d->format($format) === $date)) {
                throw new BadRequestException("$variableName is not the correct format");
            } else {
                return $date;
            }
        } else {
            if (!isset ($_POST [$variable])) {
                throw new BadRequestException("$variableName is required");
            } else {
                throw new BadRequestException("$variableName can not be blank");
            }
        }
    }

    function retrievePostInt($variable, $variableName) {
        return $this->retrievePost($variable, $variableName, 'int');
    }

    function retrievePostFloat($variable, $variableName) {
        return $this->retrievePost($variable, $variableName, 'float');
    }

    function retrievePostString($variable, $variableName) {
        return $this->retrievePost($variable, $variableName, 'string');
    }

    private function retrieveGet($variable, $variableName, $type) {
        if (isset ($_GET [$variable]) && $_GET [$variable] != "") {
            switch ($type) {
                case "int":
                    return (int)$_GET [$variable];
                case "float":
                    return floatval(str_replace('$', '', $_GET [$variable]));
                case "string":
                default:
                    return $_GET [$variable];
            }
        } else {
            if (!isset ($_GET [$variable])) {
                throw new BadRequestException("$variableName is required");
            } else {
                throw new BadRequestException("$variableName can not be blank");
            }
        }
    }

    function retrieveGetInt($variable, $variableName) {
        return $this->retrieveGet($variable, $variableName, 'int');
    }

    function retrieveGetFloat($variable, $variableName) {
        return $this->retrieveGet($variable, $variableName, 'float');
    }

    function retrieveGetString($variable, $variableName) {
        return $this->retrieveGet($variable, $variableName, 'string');
    }

    function forceLoggedIn() {
        if (!$this->user->isLoggedIn()) {
            header('HTTP/1.0 401 Unauthorized');
            echo "You must be logged in to perform this action";
            exit ();
        }
        if (!self::requireCsrfProtection()) {
            echo self::CSRF_ERROR;
            exit ();
        }
    }

    function forceAdmin() {
        if (!$this->user->isAdmin()) {
            header('HTTP/1.0 401 Unauthorized');
            if ($this->user->isLoggedIn()) {
                echo "You do not have appropriate rights to perform this action";
            }
            exit ();
        }
        if (!self::requireCsrfProtection()) {
            echo self::CSRF_ERROR;
            exit ();
        }
    }

    /**
     * Set the HTTP status for an API exception while allowing each endpoint to
     * preserve its existing response-body format.
     */
    public static function setErrorResponseCode(Throwable $exception) {
        if ($exception instanceof SaperstoneStudiosException && !($exception instanceof SqlException)) {
            http_response_code(400);
            return;
        }
        http_response_code(500);
    }
}
