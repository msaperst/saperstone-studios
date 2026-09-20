<?php

class RememberMe {

    public const COOKIE_NAME = 'remember_me';
    public const LIFETIME = 30 * 24 * 60 * 60;

    public static function remember(int $userId): string {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $expires = time() + self::LIFETIME;

        $sql = new Sql();
        $sql->executeStatement("DELETE FROM `remember_tokens` WHERE `expires_at` <= CURRENT_TIMESTAMP");
        $sql->executeStatement(
            "INSERT INTO `remember_tokens` (`selector`, `user`, `token_hash`, `expires_at`) VALUES (?, ?, ?, FROM_UNIXTIME(?))",
            [$selector, $userId, hash('sha256', $validator), $expires]
        );
        $sql->disconnect();

        $cookieValue = $selector . ':' . $validator;
        self::setCookie($cookieValue, $expires);
        return $cookieValue;
    }

    public static function restore(): ?User {
        $parts = self::cookieParts();
        if ($parts === null) {
            return null;
        }
        [$selector, $validator] = $parts;

        $sql = new Sql();
        $row = $sql->getRow(
            "SELECT `user`, `token_hash` FROM `remember_tokens` WHERE `selector` = ? AND `expires_at` > CURRENT_TIMESTAMP",
            [$selector]
        );
        if ($row === null || !hash_equals($row['token_hash'], hash('sha256', $validator))) {
            $sql->executeStatement("DELETE FROM `remember_tokens` WHERE `selector` = ?", [$selector]);
            $sql->disconnect();
            self::clearCookie();
            return null;
        }

        $user = User::withId($row['user']);
        if (!$user->isActive()) {
            $sql->executeStatement("DELETE FROM `remember_tokens` WHERE `selector` = ?", [$selector]);
            $sql->disconnect();
            self::clearCookie();
            return null;
        }

        $sql->executeStatement(
            "UPDATE `remember_tokens` SET `last_used_at` = CURRENT_TIMESTAMP WHERE `selector` = ?",
            [$selector]
        );
        $sql->executeStatement(
            "INSERT INTO `user_logs` (`user`, `time`, `action`, `what`, `album`) VALUES (?, CURRENT_TIMESTAMP, 'Remembered Login', NULL, NULL)",
            [$user->getId()]
        );
        $sql->disconnect();

        self::establishSession($user);
        return $user;
    }

    public static function forgetCurrent(): void {
        $parts = self::cookieParts();
        if ($parts !== null) {
            $sql = new Sql();
            $sql->executeStatement("DELETE FROM `remember_tokens` WHERE `selector` = ?", [$parts[0]]);
            $sql->disconnect();
        }
        self::clearCookie();
    }

    public static function forgetAllForUser(int $userId): void {
        $sql = new Sql();
        $sql->executeStatement("DELETE FROM `remember_tokens` WHERE `user` = ?", [$userId]);
        $sql->disconnect();
        self::clearCookie();
    }

    public static function clearLegacyCookies(): void {
        if (!headers_sent()) {
            $options = [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => self::isSecureRequest(),
                'httponly' => true,
                'samesite' => 'Lax'
            ];
            setcookie('hash', '', $options);
            setcookie('usr', '', $options);
        }
        unset($_COOKIE['hash'], $_COOKIE['usr']);
    }

    public static function establishSession(User $user): void {
        $session = new Session();
        $session->initialize();
        session_regenerate_id(true);
        $_SESSION['usr'] = $user->getUsername();
        $_SESSION['hash'] = $user->getHash();
    }

    private static function cookieParts(): ?array {
        if (!isset($_COOKIE[self::COOKIE_NAME]) || !is_string($_COOKIE[self::COOKIE_NAME])) {
            return null;
        }
        $parts = explode(':', $_COOKIE[self::COOKIE_NAME], 2);
        if (count($parts) !== 2
            || !preg_match('/^[a-f0-9]{32}$/', $parts[0])
            || !preg_match('/^[a-f0-9]{64}$/', $parts[1])) {
            self::clearCookie();
            return null;
        }
        return $parts;
    }

    private static function setCookie(string $value, int $expires): void {
        if (!headers_sent()) {
            setcookie(self::COOKIE_NAME, $value, [
                'expires' => $expires,
                'path' => '/',
                'secure' => self::isSecureRequest(),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
    }

    private static function clearCookie(): void {
        if (!headers_sent()) {
            setcookie(self::COOKIE_NAME, '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => self::isSecureRequest(),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        unset($_COOKIE[self::COOKIE_NAME]);
    }

    private static function isSecureRequest(): bool {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    }
}
