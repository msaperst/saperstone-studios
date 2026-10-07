<?php

final class DownloadEmailJob {

    private const JOB_DIRECTORY = 'saperstone-download-email-jobs';
    private const TOKEN_PATTERN = '/^[a-f0-9]{32}$/D';

    public static function generateToken(): string {
        return bin2hex(random_bytes(16));
    }

    public static function create(string $token, string $email, string $filePath): void {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Download email address is not valid');
        }

        $directory = self::getDirectory();
        if (!is_dir($directory)
            && !mkdir($directory, 0700, true)
            && !is_dir($directory)) {
            throw new RuntimeException('Unable to create download email job directory');
        }

        $payload = json_encode([
            'email' => $email,
            'file' => $filePath
        ], JSON_THROW_ON_ERROR);

        if (file_put_contents(self::getJobPath($token), $payload, LOCK_EX) === false) {
            throw new RuntimeException('Unable to create download email job');
        }

        chmod(self::getJobPath($token), 0600);
    }

    public static function load(string $token): array {
        $contents = @file_get_contents(self::getJobPath($token));
        if ($contents === false) {
            throw new RuntimeException('Download email job does not exist');
        }

        try {
            $job = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Download email job is not valid', 0, $exception);
        }

        if (!is_array($job)
            || !isset($job['email'], $job['file'])
            || !is_string($job['email'])
            || !is_string($job['file'])
            || filter_var($job['email'], FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException('Download email job is not valid');
        }

        return $job;
    }

    public static function delete(string $token): void {
        $path = self::getJobPath($token);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private static function getDirectory(): string {
        return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . self::JOB_DIRECTORY;
    }

    private static function getJobPath(string $token): string {
        if (preg_match(self::TOKEN_PATTERN, $token) !== 1) {
            throw new InvalidArgumentException('Download email job token is not valid');
        }

        return self::getDirectory() . DIRECTORY_SEPARATOR . $token . '.json';
    }
}
