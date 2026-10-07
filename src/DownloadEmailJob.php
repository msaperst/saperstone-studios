<?php

final class DownloadEmailJob {

    private const JOB_DIRECTORY = 'saperstone-download-email-jobs';
    private const TOKEN_PATTERN = '/^[a-f0-9]{32}$/D';

    private string $directory;

    public function __construct(?string $directory = null) {
        $this->directory = $directory
            ?? rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
                . DIRECTORY_SEPARATOR . self::JOB_DIRECTORY;
    }

    public static function generateToken(): string {
        return bin2hex(random_bytes(16));
    }

    public function create(string $token, string $email, string $filePath): void {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Download email address is not valid');
        }

        if (!is_dir($this->directory)
            && !@mkdir($this->directory, 0700, true)
            && !is_dir($this->directory)) {
            throw new DownloadEmailJobException('Unable to create download email job directory');
        }

        $payload = json_encode([
            'email' => $email,
            'file' => $filePath
        ], JSON_THROW_ON_ERROR);
        $jobPath = $this->getJobPath($token);

        if (@file_put_contents($jobPath, $payload, LOCK_EX) === false) {
            throw new DownloadEmailJobException('Unable to create download email job');
        }

        chmod($jobPath, 0600);
    }

    public function load(string $token): array {
        $contents = @file_get_contents($this->getJobPath($token));
        if ($contents === false) {
            throw new DownloadEmailJobException('Download email job does not exist');
        }

        try {
            $job = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new DownloadEmailJobException('Download email job is not valid', 0, $exception);
        }

        if (!is_array($job)
            || !isset($job['email'], $job['file'])
            || !is_string($job['email'])
            || !is_string($job['file'])
            || filter_var($job['email'], FILTER_VALIDATE_EMAIL) === false) {
            throw new DownloadEmailJobException('Download email job is not valid');
        }

        return $job;
    }

    public function delete(string $token): void {
        $path = $this->getJobPath($token);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function getJobPath(string $token): string {
        if (preg_match(self::TOKEN_PATTERN, $token) !== 1) {
            throw new InvalidArgumentException('Download email job token is not valid');
        }

        return $this->directory . DIRECTORY_SEPARATOR . $token . '.json';
    }
}
